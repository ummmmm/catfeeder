<?php
require_once( 'config.php' );

$db = new SQLite3( _database );

Functions::Feed( $db, true );
?>
