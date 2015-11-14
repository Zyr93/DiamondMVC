(function( $ ) {
	$(function(){
		$('#btn_a-note-ahead_continue').click(function( evt ){
			evt.preventDefault();
			$('#tablbl-general-settings').click();
		});
		$('#btn_general-settings_next').click(function( evt ) {
			evt.preventDefault();
			$('#tablbl-database-settings').click();
		});
		$('#btn_database-settings_previous').click(function( evt ) {
			evt.preventDefault();
			$('#tablbl-general-settings').click();
		});
	});
})(jQuery);
