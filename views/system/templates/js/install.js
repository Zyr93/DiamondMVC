require(['diamondmvc', 'jquery'], function( mvc, $ ) {
	$(document.body).on('filebrowser.initiated', '.view-filebrowser', function() {
		var dropzone = $(this).find('.modal-upload form').data('dropzone-object'),
			acceptedFiles = dropzone.options.acceptedFiles || '';
		acceptedFiles = acceptedFiles.split(',');
		acceptedFiles.push('.zip');
		dropzone.options.acceptedFiles = acceptedFiles.join(',');
		
		$('#btn-launch-installation').click(function(){
			var base = $('#view-system.view-install .filebrowser').data('base'),
				ids = [];
			$('#view-system.view-install .filebrowser tr.selected').each(function(){
				ids.push($(this).data('id'));
			});
			
			if( !ids.length ) {
				mvc.addNotification('Hey!', 'You need to select a file to install!', 'warn');
				return;
			}
			
			$('#view-system .modals .modal-installing').modal('show');
			$.ajax({
				url : DIAMONDMVC_URL + '/system/realinstall',
				data : {
					base : base,
					ids  : ids,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				if( response.success ) {
					mvc.addNotification('Wooh!', 'The extension(s) was/were successfully installed! Return to the overview to check them out!', 'success');
				}
				else {
					if( response.msg ) {
						mvc.addNotification('Oh noes!', response.msg, 'error');
					}
					else {
						mvc.addNotification('Oh noes!', 'Something went wrong during the installation!', 'error');
					}
					console.log('Failed to install extension', response);
				}
			})
			.fail(function( xhr, errmsg, error ) {
				mvc.addNotification('What?!', 'Either the server did not respond in time or I received a malformed response! What is happening?!', 'error');
				console.log('Failed to install extension', xhr, errmsg, error);
			})
			.complete(function(){
				$('#view-system .modals .modal-installing').modal('hide');
			});
		});
	});
});
