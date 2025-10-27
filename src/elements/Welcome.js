/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

const WelcomeMessage = ({ message }) => {
	return (
		<div className="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
			<p className="text-sm m-0 text-gray-700">
				<span className="font-semibold text-blue-700">
					{__('Welcome!', 'particles')}
				</span>{' '}
				{message}
			</p>
		</div>
	);
};

export default WelcomeMessage;
