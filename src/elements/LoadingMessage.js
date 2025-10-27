/**
 * WordPress dependencies.
 */
import { rotateRight, Icon } from '@wordpress/icons';

const LoadingMessage = ({ message }) => {
	return (
		<div className="flex items-center gap-2">
			<div className="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
				<Icon
					className="w-4 h-4 fill-white animate-spin"
					icon={rotateRight}
				/>
			</div>
			<div className="flex items-center gap-2 text-xs text-gray-500 mt-1">
				<span>{message}</span>
			</div>
		</div>
	);
};

export default LoadingMessage;
