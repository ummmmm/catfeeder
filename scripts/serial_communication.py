import sys
import serial

try:
	ser = serial.Serial( "/dev/ttyACM0", 9600, timeout = 2, writeTimeout = 2 )
except:
	print >> sys.stderr, "Failed to open /dev/ttyACM0"
	sys.exit( 3 )

ready = ser.readline().rstrip()

if ready != "Ready":
	ser.close()
	print >> sys.stderr, "Arduino was not ready"
	sys.exit( 1 )

ser.write( 'FEED' )
feeding = ser.readline().rstrip()

if feeding != "Success":
	ser.close()
	print >> sys.stderr, feeding
	sys.exit( 2 )

ser.close()
sys.exit( 0 )
