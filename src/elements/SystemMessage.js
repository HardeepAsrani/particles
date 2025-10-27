/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

import { addCard, Icon } from '@wordpress/icons';

const SystemMessage = ({ message }) => {
	return (
		<div className="flex items-start gap-2">
			<div className="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
				<Icon className="w-4 h-4 fill-white" icon={addCard} />
			</div>
			<div className="flex-1">
				<p className="text-sm font-semibold text-gray-800 mb-2">
					{__('Particles', 'particles')}
				</p>
				<div className="bg-white border border-gray-200 rounded p-3">
					<div className="space-y-1 text-sm">
						<span className="text-gray-700">{message}</span>
					</div>
				</div>
			</div>
		</div>
	);
};

export default SystemMessage;
