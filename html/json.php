<?php
set_include_path( get_include_path() . PATH_SEPARATOR . '/var/feeder' );

require_once( 'config.php' );

header( 'Content-Type: application/json' );

$db 		= new SQLite3( _database );
$function 	= Functions::Post( 'Function' );

if ( function_exists( $function ) )
{
	call_user_func( $function, $db );
}
else
{
	JSON::Error( 'Invalid Function value' );
}

exit( 0 );

function Feed( $db )
{
	$force 	= Functions::Post_Boolean( 'force' );
	$data	= array( 'confirm' => false, 'message' => '' );

	if ( !$force )
	{
		if ( !Feeder::Load_Last_Feed( $db, $feed ) )
		{
			return Functions::Error( $db->lastErrorMsg() );
		}

		if ( !empty( $feed ) )
		{
			$now 		= new DateTime();
			$lastfeed 	= new DateTime();
			$lastfeed->setTimestamp( $feed[ 'time' ] );
			$diff 		= $lastfeed->diff( $now );

			if ( $diff->h < 18 )
			{
				$data = array( 'confirm' => true,
							   'message' => sprintf( "Jazzy was feed %d %s %d %s and %d %s ago.\n\nAre you sure you want to feed her again?", $diff->h, Functions::Plural( $diff->h, 'hour' ), $diff->i, Functions::Plural( $diff->i, 'minute' ), $diff->s, Functions::Plural( $diff->s, 'second' ) ) );

				return JSON::Success( $data );
			}
		}
	}

	if ( !Functions::Feed( $db ) )
	{
		return JSON::Error();
	}

	return JSON::Success( $data );
}

function FeederList_Load_Query( $db )
{
	if ( !Feeder::List_Load_Query( $db, $feeder ) )
	{
		return JSON::Error();
	}

	return JSON::Success( $feeder );
}

?>