/**
 * Simple client side filebrowser engine
 */
// TODO: After the introduction of our AJAX framework, revampt this entire process.
require(['diamondmvc', 'jquery', 'dropzone'], function( mvc, $, Dropzone ) {
	$(function(){
		$(document.body).on('click', '.filebrowser tbody tr', function(e){
			e.preventDefault();
			$(this).toggleClass('selected');
		})
		.on('dblclick', '.filebrowser tbody tr', function(e){
			e.preventDefault();
			var $filebrowser = $(this).closest('.filebrowser'),
				base         = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl'),
				id           = $(this).data('id');
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			if( !$('.fa-folder-o', e.target).length ) {
				return;
			}
			
			$.ajax({
				url : actionUrl + '/browse',
				data : {
					actionUrl : actionUrl,
					base : base,
					id   : id,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ){
				if( response.success ) {
					var parent = $filebrowser.closest('.view-filebrowser').parent();
					$filebrowser.closest('.view-filebrowser').html($(response.html).html());
					requestDirectorySizes(parent.find('.filebrowser'));
				}
				else {
					mvc.addNotification('Whaaat?', response.msg, 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('Filebrowser: Failed to browse directory (base, id)', base, id, xhr, errmsg, error);
				mvc.addNotification('Sorry!', 'But I couldn\'t manage to browse deeper into the selected directory!', 'error');
			});
		})
		.on('click', '.filebrowser-control-delete', function(e) {
			e.preventDefault();
			var $view        = $(this).closest('.view-filebrowser'),
				$filebrowser = $view.find('.filebrowser'),
				base         = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl'),
				ids          = [];
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			$filebrowser.find('tr.selected').each(function(){
				ids.push($(this).data('id'));
			});
			
			$.ajax({
				url : actionUrl + '/delete',
				data : {
					base : base,
					ids  : ids,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ){
				if( response.success ) {
					$view.find('.filebrowser-control-refresh').click();
				}
				else {
					console.log(response.msg, response);
					mvc.addNotification('Sorry!', 'I could not delete your selected files!', 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('FileBrowser: failed to delete files in (base) with IDs (ids)', base, ids, xhr, errmsg, error);
				mvc.addNotification('Sorry!', 'I could not delete your selected files!', 'error');
			});
		})
		.on('click', '.filebrowser-control-rename', function(e) {
			e.preventDefault();
			if( !$(this).closest('.view-filebrowser').find('.filebrowser tr.selected').length ) {
				mvc.addNotification('Hey!', 'You forgot to select files to rename!', 'warn');
			}
			else {
				var $modal = $(this).closest('.view-filebrowser').find('.modals .modal-rename')
					.modal('show')
					.one('shown.bs.modal', function() {
						$modal.find('input').focus();
					});
			}
		})
		.on('click', '.modals .modal-rename .modal-btn-confirm', function(e) {
			e.preventDefault();
			var $view        = $(this).closest('.view-filebrowser'),
				$filebrowser = $view.find('.filebrowser'),
				base         = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl'),
				ids          = [],
				name         = $view.find('.modals .modal-rename input').val().trim();
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			if( !name.length ) {
				mvc.addNotification('Hey!', 'I need a name if you want me to rename your files!', 'warn');
				return;
			}
			
			$filebrowser.find('tr.selected').each(function(){
				ids.push($(this).data('id'));
			});
			
			$.ajax({
				url : actionUrl + '/rename',
				data : {
					base : base,
					ids  : ids,
					name : name,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				if( response.success ) {
					$view.find('.modals .modal-rename').modal('hide');
					$view.find('.filebrowser-control-refresh').click();
				}
				else {
					console.log(response.msg, response);
					mvc.addNotification('Sorry!', 'I could not rename your files!', 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('FileBrowser: failed to rename files in (base) with IDs (ids)', base, ids, xhr, errmsg, error);
				mvc.addNotification('Sorry!', 'I couldn\'t rename your selected files!', 'error');
			});
		})
		.on('click', '.filebrowser-control-refresh', function(e) {
			e.preventDefault();
			var $filebrowser = $(this).closest('.view-filebrowser').find('.filebrowser'),
				base         = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl');
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			$.ajax({
				url : actionUrl + '/browse',
				data : {
					base : base,
					id   : '.',
					actionUrl : actionUrl,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				if( response.success ) {
					var $view = $filebrowser.closest('.view-filebrowser').html($(response.html).html());
					requestDirectorySizes($view.find('.filebrowser'));
					initDropzone($view)
				}
				else {
					console.log(response.msg, response);
					mvc.addNotification('Sorry!', response.msg, 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('FileBrowser: failed to refresh the view!', base, xhr, errmsg, error);
				mvc.addNotification('Sorry!', 'Failed to refresh your view!', 'error');
			});
		})
		.on('click', '.filebrowser-control-cut, .filebrowser-control-copy', function(e) {
			e.preventDefault();
			var $view        = $(this).closest('.view-filebrowser'),
				$filebrowser = $view.find('.filebrowser'),
				base         = $filebrowser.data('base'),
				ids          = [];
			
			$filebrowser.find('tr.selected').each(function(){
				ids.push($(this).data('id'));
			});
			
			if( !ids.length ) {
				mvc.addNotification('Hey!', 'First you need to select files to copy!', 'warn');
				return;
			}
			
			$view.data('filebrowser-clipboard-base', base)
				 .data('filebrowser-clipboard-ids',  ids );
		})
		.on('click', '.filebrowser-control-cut', function(e) {
			e.preventDefault();
			$(this).closest('.view-filebrowser').data('filebrowser-clipboard-action', 'move');
		})
		.on('click', '.filebrowser-control-copy', function(e) {
			e.preventDefault();
			$(this).closest('.view-filebrowser').data('filebrowser-clipboard-action', 'copy');
		})
		.on('click', '.filebrowser-control-paste', function(e) {
			e.preventDefault();
			var $view        = $(this).closest('.view-filebrowser'),
				$filebrowser = $view.find('.filebrowser'),
				baseSrc      = $view.data('filebrowser-clipboard-base'),
				baseTgt      = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl'),
				idsSrc       = $view.data('filebrowser-clipboard-ids'),
				action       = $view.data('filebrowser-clipboard-action');
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			if( !baseSrc || !idsSrc || !action ) {
				mvc.addNotification('Oops!', 'Nothing to paste.', 'warn');
				return;
			}
			
			$.ajax({
				url : actionUrl + '/' + $view.data('filebrowser-clipboard-action'),
				data : {
					baseSrc : baseSrc,
					baseTgt : baseTgt,
					ids  : idsSrc,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				if( response.success ) {
					$view.find('.filebrowser-control-refresh').click();
				}
				else {
					console.log(response.msg, response);
					mvc.addNotification('Sorry!', response.msg, 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('FileBrowser: failed to paste files', base, ids, xhr, errmsg, error)
				mvc.addNotification('Sorry!', 'I could not paste your files!', 'error');
			});
		})
		.on('click', '.filebrowser-control-upload', function(e) {
			e.preventDefault();
			$(this).closest('.view-filebrowser').find('.modals .modal-upload').modal('show');
		})
		.on('hidden.bs.modal', '.modal-upload', function(e) {
			$(this).closest('.view-filebrowser').find('.filebrowser-control-refresh').click();
		})
		.on('click', '.filebrowser-control-mkdir', function(e) {
			e.preventDefault();
			var $modal = $(this).closest('.view-filebrowser').find('.modals .modal-mkdir')
				.modal('show')
				.one('shown.bs.modal', function() {
					$modal.find('input').focus();
				});
		})
		.on('click', '.modals .modal-mkdir .modal-btn-confirm', function(e) {
			e.preventDefault();
			var $view        = $(this).closest('.view-filebrowser'),
				$filebrowser = $view.find('.filebrowser'),
				base         = $filebrowser.data('base'),
				actionUrl    = $filebrowser.data('actionUrl'),
				id           = $(this).closest('.modal').find('input').val().trim();
			
			if( !actionUrl ) {
				return noActionUrlError($filebrowser);
			}
			
			$.ajax({
				url : actionUrl + '/mkdir',
				data : {
					base : base,
					id   : id,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				if( response.success ) {
					$view.find('.modals .modal-mkdir').modal('hide');
					$view.find('.filebrowser-control-refresh').click();
				}
				else {
					console.log(response.msg, response);
					mvc.addNotification('Sorry!', response.msg, 'error');
				}
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('FileBrowser: failed to create directory', base, id, xhr, errmsg, error);
				mvc.addNotification('Sorry!', 'I could not create your directory!', 'error');
			});
		})
		.on('keyup', '.modals .modal-mkdir input, .modals .modal-rename input', function(e) {
			if( e.which === 13 ) {
				e.preventDefault();
				$(this).closest('.modal').find('.modal-btn-confirm').click();
			}
		})
		.on('keyup', function(e) {
			if( e.which === 113 ) {
				$('.view-filebrowser').each(function(){
					// Only open renaming dialog for filebrowsers with selected rows
					if( $(this).find('tr.selected').length ) {
						$(this).find('.filebrowser-control-rename').click();
					}
				})
			}
			else if( e.which === 67 && e.ctrlKey ) {
				$('.filebrowser-control-copy').click();
			}
			else if( e.which === 88 && e.ctrlKey ) {
				$('.filebrowser-control-cut').click();
			}
			else if( e.which === 86 && e.ctrlKey ) {
				$('.filebrowser-control-paste').click();
			}
			else if( e.which === 77 && e.ctrlKey ) {
				$('.filebrowser-control-mkdir').click();
			}
			else if( e.which === 82 && (e.altKey || e.ctrlKey) ) {
				$('.filebrowser-control-refresh').click();
			}
			else if( e.which === 46 ) {
				$('.filebrowser-control-delete').click();
			}
			else if( e.which === 85 && e.ctrlKey ) {
				$('.filebrowser-control-upload').click();
			}
		});
		
		// Initiate the dropzone in the upload modal on every available file browser and request the sizes of its directories.
		$('.view-filebrowser').each(function(){
			initDropzone(this);
			requestDirectorySizes($(this).find('.filebrowser'));
			$(this).trigger('filebrowser.initiated');
		});
	});
	
	/**
	 * Initiates the dropzone in the upload modal for a particular view. This allows us to update a
	 * single view without reinitializing the dropzones of other views - which leads to an error anyway.
	 */
	function initDropzone( view ) {
		$(view).find('.modals .modal-upload form').addClass('dropzone').dropzone({
			paramName : 'filebrowser-upload',
			init : function( ) {
				var $this = $(this.element).data('dropzone-object', this);
				
				// Make sure we send the filebrowser base ID along with the request so the server even knows where to save the file.
				this.on('sending', function( file, xhr, formData ) {
					var base = $this.closest('.view-filebrowser').find('.filebrowser').data('base');
					formData.append('type', 'json'); // Required to get an easily parsable response from the server
					formData.append('base', base);   // Required to know where the hell to save the file
				});
				
				// Mandatory error handling
				this.on('error', function( file, errmsg, xhr ) {
					mvc.addNotification('Whoops!', errmsg, 'error');
					console.log('Failed to upload file', file, errmsg, xhr);
				});
				
				// Validate the response of the server and check if it actually did accept the file.
				this.on('success', function( file, response ) {
					var json = {};
					try {
						json = JSON.parse(response);
					}
					catch( ex ) {
						mvc.addNotification('Oh no!', 'We received a malformed response from the server. I don\'t know what happened!', 'error');
						console.log('Malformed server response', file, response);
						return;
					}
					
					if( !json.success ) {
						mvc.addNotification('Oh no!', 'It would appear the server rejected your file! Here\'s its message: ' + json.msg, 'warn');
						console.log('Server rejected file', file, json);
					}
				});
			},
		});
	}
	
	function requestDirectorySizes( filebrowser ) {
		var $filebrowser = $(filebrowser),
			base         = $filebrowser.data('base'),
			actionUrl    = $filebrowser.data('actionUrl');
		
		if( !actionUrl ) {
			return noActionUrlError($filebrowser);
		}
		
		$filebrowser.find('tbody tr').each(function(index, elem){
			var $elem = $(elem),
				id    = $elem.data('id');
			
			// Only request the sizes of folders as sizes of files are already delivered with the request
			if( !$elem.find('.fa-folder-o').length ) {
				return;
			}
			
			// Skip the "up" special item
			if( $elem.find('td.file-info-name').text().trim() === '..' ) {
				return;
			}
			
			$.ajax({
				url : actionUrl + '/size',
				data : {
					base : base,
					id   : id,
					type : 'json',
				},
				type : 'get',
				dataType : 'json',
			})
			.done(function( response ) {
				console.log('Filebrowser size response for element:', $elem, response);
				$elem.find('td.file-info-size').text(response);
			})
			.fail(function( xhr, errmsg, error ) {
				console.log('Filebrowser: Error while attempting to get directory size', xhr, errmsg, error);
				$elem.find('td.file-info-size').text('N/A');
			});
		})
	}
	
	function noActionUrlError( $filebrowser ) {
		$filebrowser = $($filebrowser);
		console.log('FileBrowser: no action URL specified for file browser', $filebrowser);
		mvc.addNotification('Error', 'See the logs for more information', 'error');
	}
});
