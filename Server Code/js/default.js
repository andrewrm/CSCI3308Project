$(function() { $("#LeftPanelMenu").menu();});

$( document ).ready( function () {
	
	$( '#ingredients-list > li > a.remove, #directions-list > li > a.remove' ).click( function ( e ) {
		e.preventDefault();
		if ( $( this ).parent().siblings().length > 0 ) {
			$( this ).parent().remove();
		}
	} );
	
	$( '#add-ingredient' ).click( function ( e ) {
		e.preventDefault();
		var clone = $( '#ingredients-list > li:last-child' ).clone( true, true );
		clone.children( 'input[type="text"]' ).val( '' );
		clone.appendTo( $( '#ingredients-list' ) );
	} );
	
	$( '#add-direction' ).click( function ( e ) {
		e.preventDefault();
		var clone = $( '#directions-list > li:last-child' ).clone( true, true );
		clone.children( 'input[type="text"]' ).val( '' );
		clone.appendTo( $( '#directions-list' ) );
	} );
	
	$( '#directions-list' ).sortable( { axis: 'y', cancel: 'a,input', cursor: 'move' } );
} );
