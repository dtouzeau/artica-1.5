#! /bin/bash

# cronspy - part of mailspy log htmlizer and summarizer
#
# A script designed to be run from cron as user mailspy (see crontab.spy)
# Launches htmlspy analysis and rotates the log
# Results end up as html documents ..
#
# Copyright (c) 2001, 2002 Andrew McGill and Leading Edge Business Solutions
# (South Africa).  
#
# Mailspy is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# This should bear some resemblance to what you read in rcmailspy ... :
RUNDIR="/var/run/mailspy"
MAILSPYSOCKET=$RUNDIR/milter
MAILSPYPIDFILE=$RUNDIR/pid
LOGDIR=/var/log/mailspy
LOGFILE="${LOGFILE:=/var/log/mailspy/mailspy}"
MAILSPYUSER=mailspy
REVISION='$Id: cronspy.sh,v 1.22 2006/05/16 12:52:15 andrewm Exp $'
# Number of history entries to make
WEEKS=52
DAYS=$((WEEKS * 52))

BASEDIR="${BASEDIR:=/usr/local/httpd/htdocs/mailspy}"
HTMLSPY="${HTMLSPY:=nice /usr/local/bin/htmlspy.pl}"
umask 022

# Print a header
function htmlheader()
{
	HEADING="$1"
echo "<HTML>
<HEAD>
<TITLE>$HEADING</TITLE>
</HEAD>
<BODY>
<H1>$HEADING</H1>";
}

function htmlfooter()
{
echo "<HR><FONT size=-4>Generated at `date` by $HOSTNAME<br>$REVISION
</FONT></BODY></HTML>";
}

function setuphtaccess()
{
HTTPDCONF=/etc/httpd/httpd.conf
[ -w $HTTPDCONF ] && {
grep -q "Added by mailspy" $HTTPDCONF || echo "
# --mailspy begin--
# Added by mailspy - allow .htaccess files in $BASEDIR
<Directory $BASEDIR>
    AllowOverride AuthConfig Limit
</Directory>
# --mailspy end--
" >> $HTTPDCONF
}

DIRNAME=`cd $REPORTDIR; pwd -P`
AUTHUSERFILE="$DIRNAME/.htpasswd"
touch "$AUTHUSERFILE"
echo "AuthUserFile $AUTHUSERFILE
AuthGroupFile /dev/null
AuthName \"Mailspy files for `basename $DIRNAME`\"
AuthType Basic

<Limit GET POST>
require valid-user
</Limit>
" > "$REPORTDIR/.htaccess"
echo 1>&2 ".htaccess in $DIRNAME"
}

# Rotate log files and report directories in sync
function rotatereports()
{

	# Remove oldest report dir
	rm -rf "$REPORTDIR/$WEEKS"

	for (( a=WEEKS;a>1;a-- )) ; do 
		mv -f "$REPORTDIR/$((a-1))" "$REPORTDIR/$a"
	done
}

# Rotate log files and report directories in sync
function rotatelogs()
{
	# Rotate logfiles: assumes that all the files exist
	# /var/log/mailspy.5 -> /var/log/mailspy.tmp
	# /var/log/mailspy.4 -> /var/log/mailspy.5
	# /var/log/mailspy.3 -> /var/log/mailspy.4
	# /var/log/mailspy.2 -> /var/log/mailspy.3
	# /var/log/mailspy.1 -> /var/log/mailspy.2
	# /var/log/mailspy -> /var/log/mailspy.1
	# Blank mailspy.tmp
	# /var/log/mailspy.tmp -> /var/log/mailspy

	# we may be running as mailspy, so we can't delete the file
	rm $LOGFILE.$WEEKS
	for (( a=WEEKS;a>1;a-- )) ; do 
		if ! [ -e $LOGFILE.$((a-1)) ] ; then
			touch $LOGFILE.$((a-1))
		fi
		mv -f $LOGFILE.$((a-1)) $LOGFILE.$a
		chown $MAILSPYUSER $LOGFILE.$a
	done
	# Rotate old log
	mv -f $LOGFILE   $LOGFILE.1
	chown $MAILSPYUSER $LOGFILE.1
	# New blank log
	touch $LOGFILE 
	chown $MAILSPYUSER $LOGFILE

	# Reopen log file
	killall -USR1 mailspy
}

# Make an index of stuff
function doupdateindex()
{
	{
	htmlheader "Mailspy reports"
	echo '<A href=current/index.html>Week-so-far report</A><BR>
	'
	( cd $REPORTDIR; ls --sort=time */range.txt ; ) |
	while read INDEXFILE ; do
		DIRNAME=${INDEXFILE/range.txt/}
		RANGE="`cat $REPORTDIR/$INDEXFILE`"
		[ "$RANGE" ] || continue
		set -- $RANGE
		FORMAT='+%Y %b %d %H:%M'
		STARTDATE="`date -d "$RANGE" "$FORMAT"`"
		echo " <A href=${DIRNAME}index.html>$STARTDATE</A><BR>";
	done
	htmlfooter
	} > "$REPORTDIR/index.html"
}

# Generate a report in the designated log directory
function generatereport()
{
	# generatereport .1 1
	LOG=$LOGFILE$1;
	DIROUT="$REPORTDIR/$2";
	mkdir -p "$DIROUT"
	$HTMLSPY < $LOG "$DIROUT" $USERLIST
	chown -R mailspy $DIROUT 2>/dev/null
	doupdateindex
}

# Set up directories in the way that we expect them
function initdirs()
{
	mkdir -p $RUNDIR
	chown -R $MAILSPYUSER $RUNDIR
	for (( a=WEEKS;a>0;a-- )) ; do 
		if ! [ -e $LOGFILE.$a ] ; then
			touch $LOGFILE.$a
			chown $MAILSPYUSER $LOGFILE.$a
		fi
	done
	mkdir -p $REPORTDIR
	chown $MAILSPYUSER $REPORTDIR
}

function dofunction()
{
case "$1" in 
	init)
		# secret initialisation thingy
		initdirs
		exit
		;;
	migrate-from-old-logfiles)
		# Migration routine  from /var/log/mailspy* to
		# /var/log/mailspy/*
		[ -f /var/log/mailspy ] && mv /var/log/mailspy /var/log/mailspy.0
		mkdir -p $LOGDIR
		chown mailspy $LOGDIR
		mkdir -p $HOME/mailspy

		echo "Writing /var/log/mailspy.* to $LOGDIR/logfiles"
		cat /var/log/mailspy.* > $LOGDIR/logfiles
		$0 redo-log-rotation-yeah
		;;
	redo-log-rotation)
		echo "Concatenating $LOGDIR/mailspy*"
		cat $LOGDIR/mailspy* > $LOGDIR/logfiles
		$0 redo-log-rotation-yeah
		;;
	redo-log-rotation-yeah)
		echo "Listing weeks to $LOGDIR/regex.*"
		FRIDAY=$(date -d 'next friday')
		for (( a=WEEKS;a>=0;a-- )) ; do 
			(
			let n=a*7
			date -d "$FRIDAY $((n+0)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+1)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+2)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+3)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+4)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+5)) days ago" "+^%y-%m-%d "
			date -d "$FRIDAY $((n+6)) days ago" "+^%y-%m-%d "
			) > $LOGDIR/regex.$a
		done

		echo -ne "Searching for week "
		for (( a=WEEKS;a>=0;a-- )) ; do 
			echo -ne " $a"
			if [ $a = 0 ] ; then ext= ; else ext=.$a; fi
			grep -f $LOGDIR/regex.$a < $LOGDIR/logfiles > $LOGDIR/mailspy$ext
		done
		echo

		echo "Setting ownership on $LOGDIR"
		chmod 644 $LOGDIR/mailspy*
		chown $MAILSPYUSER $LOGDIR/mailspy*
		;;
	regen)
		# Redo ALL the reports
		for (( a=WEEKS;a>0;a-- )) ; do 
			echo "Generating $a"
			generatereport ".$a" "$a"
		done
		generatereport "" "current"
		;;
	htaccess)
		setuphtaccess;
		;;
	doupdateindex)
		mkdir -p $REPORTDIR
		doupdateindex
		;;
	generatereport)
		# secret recovery thing (regenerate existing report)
		[ "$2" ] && generatereport ".$2" "$2";
		;;
	weekly)
		rotatereports
		rotatelogs
		generatereport ".1" "1";
		;;
	hourly)
		generatereport "" "current";
		;;
	daily)
		# Archive old procmailrc stuff
		[ -f ~mailspy/.procmailrc ] &&  {
		cd ~mailspy/Mail
		ARCHNAME=`date +'%y-%m-%d-%a-%h'`
		mv -f today $ARCHNAME 2>/dev/null
		COUNT=0
		ls -c | while read FILE ; do
			[ $((++COUNT)) -gt $DAYS ] && rm $FILE ;
		done
		> discarded
		}
		generatereport "" "current";
		;;
	*)
		echo "Usage: $0 [rotatelogs|weekly|daily|hourly|regen]"
		echo ""
		echo "  rotatelogs : rotates logs (must run weekly, if 'weekly' is not used)"
		echo "  weekly : generate html reports and rotates logs"
		echo "  hourly : generate current report (week so far)"
		echo "  daily  : rotate 'today' archive in ~mailspy/Mail, reset 'discarded' archive"
		echo "  regen  : regenerates ALL html pages (takes forever)"
		false
		return 1
		;;
esac
return 0
}


# If there is a dir called list/ in the reports directory, then it is used ...
mkdir -p "$BASEDIR"

# Only rotate the logs once - then reuse them for all the files
case "$1" in
	rotatelogs) rotatelogs ;;
esac

LISTDIR=$BASEDIR/lists
if [ -d "$LISTDIR" ] ; then
	for LISTFILE in `ls "$LISTDIR" ` ; do
		[ -f "$LISTDIR/$LISTFILE" ] || continue
		USERLIST="$LISTDIR/$LISTFILE"
		REPORTDIR="$BASEDIR/$LISTFILE"
		dofunction "$@" || exit
	done
fi
# List for all $BASEDIR/all/
USERLIST=""
REPORTDIR="$BASEDIR/all"
dofunction "$@" || exit 1

{
htmlheader "Mailspy reports - report list"
echo "<a href=all/index.html>All reports</a><br>"
if [ -d "$LISTDIR" ] ; then
	ls "$LISTDIR" | while read LISTFILE ; do
		HREF="$LISTFILE/index.html"
		DESC=$LISTFILE
		[ -f $LISTFILE/desc.txt ] && DESC="`cat $LISTFILE/desc.txt`"
		echo "<a href=$HREF>$DESC</a><br>"
	done
fi
htmlfooter
} > $BASEDIR/index.html
 
exit 0

