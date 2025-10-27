const UserMessage = ({ message, timestamp }) => {
	return (
		<div className="flex justify-end">
			<div className="bg-gray-100 rounded-lg p-3 max-w-xs">
				<p className="text-sm text-gray-800">{message}</p>
				<span className="text-xs text-gray-500 mt-1 block">
					{timestamp}
				</span>
			</div>
		</div>
	);
};

export default UserMessage;
