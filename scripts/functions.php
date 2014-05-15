<?php
class Functions
{
	static function Feed( $db, $automated = false )
	{
		$message 	= '';
		$method		= $automated ? 'a' : 'm';
		$ip			= isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : '192.168.1.10';

		for( $i = 0; $i < _attempts; $i++ )
		{
			$return_value = Functions::Serial_Communication( $stderr );

			if ( $return_value === 0 )
			{
				break;
			}

			sleep( 2 );
		}

		if ( $return_value !== 0 )
		{
			$method		= 'e';
			$message 	= sprintf( "(%d) %s", $return_value, $stderr );
		}
		
		$feeder = array( 'method' => $method, 'time' => time(), 'message' => $message, 'ip' => $ip );

		if ( !Feeder::Insert( $db, $feeder ) )
		{
			return Functions::Error( $db->laseErrorMsg );
		}

		if ( $message )
		{
			return Functions::Error( sprintf( "Failed to feed Jazzy after %d %s\n\n%s", _attempts, Functions::Plural( _attempts, 'attempt' ), $message ) );
		}		

		return true;
	}

	static function Serial_Communication( &$stderr )
	{
		$process = proc_open( _which_python . ' ' . _serial_script, array( 0 => array( 'pipe', 'r' ),
																		   1 => array( 'pipe', 'w' ),
																		   2 => array( 'pipe', 'w' ) ), $pipes );
		$stdout	= stream_get_contents( $pipes[ 1 ] );
		fclose( $pipes[ 1 ] );

		$stderr = stream_get_contents( $pipes[ 2 ] );
		fclose( $pipes[ 2 ] );

		$return_value = proc_close( $process );

		if ( $return_value != 0 )
		{
			$stderr = is_null( $stderr ) ? 'Failed to feed Jazzy!' : $stderr;

			return $return_value;
		}

		return 0;
	}

	static function Error( $message )
	{
		global $error_message;

		$error_message = $message;

		return false;
	}

	static function Post( $index )
	{
		return ( isset( $_POST[ $index ] ) ) ? trim( $_POST[ $index ] ) : '';
	}

	static function Post_Boolean( $index )
	{
		if ( !isset( $_POST[ $index ] ) || is_null( $_POST[ $index ] ) ||  $_POST[ $index ] == '0' || $_POST[ $index ] == 'false' )
		{
			return false;
		}

		return true;
	}

	static function Plural( $i, $word, $plural = null )
	{
		if ( $i === 1 )
		{
			return $word;
		}

		return is_null( $plural ) ? sprintf( '%ss', $word ) : $plural;
	}
}

class Feeder
{
	public static function Insert( $db, $feeder )
	{
		$stmt = $db->prepare( 'INSERT INTO feeder ( method, time, ip, message ) VALUES ( :method, :time, :ip, :message )' );
		$stmt->bindValue( ':method', 	$feeder[ 'method' ], 		SQLITE3_TEXT );
		$stmt->bindValue( ':time', 		$feeder[ 'time' ],			SQLITE3_INTEGER );
		$stmt->bindValue( ':ip',		$feeder[ 'ip' ],			SQLITE3_TEXT );
		$stmt->bindValue( ':message',	$feeder[ 'message' ],		SQLITE3_TEXT );

		return $stmt->execute();
	}

	public static function Delete( &$db, $id )
	{
		$stmt = $db->prepare( 'DELETE FROM feeder WHERE id = :id' );
		$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );

		return $stmt->execute();
	}

	public static function Load_Last_Feed( &$db, &$feed )
	{
		$feed = $db->querySingle( 'SELECT * FROM feeder WHERE method <> \'e\' ORDER BY id DESC LIMIT 1', true );

		return ( $feed === false ) ? false : true;
	}

	public static function List_Load_Query( &$db, &$results )
	{
		$query 		= $db->query( 'SELECT * FROM feeder ORDER BY time DESC' );
		$results 	= array();

		while( $row = $query->fetchArray( SQLITE3_ASSOC ) )
		{
			$datetime = new DateTime();
			$datetime->setTimestamp( $row[ 'time' ] );
			$row[ 'formatted_date' ] = $datetime->format( 'm/d/y h:i:s A' );

			switch( $row[ 'method' ] )
			{
				case 'm':
					$row[ 'formatted_method' ] = 'Manually';
					break;

				case 'a':
					$row[ 'formatted_method' ] = 'Automated';
					break;

				case 'e':
					$row[ 'formatted_method' ]	= 'Error';
					break;

				default:
					$row[ 'formatted_method' ] = 'Unknown';
					break;
			}

			if ( preg_match( '/^173\.196/', $row[ 'ip' ] ) )
			{
				$row[ 'location' ] = 'Work';
			}
			else if ( preg_match( '/^174\.65|^192\.168/', $row[ 'ip' ] ) )
			{
				$row[ 'location' ] = 'Home';
			}
			else
			{
				$row[ 'location' ] = 'Unknown';
			}

			array_push( $results, $row );
		}

		return true;
	}
}

class JSON
{
	public static function Success( $data = '' )
	{
		if ( $data == '' )
		{
			die( json_encode( array( 'success' => true ) ) );
		}

		die( json_encode( array( 'success' => true, 'data' => $data ), JSON_PRETTY_PRINT ) );
	}

	public static function Error( $message = '' )
	{
		if ( $message == '' )
		{
			global $error_message;
			$message = $error_message;
		}

		die( json_encode( array( 'success' => false, 'error_message' => $message ) ) );
	}
}
?>
