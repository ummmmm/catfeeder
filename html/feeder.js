$( document ).ready( function()
{
	$.fn.json = function( data, callback )
	{
		data = 
		$.ajax( {
			'url': 'json.php',
			'type':	'POST',
			'data':	data,
			'dataType': 'json',
			'success': function( response )
			{
				callback( response );
			},
			'error': function( jqXHR, textStatus, errorThrown )
			{
				var response = { 'success': false, 'error_message': 'The server returned an invalid response.\nResponse: ' + jqXHR.responseText };

				callback( response );
			}
		} );
	}

	$.fn.FeederList_Load_Query = function()
	{
		$.fn.json( { 'Function': 'FeederList_Load_Query' }, function( response )
		{
			if ( !response.success )
			{
				return alert( response.error_message );
			}

			$( '#feeder' ).text( '' );

			$.each( response.data, function( index, value )
			{
				var div 			= $( '<div/>', { 'class': 'entry' } );
				var method_span		= $( '<div/>', { 'style': 'width: 100px;', 'text': value.formatted_method } );
				var date_span 		= $( '<div/>', { 'text': value.formatted_date } );
				var location_span	= $( '<div/>', { 'text': ( value.location == 'Unknown' ) ? 'Unknown - ' + value.ip : value.location } );
				var message_span	= $( '<div/>', { 'text': value.message } );
				div.append( method_span );
				div.append( date_span );
				div.append( location_span );
				div.append( message_span );
				$( '#feeder' ).append( div );
			} );
		} );
	}

	$.fn.Feed = function( force )
	{
		$( '#overlay' ).show();
		//return;
		$.fn.json( { 'Function': 'Feed', 'force': force }, function( response )
		{
			if ( !response.success )
			{
				$( '#overlay' ).hide();
				alert( response.error_message );
			}

			if ( response.data.confirm )
			{
				if ( confirm( response.data.message ) )
				{
					return $.fn.Feed( true );
				}
			}

			$( '#overlay' ).hide();
			$.fn.FeederList_Load_Query();
		} );
	}
} );