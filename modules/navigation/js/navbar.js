require(['diamondmvc', 'jquery'], function( mvc, $ ) {
	var w = window, d = document, b = d.body,
		dropdownMenuWidth = 300;
	
	$(function(){
		// Make dropdown menus work.
		$('.module-navigation.navbar .dropdown').click(function(e){
			e.preventDefault();
			
			// Make sure we can arrange the dropdown menu container relatively to this element.
			$(this).css('position', 'relative');
			
			// TODO: Arrange it to be correct.
			$(this).children('ul, ol').css({
				position: absolute,
				top:      $(this).height(),
				left:     0,
				width:    dropdownMenuWidth,
			});
		});
	});
	
	function showDropdownMenu( menu ) {
		var $menu = $(menu);
		$menu.css('position', 'relative');
		
		$menu.children('ul, ol').css({
			position: 'absolute',
			top:      $menu.height(),
			left:     0,
			width:    dropdownMenuWidth,
		});
	}
});
