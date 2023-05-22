#! /bin/sh

# Installation prefix
prefix=../gps_daemon

# Where to keep a log file
LOGFILE="logtcp"
PIDFILE="$prefix/pidfile"

## STOP EDITING HERE

# Check for echo -n vs echo \c
if echo '\c' | grep -s c >/dev/null 2>&1 ; then
    ECHO_N="echo -n"
    ECHO_C=""
else
    ECHO_N="echo"
    ECHO_C='\c'
fi

# The path that is to be used for the script
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# What to use to start up the postmaster (we do NOT use pg_ctl for this,
# as it adds no value and can cause the postmaster to misrecognize a stale
# lock file)
DAEMON="$prefix/gpsdaemon_tcp.php"
ARGS="-p 7777 -v"

set -e

# Only start if we can find the postmaster.
test -x $DAEMON || exit 0

# Parse command line parameters.
case $1 in
	start)
		$ECHO_N "Starting DAEMON: "$ECHO_C
		#	$DAEMON $ARGS & >>$LOGFILE 2>&1
		if [ -f $PIDFILE ] ; then
			PID=`cat $PIDFILE`
			if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
				echo " Error: El proceso ($PID) ya se está ejecutando."
				exit 0
			fi
		fi
		$DAEMON $ARGS >>$LOGFILE 2>&1 &
		echo $! > $PIDFILE
		echo "ok"
	;;
	stop)
		echo -n "Stopping DAEMON: "
		if [ -f $PIDFILE ] ; then
			PID=`cat $PIDFILE`
			if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
				kill $PID &
				wait
				sleep 1
				if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
					echo " Error: No se pudo terminar el proceso ($PID)"
					exit 0
				fi
				rm $PIDFILE
				echo "Ok"
				exit 0
			else
				STATUS="DAEMON (pid $PID?) not running"
				RUNNING=0
			fi
		else
			STATUS="DAEMON (no pid file) not running"
			RUNNING=0
		fi
		echo "Error: $STATUS"
	;;
	restart)
		echo "Restarting DAEMON: "
		$0 stop
		$0 start
	;;
	check)
		if [ -f $PIDFILE ] ; then
			PID=`cat $PIDFILE`
			if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
				STATUS="DAEMON (pid $PID) running"
				RUNNING=1
			else
				STATUS="DAEMON (pid $PID?) not running"
				RUNNING=0
			fi
		else
			STATUS="DAEMON (no pid file) not running"
			RUNNING=0
		fi
		if [ "x$RUNNING" != "x1" ] ; then
			fecha=eval date
			echo $fecha
			echo $STATUS
			$0 start
		fi
	;;
	status)
		if [ -f $PIDFILE ] ; then
			PID=`cat $PIDFILE`
			if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
				STATUS="DAEMON (pid $PID) running"
				RUNNING=1
			else
				STATUS="DAEMON (pid $PID?) not running"
				RUNNING=0
			fi
		else
			STATUS="DAEMON (no pid file) not running"
			RUNNING=0
		fi
		echo $STATUS
	;;
	*)
	# Print help
	echo "Usage: $0 {start|stop|restart|status}" 1>&2
	exit 1
	;;
esac

exit 0
