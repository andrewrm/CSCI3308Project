$(function() {
	$( "#tags" ).autocomplete({
		minLength: 0,
		source: available_bars,
		focus: function( event, ui ) {
			$( "#tags" ).val( ui.item.label );
			$( "#tagged-bar" ).val( ui.item.value );
			return false;
		},
		select: function( event, ui ) {
			$( "#tags" ).val( ui.item.label );
			$( "#tagged-bar" ).val( ui.item.value );
			return false;
		}
	});
});