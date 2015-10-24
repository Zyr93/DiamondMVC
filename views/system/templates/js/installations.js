require(['diamondmvc', 'jquery'], function( mvc, $ ) {
	$(function(){
		$('#view-system .view-installations tbody tr').addClass('clickable').click(function(){
			window.location.href = $('.col-name a', this).attr('href');
		});
	});
});
