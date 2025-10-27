/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { check, Icon } from '@wordpress/icons';

const ConfirmationMessage = ({ message, onInsert, onRefine }) => {
	return (
		<div className="flex items-start gap-2">
			<div className="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
				<Icon className="w-4 h-4 fill-white" icon={check} />
			</div>

			<div className="flex-1">
				<p className="text-sm text-gray-800 mb-3">{message}</p>

				<div className="flex gap-2">
					<button
						onClick={onInsert}
						className="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded cursor-pointer transition"
					>
						{__('Insert', 'particles')}
					</button>

					<button
						onClick={onRefine}
						className="bg-white border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded cursor-pointer hover:bg-gray-50 transition"
					>
						{__('Reject', 'particles')}
					</button>
				</div>
			</div>
		</div>
	);
};

export default ConfirmationMessage;
