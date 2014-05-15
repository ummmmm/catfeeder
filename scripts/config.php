<?php
define( '_attempts', 		5 );
define( '_path', 			'/var/feeder' );
define( '_which_python',	'/usr/bin/python2.7' );
define( '_serial_script',	_path . '/serial_communication.py' );
define( '_database', 		_path . '/feeder.db' );

require_once( _path . '/functions.php' );
?>