require(['diamondmvc', 'jquery'], function(mvc, $){
	var w = window, d = document;
	
	mvc.addNotification = function( title, notification, level ) {
		$('#messages').prepend(mvc.generateNotification(title, notification, level));
	};
});
