/* PINS */
const int 	_pin_motor_out	= 12;
const int 	_pin_led_out	= 13;

/* SERIAL BUFFER */
const int 	_buffer_size	= 10;
int 		_buffer_length 	= 0;
char 		_buffer[ _buffer_size ];
char 		_buffer_byte;

const int	_baud	= 9600;
int			_speed	= 650;

void setup()
{
	pinMode( _pin_motor_out,	OUTPUT );
	pinMode( _pin_led_out,		OUTPUT );

	Serial.begin( _baud );
	Serial.println( "Ready" );
}

void loop()
{
	_buffer_length = 0;

	while( Serial.available() )
	{
		_buffer_byte = Serial.read();

		if ( _buffer_length + 1 < _buffer_size )
		{
			_buffer[ _buffer_length++ ] = _buffer_byte;
			_buffer[ _buffer_length ] 	= '\0';
		}

		delay( 2 );
	}

	if ( _buffer_length > 0 )
	{
		if ( strcmp( _buffer, "FEED" ) == 0 )
		{
			feed_jazzy();
		}
		else
		{
			Serial.print( "Unrecognized command: " );
			Serial.println( _buffer );			
		}
	}
}

void feed_jazzy()
{
	digitalWrite( _pin_led_out, 	HIGH );
	digitalWrite( _pin_motor_out, 	HIGH );
	
	delay( _speed );
	
	digitalWrite( _pin_motor_out, 	LOW );
	digitalWrite( _pin_led_out, 	LOW );

	Serial.println( "Success" );
}
