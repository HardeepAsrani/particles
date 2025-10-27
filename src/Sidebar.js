/**
 * External dependencies.
 */
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import apiFetch from '@wordpress/api-fetch';

import { parse } from '@wordpress/blocks';

import { useState } from '@wordpress/element';

import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';

import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies.
 */
import WelcomeMessage from './elements/Welcome';
import UserMessage from './elements/UserMessage';
import SystemMessage from './elements/SystemMessage';
import LoadingMessage from './elements/LoadingMessage';
import ConfirmationMessage from './elements/ConfirmationMessage';
import ChatInput from './elements/ChatInput';

const Sidebar = () => {
	const [ messages, setMessages ] = useState([
		{
			id: uuidv4(),
			type: 'welcome',
			message:
				'Welcome to Particles! Describe what you\'d like to create, and I\'ll help you design it.',
			timestamp: Date.now(),
		},
	]);

	const [ isLoading, setIsLoading ] = useState(false);

	// Message component mapping
	const getMessageComponent = type => {
		const messageComponents = {
			welcome: WelcomeMessage,
			user: UserMessage,
			system: SystemMessage,
			loading: LoadingMessage,
			confirmation: ConfirmationMessage,
		};

		return messageComponents[type] || SystemMessage; // fallback to SystemMessage
	};

	const handleSendMessage = async message => {
		// Add user message immediately
		setMessages( prevMessages => [
			...prevMessages,
			{
				id: uuidv4(),
				type: 'user',
				timestamp: Date.now(),
				message,
			},
		]);

		// Send the request to the API
		await sendRequest( 'planning', message );
	};

	const removeMessageById = id => {
		setMessages( prevMessages =>
			prevMessages.filter( msg => msg.id !== id )
		);
	};

	const addMessage = ( type, message, id = null ) => {
		setMessages( prevMessages => [
			...prevMessages,
			{
				id: id || uuidv4(),
				type,
				message,
				timestamp: Date.now(),
			},
		]);
	};

	const handleInsertPattern = (/* message */) => {
		// TODO: Implement pattern insertion logic
		// This will insert the generated pattern into the editor
	};

	const handleRefinePattern = message => {
		// Send refinement request
		sendRequest( 'refinement', message.message );
	};

	const requestHandlers = {
		planning: {
			loadingMessage: 'Planning your design...',
			successHandler: response => {
				addMessage( 'system', response.message.message );
				
				if ( response.message.outline ) {
					setTimeout( () => {
						sendRequest( 'generation', JSON.stringify( response.message.outline ) );
					}, 1000 );
				}
			},
		},
		generation: {
			loadingMessage: 'Generating your pattern...',
			successHandler: response => {
				addMessage( 'confirmation', response.message.message );
				response.message.patterns.forEach( block => {
					const parsedBlocks = parse( block );
					wp.data.dispatch( 'core/block-editor' ).insertBlocks( parsedBlocks );
				} );
			},
		},
		refinement: {
			loadingMessage: 'Refining your design...',
			successHandler: response => {
				addMessage( 'system', response.message.message );
			},
		},
	};

	const sendRequest = async ( type, message ) => {
		const handler = requestHandlers[type];
		if ( ! handler ) {
			addMessage( 'system', 'Invalid request type.' );
			return;
		}

		try {
			setIsLoading( true );
			addMessage( 'loading', handler.loadingMessage, 'loading' );

			const response = await apiFetch({
				path: `${ window.particlesEditor.api }/chat`,
				method: 'POST',
				data: {
					type,
					message,
				},
			});

			if ( response.error ) {
				removeMessageById( 'loading' );
				setIsLoading( false );
				addMessage(
					'system',
					'Sorry, something went wrong. Please try again.'
				);
				return;
			}

			// Start polling for request status
			await pollRequest( response.response, type );
		} catch ( error ) {
			removeMessageById( 'loading' );
			setIsLoading( false );
			addMessage(
				'system',
				'Sorry, something went wrong. Please try again.'
			);
		}
	};

	const pollRequest = async ( requestId, type, attempt = 1 ) => {
		const maxAttempts = 60; // 5 minutes maximum

		try {
			const response = await apiFetch({
				path: addQueryArgs( `${ window.particlesEditor.api }/chat`, {
					request_id: requestId,
				}),
				method: 'GET',
			});

			if ( response.error ) {
				removeMessageById( 'loading' );
				setIsLoading( false );
				addMessage(
					'system',
					'Sorry, something went wrong. Please try again.'
				);
				return;
			}

			// Handle different status types
			switch ( response.status ) {
			case 'in_progress':
			case 'queued':
				if ( attempt >= maxAttempts ) {
					removeMessageById( 'loading' );
					setIsLoading( false );
					addMessage(
						'system',
						'Request timed out. Please try again.'
					);
					return;
				}

				setTimeout( () => {
					pollRequest( requestId, type, attempt + 1 );
				}, 5000 );
				break;

			case 'completed':
				removeMessageById( 'loading' );
				setIsLoading( false );

				// Use type-specific success handler
				const handler = requestHandlers[type];
				if ( handler && handler.successHandler ) {
					handler.successHandler( response );
				} else {
					addMessage(
						'system',
						response.message?.message || 'Request completed successfully.'
					);
				}
				break;

			case 'failed':
				removeMessageById( 'loading' );
				setIsLoading( false );
				addMessage(
					'system',
					response.error_message || 'Sorry, something went wrong. Please try again.'
				);
				break;

			default:
				removeMessageById( 'loading' );
				setIsLoading( false );
				addMessage(
					'system',
					'Unknown response status. Please try again.'
				);
			}
		} catch ( error ) {
			removeMessageById( 'loading' );
			setIsLoading( false );
			addMessage(
				'system',
				'Sorry, something went wrong. Please try again.'
			);
		}
	};

	return (
		<>
			<PluginSidebarMoreMenuItem target="particles">
				{ __( 'Particles', 'particles' ) }
			</PluginSidebarMoreMenuItem>

			<PluginSidebar
				title={ __( 'Particles', 'particles' ) }
				name="particles"
			>
				<div className="flex-1 flex flex-col">
					<div className="flex-1 overflow-y-auto p-4">
						<div className="space-y-4 max-w-4xl mx-auto">
							{ messages.map( message => {
								const MessageComponent = getMessageComponent( message.type );
								return (
									<MessageComponent
										key={ message.id }
										message={ message.message }
										timestamp={ message.timestamp }
										{ ...( message.type === 'confirmation' && {
											onInsert: () =>
												handleInsertPattern( message ),
											onRefine: () =>
												handleRefinePattern( message ),
										} ) }
									/>
								);
							} ) }
						</div>
					</div>

					<ChatInput
						isLoading={ isLoading }
						onSend={ handleSendMessage }
					/>
				</div>
			</PluginSidebar>
		</>
	);
};

export default Sidebar;
