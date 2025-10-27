/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { useState } from '@wordpress/element';

import { send, Icon } from '@wordpress/icons';

const ChatInput = ({ isLoading, onSend }) => {
	const [value, setValue] = useState('');

	const handleSend = () => {
		if (value.trim()) {
			onSend(value);
			setValue('');
		}
	};

	const handleKeyDown = (e) => {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			handleSend();
		}
	};

	return (
		<div className="border-t border-gray-200 p-4 bg-white fixed w-[-moz-available] w-[-webkit-fill-available] w-[fill-available] bottom-0">
			<div className="flex items-end gap-2">
				<textarea
					value={value}
					onChange={(e) => setValue(e.target.value)}
					onKeyDown={handleKeyDown}
					placeholder={__(
						"Describe what you'd like to create…",
						'particles'
					)}
					className="flex-1 min-h-[80px] px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					rows={3}
					disabled={isLoading}
				/>
				<button
					onClick={handleSend}
					className="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg transition flex-shrink-0"
					disabled={isLoading}
				>
					<Icon className="w-5 h-5 fill-white" icon={send} />
				</button>
			</div>
			<div className="flex items-center justify-between mt-2">
				<p className="text-xs text-gray-500">
					{__('Press Enter to send', 'particles')}
				</p>
				<p className="text-xs text-gray-400">
					{__('Powered by AI', 'particles')}
				</p>
			</div>
		</div>
	);
};

export default ChatInput;
