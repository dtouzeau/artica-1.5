<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}


if($argv[1]=='--build'){build();die();}
if($argv[1]=='--comps'){computers();die();}
if($argv[1]=='--resolv'){resolv_computers();die();}
if($argv[1]=='--backup-server'){set_backup_server();die();}




function build(){
	
	
$f[]="# default: on";
$f[]="# description: The amanda service";
$f[]="service amanda";
$f[]="{";
$f[]="#	only_from	= <Amanda server>";
$f[]="	socket_type	= stream";
$f[]="	protocol	= tcp";
$f[]="	wait		= no";
$f[]="	user		= root";
$f[]="	group		= root";
$f[]="	groups		= yes";
$f[]="	server		= /usr/lib/amanda/amandad";
$f[]="	server_args	= -auth=bsdtcp amdump amindexd amidxtaped";
$f[]="	disable		= no";
$f[]="}";
/*
$f[]="";
$f[]="service amandaidx";
$f[]="{";
$f[]="socket_type   = stream";
$f[]="protocol      = tcp";
$f[]="wait          = no";
$f[]="user          = root";
$f[]="group         = root";
$f[]="groups        = yes";
$f[]="server        = /usr/lib/amanda/amindexd";
$f[]="disable       = no";
$f[]="}";
$f[]="";
$f[]="service amidxtape";
$f[]="{";
$f[]="socket_type   = stream";
$f[]="protocol      = tcp";
$f[]="wait          = no";
$f[]="user          = root";
$f[]="group         = root";
$f[]="groups        = yes";
$f[]="server        = /usr/lib/amanda/amidxtaped";
$f[]="disable       = no";
$f[]="}";
$f[]="";
*/
@file_put_contents("/etc/xinetd.d/amanda", @implode("\n", $f));
echo "Starting......: Amanda server updating xinetd.d success\n";
buildmain();
if(is_file("/etc/init.d/xinetd")){echo "Starting......: Amanda server reloading xinetd\n";shell_exec("/etc/init.d/xinetd reload >/dev/null 2>&1");}
	
}


function buildmain(){
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AmandaServerConfig")));
	if(!isset($config["mailto"])){$config["mailto"]="root";}
	if(!isset($config["tapecycle"])){$config["tapecycle"]="6";}
	if(!isset($config["tapecycleMB"])){$config["tapecycleMB"]="3072";}
	if(!isset($config["netusage"])){$config["netusage"]="600";}
	if(!is_numeric($config["tapecycleMB"])){$config["tapecycleMB"]=3072;}
	if(!is_numeric($config["netusage"])){$config["netusage"]=600;}
	$unix=new unix();
	
	
	
$f[]="#";
$f[]="# amanda.conf - sample Amanda configuration file.";
$f[]="#";
$f[]="# If your configuration is called, say, \"DailySet1\", then this file";
$f[]="# normally goes in /etc/amanda/DailySet1/amanda.conf.";
$f[]="#";
$f[]="# You need to edit this file to suit your needs.  See the documentation in ";
$f[]="# this file, in the \"man amanda\" man page, in the /usr/share/docs/amanda*";
$f[]="# directories, and on the web at www.amanda.org for more information.";
$f[]="#";
$f[]="";
$f[]="org \"DailySet1\"			# your organization name for reports";
$f[]="mailto \"{$config["mailto"]}\"			# space separated list of operators at your site";
$f[]="dumpuser \"root\"		# the user to run dumps under";
$f[]="";
$f[]="inparallel 4		# maximum dumpers that will run in parallel (max 63)";
$f[]="			# this maximum can be increased at compile-time,";
$f[]="			# modifying MAX_DUMPERS in server-src/driverio.h";
$f[]="dumporder \"sssS\"	# specify the priority order of each dumper";
$f[]="			#   s -> smallest size";
$f[]="			#   S -> biggest size";
$f[]="			#   t -> smallest time";
$f[]="			#   T -> biggest time";
$f[]="			#   b -> smallest bandwitdh";
$f[]="			#   B -> biggest bandwitdh";
$f[]="			# try \"BTBTBTBTBTBT\" if you are not holding";
$f[]="			# disk constrained";
$f[]="";
$f[]="taperalgo first		# The algorithm used to choose which dump image to send";
$f[]="			# to the taper.";
$f[]="";
$f[]="			# Possible values: ";
$f[]="			#   [first|firstfit|largest|largestfit|smallest|last]";
$f[]="			# Default: first. ";
$f[]="";
$f[]="			# first		First in - first out.";
$f[]="			# firstfit 	The first dump image that will fit on ";
$f[]="			#		the current tape.";
$f[]="			# largest 	The largest dump image.";
$f[]="			# largestfit 	The largest dump image that will fit on";
$f[]="			#		 the current tape.";
$f[]="			# smallest 	The smallest dump image.";
$f[]="			# last 		Last in - first out.";
$f[]="";
$f[]="displayunit \"k\"		# Possible values: \"k|m|g|t\"";
$f[]="			# Default: k. ";
$f[]="			# The unit used to print many numbers.";
$f[]="			# k=kilo, m=mega, g=giga, t=tera";
$f[]="			";
$f[]="netusage  {$config["netusage"]} Kbps	# maximum net bandwidth for Amanda, in KB per sec";
$f[]="";
$f[]="dumpcycle 4 weeks	# the number of days in the normal dump cycle";
$f[]="runspercycle 20         # the number of amdump runs in dumpcycle days";
$f[]="			# (4 weeks * 5 amdump runs per week -- just weekdays)";
$f[]="tapecycle {$config["tapecycle"]} tapes	# the number of tapes in rotation";
$f[]="			# 4 weeks (dumpcycle) times 5 tapes per week (just";
$f[]="			# the weekdays) plus a few to handle errors that";
$f[]="			# need amflush and so we do not overwrite the full";
$f[]="			# backups performed at the beginning of the previous";
$f[]="			# cycle";
$f[]="";
$f[]="bumpsize 20 Mb		# minimum savings (threshold) to bump level 1 -> 2";
$f[]="bumppercent 20		# minimum savings (threshold) to bump level 1 -> 2";
$f[]="bumpdays 1		# minimum days at each level";
$f[]="bumpmult 4		# threshold = bumpsize * bumpmult^(level-1)";
$f[]="";
$f[]="etimeout 300		# number of seconds per filesystem for estimates.";
$f[]="#etimeout -600		# total number of seconds for estimates.";
$f[]="# a positive number will be multiplied by the number of filesystems on";
$f[]="# each host; a negative number will be taken as an absolute total time-out.";
$f[]="# The default is 5 minutes per filesystem.";
$f[]="";
$f[]="dtimeout 1800		# number of idle seconds before a dump is aborted.";
$f[]="";
$f[]="ctimeout 30		# maximum number of seconds that amcheck waits";
$f[]="			# for each client host";
$f[]=" ";
$f[]="runtapes 1		# number of tapes to be used in a single run of amdump";
$f[]="tpchanger \"chg-disk\"	# the tape-changer glue script";


$f[]="tapedev \"file:/dumps/amandatapes/DailySet1\"";
$f[]="#changerfile \"/etc/amanda/DailySet1/changer\"";
$f[]="#changerfile \"/etc/amanda/DailySet1/changer-status\"";
$f[]="changerfile \"/etc/amanda/DailySet1/changer.conf\"";
$f[]="changerdev \"@DEFAULT_CHANGER_DEVICE@\"";
$f[]="";
$f[]="# If you want Amanda to automatically label any non-Amanda tapes it";
$f[]="# encounters, uncomment the line below. Note that this will ERASE any";
$f[]="# non-Amanda tapes you may have, and may also ERASE any near-failing tapes.";
$f[]="# Use with caution.";
$f[]="## label_new_tapes \"DailySet1-%%%\"";
$f[]="";
$f[]="# maxdumpsize -1	# Maximum number of bytes the planner will schedule";
$f[]="			# for a run (default: runtapes * tape_length).";
$f[]="tapetype DISK		# what kind of tape it is (see tapetypes below)";
//$f[]="#labelstr \"^DailySet1-[0-9][0-9]*\$\"	";
//$f[]="diskdir \"/tmp\"";
//$f[]="disksize 5000 MB";
$f[]="";
$f[]="amrecover_changer \"@DEFAULT_TAPE_DEVICE@\"	# amrecover will use the changer if you restore";
$f[]="				# from this device.";
$f[]="				# It could be a string like 'changer' and";
$f[]="				# amrecover will use your changer if you";
$f[]="				# set your tape with 'settape changer'";
$f[]="";
$f[]="# Specify holding disks.  These are used as a temporary staging area for";
$f[]="# dumps before they are written to tape and are recommended for most sites.";
$f[]="# The advantages include: tape drive is more likely to operate in streaming";
$f[]="# mode (which reduces tape and drive wear, reduces total dump time); multiple";
$f[]="# dumps can be done in parallel (which can dramatically reduce total dump time.";
$f[]="# The main disadvantage is that dumps on the holding disk need to be flushed";
$f[]="# (with amflush) to tape after an operating system crash or a tape failure.";
$f[]="# If no holding disks are specified then all dumps will be written directly";
$f[]="# to tape.  If a dump is too big to fit on the holding disk than it will be";
$f[]="# written directly to tape.  If more than one holding disk is specified then";
$f[]="# they will all be used based on activity and available space.";
$f[]="";
$f[]="holdingdisk hd1 {";
$f[]="    comment \"main holding disk\"";
$f[]="    directory \"/dumps/amanda\"	# where the holding disk is";
$f[]="    use -100 Mb		# how much space can we use on it";
$f[]="			# a non-positive value means:";
$f[]="			#        use all space but that value";
$f[]="    chunksize 1Gb 	# size of chunk if you want big dump to be";
$f[]="			# dumped on multiple files on holding disks";
$f[]="			#  N Kb/Mb/Gb split images in chunks of size N";
$f[]="			#	      The maximum value should be";
$f[]="			#	      (MAX_FILE_SIZE - 1Mb)";
$f[]="			#  0          same as INT_MAX bytes";
$f[]="    }";
$f[]="#holdingdisk hd2 {";
$f[]="#    directory \"/dumps2/amanda\"";
$f[]="#    use 1000 Mb";
$f[]="#    }";
$f[]="#holdingdisk hd3 {";
$f[]="#    directory \"/mnt/disk4\"";
$f[]="#    use 1000 Mb";
$f[]="#    }";
$f[]="";
$f[]="";
$f[]="# If amanda cannot find a tape on which to store backups, it will run";
$f[]="# as many backups as it can to the holding disks.  In order to save";
$f[]="# space for unattended backups, by default, amanda will only perform";
$f[]="# incremental backups in this case, i.e., it will reserve 100% of the";
$f[]="# holding disk space for the so-called degraded mode backups.";
$f[]="# However, if you specify a different value for the `reserve'";
$f[]="# parameter, amanda will not degrade backups if they will fit in the";
$f[]="# non-reserved portion of the holding disk.";
$f[]="";
$f[]="# reserve 30 # percent";
$f[]="# This means save at least 30% of the holding disk space for degraded";
$f[]="# mode backups.  ";
$f[]="";
$f[]="autoflush no #";
$f[]="# if autoflush is set to yes, then amdump will schedule all dump on";
$f[]="# holding disks to be flush to tape during the run.";
$f[]="";
$f[]="# The format for a ColumnSpec is a ',' seperated list of triples.";
$f[]="# Each triple consists of";
$f[]="#   + the name of the column (as in ColumnNameStrings)";
$f[]="#   + prefix before the column";
$f[]="#   + the width of the column, if set to -1 it will be recalculated";
$f[]="#     to the maximum length of a line to print.";
$f[]="# Example:";
$f[]="# 	\"Disk=1:17,HostName=1:10,OutKB=1:7\"";
$f[]="# or";
$f[]="# 	\"Disk=1:-1,HostName=1:10,OutKB=1:7\"";
$f[]="#        ";
$f[]="# You need only specify those colums that should be changed from";
$f[]="# the default. If nothing is specified in the configfile, the";
$f[]="# above compiled in values will be in effect, resulting in an";
$f[]="# output as it was all the time.";
$f[]="# The names of the colums are:";
$f[]="# HostName, Disk, Level, OrigKB, OutKB, Compress, DumpTime, DumpRate,";
$f[]="# TapeTime and TapeRate.";
$f[]="#							ElB, 1999-02-24.";
$f[]="# columnspec \"Disk=1:18,HostName=0:10,OutKB=1:7\"";
$f[]="";
$f[]="";
$f[]="# Amanda needs a few Mb of diskspace for the log and debug files,";
$f[]="# as well as a database.  This stuff can grow large, so the conf directory";
$f[]="# isn't usually appropriate.  Some sites use /usr/local/var and some /usr/adm.";
$f[]="# Create an amanda directory under there.  You need a separate infofile and";
$f[]="# logdir for each configuration, so create subdirectories for each conf and";
$f[]="# put the files there.  Specify the locations below.";
$f[]="";
$f[]="# Note that, although the keyword below is infofile, it is only so for";
$f[]="# historic reasons, since now it is supposed to be a directory (unless";
$f[]="# you have selected some database format other than the `text' default)";
$f[]="infofile \"/etc/amanda/DailySet1/curinfo\"	# database DIRECTORY";
$f[]="logdir   \"/etc/amanda/DailySet1\"		# log directory";
$f[]="indexdir \"/etc/amanda/DailySet1/index\"		# index directory";
$f[]="#tapelist \"@CONFIG_DIR/DailySet1/tapelist\"	# list of used tapes";
$f[]="# tapelist is stored, by default, in the directory that contains amanda.conf";
$f[]="";
$f[]="# tapetypes";
$f[]="";
$f[]="# Define the type of tape you use here, and use it in \"tapetype\"";
$f[]="# above.  Some typical types of tapes are included here.  The tapetype";
$f[]="# tells amanda how many MB will fit on the tape, how big the filemarks";
$f[]="# are, and how fast the tape device is.";
$f[]="";
$f[]="# A filemark is the amount of wasted space every time a tape section";
$f[]="# ends.  If you run `make tapetype' in tape-src, you'll get a program";
$f[]="# that generates tapetype entries, but it is slow as hell, use it only";
$f[]="# if you really must and, if you do, make sure you post the data to";
$f[]="# the amanda mailing list, so that others can use what you found out";
$f[]="# by searching the archives.";
$f[]="";
$f[]="# For completeness Amanda should calculate the inter-record gaps too,";
$f[]="# but it doesn't.  For EXABYTE and DAT tapes this is ok.  Anyone using";
$f[]="# 9 tracks for amanda and need IRG calculations?  Drop me a note if";
$f[]="# so.";
$f[]="";
$f[]="# If you want amanda to print postscript paper tape labels";
$f[]="# add a line after the comment in the tapetype of the form";
$f[]="#    lbl-templ \"/path/to/postscript/template/label.ps\"";
$f[]="";
$f[]="# if you want the label to go to a printer other than the default";
$f[]="# for your system, you can also add a line above for a different";
$f[]="# printer. (i usually add that line after the dumpuser specification)";
$f[]="";
$f[]="# dumpuser \"operator\"     # the user to run dumps under";
$f[]="# printer \"mypostscript\"  # printer to print paper label on";
$f[]="";
$f[]="# here is an example of my definition for an EXB-8500";
$f[]="";
$f[]="# define tapetype EXB-8500 {";
$f[]="# ...";
$f[]="#     lbl-templ \"/etc/amanda/config/lbl.exabyte.ps\"";
$f[]="# }";
$f[]="";
$f[]="";
$f[]="define tapetype QIC-60 {";
$f[]="    comment \"Archive Viper\"";
$f[]="    length 60 mbytes";
$f[]="    filemark 100 kbytes		# don't know a better value";
$f[]="    speed 100 kbytes		# dito";
$f[]="}";

$f[]="define tapetype DISK {";
$f[]="   comment \"Backup to HD\"";
$f[]="   length {$config["tapecycleMB"]} mbytes";
$f[]="}";

$f[]="";
$f[]="define tapetype DEC-DLT2000 {";
$f[]="    comment \"DEC Differential Digital Linear Tape 2000\"";
$f[]="    length 15000 mbytes";
$f[]="    filemark 8 kbytes";
$f[]="    speed 1250 kbytes";
$f[]="}";
$f[]="";
$f[]="# goluboff@butch.Colorado.EDU";
$f[]="# in amanda-users (Thu Dec 26 01:55:38 MEZ 1996)";
$f[]="define tapetype DLT {";
$f[]="    comment \"DLT tape drives\"";
$f[]="    length 20000 mbytes		# 20 Gig tapes";
$f[]="    filemark 2000 kbytes	# I don't know what this means";
$f[]="    speed 1536 kbytes		# 1.5 Mb/s";
$f[]="}";
$f[]="";
$f[]="define tapetype SURESTORE-1200E {";
$f[]="    comment \"HP AutoLoader\"";
$f[]="    length 3900 mbytes";
$f[]="    filemark 100 kbytes";
$f[]="    speed 500 kbytes";
$f[]="}";
$f[]="";
$f[]="define tapetype EXB-8500 {";
$f[]="    comment \"Exabyte EXB-8500 drive on decent machine\"";
$f[]="    length 4200 mbytes";
$f[]="    filemark 48 kbytes";
$f[]="    speed 474 kbytes			";
$f[]="}";
$f[]="";
$f[]="define tapetype EXB-8200 {";
$f[]="    comment \"Exabyte EXB-8200 drive on decent machine\"";
$f[]="    length 2200 mbytes";
$f[]="    filemark 2130 kbytes";
$f[]="    speed 240 kbytes			";
$f[]="}";
$f[]="";
$f[]="define tapetype HP-DAT {";
$f[]="    comment \"DAT tape drives\"";
$f[]="    # data provided by Rob Browning <rlb@cs.utexas.edu>";
$f[]="    length 1930 mbytes";
$f[]="    filemark 111 kbytes";
$f[]="    speed 468 kbytes";
$f[]="}";
$f[]="";
$f[]="define tapetype DAT {";
$f[]="    comment \"DAT tape drives\"";
$f[]="    length 1000 mbytes		# these numbers are not accurate";
$f[]="    filemark 100 kbytes		# but you get the idea";
$f[]="    speed 100 kbytes";
$f[]="}";
$f[]="";
$f[]="define tapetype MIMSY-MEGATAPE {";
$f[]="    comment \"Megatape (Exabyte based) drive through Emulex on Vax 8600\"";
$f[]="    length 2200 mbytes";
$f[]="    filemark 2130 kbytes";
$f[]="    speed 170 kbytes		# limited by the Emulex bus interface, ugh";
$f[]="}";
$f[]="";
$f[]="";
$f[]="# dumptypes"; 
$f[]="#   kencrypt	- encrypt the data stream between the client and server.";
$f[]="#		  Default: [kencrypt no]";
$f[]="#   program	- specify the dump system to use.  Valid values are \"DUMP\" and";
$f[]="#		  \"GNUTAR\".  Default: [program \"DUMP\"].";
$f[]="#   record	- record the backup in the time-stamp-database of the backup";
$f[]="#		  program (e.g. /var/lib/dumpdates for DUMP or";
$f[]="#		  @GNUTAR_LISTED_INCREMENTAL_DIRX@ for GNUTAR.).";
$f[]="#		  Default: [record yes]";
$f[]="#   starttime	- delay the start of the dump?  Default: no delay";
$f[]="# split_diskbuffer - (optional) When dumping a split dump  in  PORT-WRITE";
$f[]="#                 mode (usually meaning \"no holding disk\"), buffer the split";
$f[]="#		  chunks to a file in the directory specified by this option.";
$f[]="#		  Default: [none]";
$f[]="# fallback_splitsize - (optional) When dumping a split dump  in  PORT-WRITE";
$f[]="#                 mode, if no split_diskbuffer is specified (or if we somehow";
$f[]="#                 fail to use our split_diskbuffer), we must buffer split";
$f[]="#                 chunks in memory.  This specifies the maximum size split";
$f[]="#                 chunks can be in this scenario, and thus the maximum amount";
$f[]="#                 of memory consumed for in-memory splitting.  Default: [10m]";
$f[]="#";
$f[]="# Note that you may specify previously defined dumptypes as a shorthand way of";
$f[]="# defining parameters.";
$f[]="";
$f[]="define dumptype global {";
$f[]="    comment \"Global definitions\"";
$f[]="    # This is quite useful for setting global parameters, so you don't have";
$f[]="    # to type them everywhere.  All dumptype definitions in this sample file";
$f[]="    # do include these definitions, either directly or indirectly.";
$f[]="    # There's nothing special about the name `global'; if you create any";
$f[]="    # dumptype that does not contain the word `global' or the name of any";
$f[]="    # other dumptype that contains it, these definitions won't apply.";
$f[]="    # Note that these definitions may be overridden in other";
$f[]="    # dumptypes, if the redefinitions appear *after* the `global'";
$f[]="    # dumptype name.";
$f[]="    # You may want to use this for globally enabling or disabling";
$f[]="    # indexing, recording, etc.  Some examples:";
$f[]="    # index yes";
$f[]="    # record no";
$f[]="    # split_diskbuffer \"/raid/amanda\"";
$f[]="    # fallback_splitsize 64m";
$f[]="}";
$f[]="";
$f[]="define dumptype always-full {";
$f[]="    global";
$f[]="    comment \"Full dump of this filesystem always\"";
$f[]="    compress none";
$f[]="    priority high";
$f[]="    dumpcycle 0";
$f[]="}";
$f[]="";
$f[]="define dumptype root-tar {";
$f[]="    global";
$f[]="    program \"GNUTAR\"";
$f[]="    comment \"root partitions dumped with tar\"";
$f[]="    compress none";
$f[]="    index";
$f[]="#   exclude list \"/etc/amanda/exclude.gtar\"";
$f[]="    priority low";
$f[]="}";
$f[]="";
$f[]="define dumptype user-tar {";
$f[]="    root-tar";
$f[]="    comment \"user partitions dumped with tar\"";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype user-tar-span {";
$f[]="    root-tar";
$f[]="    comment \"tape-spanning user partitions dumped with tar\"";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype high-tar {";
$f[]="    root-tar";
$f[]="    comment \"partitions dumped with tar\"";
$f[]="    priority high";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-root-tar {";
$f[]="    root-tar";
$f[]="    comment \"Root partitions with compression\"";
$f[]="    compress client fast";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-user-tar {";
$f[]="    user-tar";
$f[]="    compress client fast";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-user-tar-span {";
$f[]="    user-tar-span";
$f[]="    compress client fast";
$f[]="}";
$f[]="";
$f[]="define dumptype holding-disk {";
$f[]="    global";
$f[]="    comment \"The master-host holding disk itself\"";
$f[]="    holdingdisk no # do not use the holding disk";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-user {";
$f[]="    global";
$f[]="    comment \"Non-root partitions on reasonably fast machines\"";
$f[]="    compress client fast";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-user-span {";
$f[]="    global";
$f[]="    comment \"Tape-spanning non-root partitions on reasonably fast machines\"";
$f[]="    compress client fast";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype nocomp-user {";
$f[]="    comp-user";
$f[]="    comment \"Non-root partitions on slow machines\"";
$f[]="    compress none";
$f[]="}";
$f[]="";
$f[]="define dumptype nocomp-user-span {";
$f[]="    comp-user-span";
$f[]="    comment \"Tape-spanning non-root partitions on slow machines\"";
$f[]="    compress none";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-root {";
$f[]="    global";
$f[]="    comment \"Root partitions with compression\"";
$f[]="    compress client fast";
$f[]="    priority low";
$f[]="}";
$f[]="";
$f[]="define dumptype nocomp-root {";
$f[]="    comp-root";
$f[]="    comment \"Root partitions without compression\"";
$f[]="    compress none";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-high {";
$f[]="    global";
$f[]="    comment \"very important partitions on fast machines\"";
$f[]="    compress client best";
$f[]="    priority high";
$f[]="}";
$f[]="";
$f[]="define dumptype nocomp-high {";
$f[]="    comp-high";
$f[]="    comment \"very important partitions on slow machines\"";
$f[]="    compress none";
$f[]="}";
$f[]="";
$f[]="define dumptype nocomp-test {";
$f[]="    global";
$f[]="    comment \"test dump without compression, no /var/lib/dumpdates recording\"";
$f[]="    compress none";
$f[]="    record no";
$f[]="    priority medium";
$f[]="}";
$f[]="";
$f[]="define dumptype comp-test {";
$f[]="    nocomp-test";
$f[]="    comment \"test dump with compression, no /var/lib/dumpdates recording\"";
$f[]="    compress client fast";
$f[]="}";
$f[]="";
$f[]="define dumptype custom-compress {";
$f[]="   global";
$f[]="   program \"GNUTAR\"";
$f[]="   comment \"test dump with custom client compression\"";
$f[]="   compress client custom";
$f[]="   client_custom_compress \"/usr/bin/bzip2\"";
$f[]="}";
$f[]="";
$f[]="define dumptype encrypt-fast {";
$f[]="   global";
$f[]="   program \"GNUTAR\"";
$f[]="   comment \"test dump with fast client compression and server symmetric encryption\"";
$f[]="   compress client fast";
$f[]="   encrypt server";
$f[]="   server_encrypt \"/usr/local/sbin/amcrypt\"";
$f[]="   server_decrypt_option \"-d\"";
$f[]="}";
$f[]="";

		$q=new mysql();
		$sql="SELECT dumpname,comment FROM amanda_dumptype ORDER BY dumpname";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
				$f[]="define dumptype {$ligne["dumpname"]} {";
				$f[]="    auth {$ligne["auth"]}";			
				$f[]="    comment \"{$ligne["comment"]}\"";
				$f[]="    program \"{$ligne["program"]}\"";
				$f[]="    compress {$ligne["compress"]}";
				$f[]="    comprate {$ligne["comprate"]}";
				$f[]="    priority {$ligne["priority"]}";
				$f[]="    strategy {$ligne["strategy"]}";
				$f[]="    dumpcycle {$ligne["dumpcycle"]}";
				$f[]="    maxdumps {$ligne["maxdumps"]}";
				$f[]="    maxpromoteday {$ligne["maxpromoteday"]}";
				$f[]="    estimate {$ligne["estimate"]}";
				if($ligne["skip-incr"]==1){$f[]="    skip-incr";}
				if($ligne["skip-full"]==1){$f[]="    skip-full";}
				if($ligne["index"]==1){$f[]="    index yes";}else{$f[]="    index no";}
				if($ligne["holdingdisk"]==1){$f[]="    holdingdisk yes";}else{$f[]="    holdingdisk no";}
				$f[]="}";
				$f[]="";
		}




$f[]="";
$f[]="# network interfaces";
$f[]="#";
$f[]="# These are referred to by the disklist file.  They define the attributes";
$f[]="# of the network interface that the remote machine is accessed through.";
$f[]="# Notes: - netusage above defines the attributes that are used when the";
$f[]="#          disklist entry doesn't specify otherwise.";
$f[]="#        - the values below are only samples.";
$f[]="#        - specifying an interface does not force the traffic to pass";
$f[]="#          through that interface.  Your OS routing tables do that.  This";
$f[]="#          is just a mechanism to stop Amanda trashing your network.";
$f[]="# Attributes are:";
$f[]="#	use		- bandwidth above which amanda won't start";
$f[]="#			  backups using this interface.  Note that if";
$f[]="#			  a single backup will take more than that,";
$f[]="#			  amanda won't try to make it run slower!";
$f[]="";
$f[]="define interface local {";
$f[]="    comment \"a local disk\"";
$f[]="    use 1000 kbps";
$f[]="}";
$f[]="";
$f[]="define interface le0 {";
$f[]="    comment \"10 Mbps ethernet\"";
$f[]="    use 400 kbps";
$f[]="}";
$f[]="";
$f[]="# You may include other amanda configuration files, so you can share";
$f[]="# dumptypes, tapetypes and interface definitions among several";
$f[]="# configurations.";
$f[]="";
$f[]="#includefile \"/etc/amanda/amanda.conf.main\"";
$f[]="";	

if(!is_dir("/etc/amanda/DailySet1")){@mkdir("/etc/amanda/DailySet1",644,true);}
if(!is_dir("/dumps/amanda")){@mkdir("/dumps/amanda",640,true);}
if(!is_dir("/dumps/amandatapes/DailySet1")){@mkdir("/dumps/amandatapes/DailySet1",640,true);}
if(!is_file("/etc/amanda/DailySet1/disklist")){@file_put_contents("/etc/amanda/DailySet1/disklist", " ");}
if(!is_dir("/var/lib/amanda/gnutar-lists")){@mkdir("/var/lib/amanda/gnutar-lists",true,600);}
for($i=1;$i<$config["tapecycle"]+1;$i++){
	if(!is_dir("/dumps/amandatapes/DailySet1/slot$i")){
		@mkdir("/dumps/amandatapes/DailySet1/slot$i",640,true);
	}
}

if(!is_file("/dumps/amandatapes/DailySet1/info")){@file_put_contents("/dumps/amandatapes/DailySet1/info", " ");}
if(!is_file("/etc/amanda/DailySet1/tapelist")){@file_put_contents("/etc/amanda/DailySet1/tapelist", " ");}

if(!is_file("/dumps/amandatapes/DailySet1/data")){shell_exec("/bin/ln -s /dumps/amandatapes/DailySet1/slot1 /dumps/amandatapes/DailySet1/data >/dev/null 2>&1");}

$amlabel=$unix->find_program("amlabel");
for($i=1;$i<$config["tapecycle"]+1;$i++){
	shell_exec("$amlabel DailySet1 DailySet1-$i slot $i >/dev/null 2>&1");
}
set_backup_server();

@file_put_contents("/etc/amanda/DailySet1/amanda.conf", @implode("\n", $f));
echo "Starting......: Amanda server updating DailySet1 - amanda.conf success\n";
}


function computers(){
	$q=new mysql();
	$sql="SELECT * FROM amanda_hosts ORDER BY hostname";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	$f[]="{$ligne["hostname"]}\t{$ligne["directory"]}\t{$ligne["dumptype"]}";
	}
	@file_put_contents("/etc/amanda/DailySet1/disklist", @implode("\n", $f));
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.amanda.php --resolv >/dev/null 2>&1 &");
	shell_exec($cmd);
	
}

function resolv_computers(){
	$q=new mysql();
	$sql="SELECT ID,hostname,resolved FROM amanda_hosts ORDER BY hostname";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if(!isset($resolved[$ligne["hostname"]])){$ipaddr=gethostbyname($ligne["hostname"]);}else{$resolved[$ligne["hostname"]];}
	$resolved[$ligne["hostname"]]=$ipaddr;
	if($GLOBALS["VERBOSE"]){echo "{$ligne["hostname"]} = $ipaddr\n";}
	if($ipaddr==$ligne["hostname"]){$ipaddr=null;}
	$sql="UPDATE amanda_hosts SET `resolved`='$ipaddr' WHERE ID='{$ligne["ID"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	}
}

function set_backup_server(){
	$sock=new sockets();
	$f[]="localhost\troot";
	$AmandaBackupServer=trim($sock->GET_INFO("AmandaBackupServer"));
	if($AmandaBackupServer<>null){
		$f[]="$AmandaBackupServer\troot";
	}
	if(!is_dir("/var/lib/amanda")){@mkdir("/var/lib/amanda",644,true);}
	@file_put_contents("/var/lib/amanda/.amandahosts",@implode("\n", $f));
	@file_put_contents("/root/.amandahosts",@implode("\n", $f));
	shell_exec("/bin/chmod 600 /var/lib/amanda/.amandahosts");
	shell_exec("/bin/chmod 600 /root/.amandahosts");
	if(!is_dir("/var/lib/amanda/gnutar-lists")){@mkdir("/var/lib/amanda/gnutar-lists",true,600);}
	
	
}