#!/usr/bin/perl -w
#
# Really useful when script is run from the relative path
BEGIN { unshift @INC, $1 if $0 =~ /(.*)[\/]/; }

use Time::Local;
use File::Copy;
use Shell;
use strict;

require 'stat-lib.pl';

sub usage
{
    print "parse_avstat.pl {-n=day_num|-ds=dd.mm.yyyy [-de=dd.mm.yyyy]} {-sd=stat_dir} {-h} {parse_log_name}\n";
    print "   parse_log_name - analyse parse_log_name file\n\n";
    print "   -n=day_num     - get statistics for day_num last days\n";
    print "   -ds=dd.mm.yyyy - get statistics from -ds date to -de(or to now).\n";
    print "   -sd=stat_dir   - save statistics to stat_dir\n";
    print "   -r             - reanalyse log.\n";
    print "   -h             - this message\n";
    print "   -x             - don't move file\n";
    print "   -d             - delete log file after processing\n";
    exit;
}


if( $#ARGV == -1 ){
    usage;
}

my $arg;
my $stat_dir="./";
my $daysec = 86400;
my $list_breaker=',';
my $cur_time = time;
my @tm = localtime($cur_time);
my $start_time;
my $start_timestamp=0;
my $end_time = $tm[3].".".($tm[4]+1).".".($tm[5]+1900);
my $end_timestamp=$cur_time;
my $log_filename="";
my $append_data=1;
my $move_file=1;
my $delete_stat=0;

foreach $arg (@ARGV){
	if( $arg=~/^-n=(\d+)/){
		my $ndays = $1;
		@tm = localtime($cur_time-$ndays*$daysec);
		$start_time = $tm[3].".".($tm[4]+1).".".($tm[5]+1900);
		$start_timestamp = timelocal(0,0,0,$tm[3],$tm[4],$tm[5]);
	}elsif( $arg=~/^-ds=(\d+).(\d+).(\d+)/){
		$start_time = int($1).".".int($2).".".int($3);
		$start_timestamp = timelocal(0,0,0,int($1),int($2)-1,int($3)-1900);
	}elsif( $arg=~/^-de=(\d+).(\d+).(\d+)/){
		$end_time = int($1).".".int($2).".".int($3);
		$end_timestamp = timelocal(0,0,0,int($1),int($2)-1,int($3)-1900);
		$move_file=0;
	}elsif( $arg=~/^-sd=(.*)/){
		$stat_dir=$1;
	}elsif( $arg=~/^-r/){
		$append_data=0;
	}elsif( $arg=~/^-d/){
		$delete_stat=1;
	}elsif( $arg=~/^-x/){
		$move_file=0;
	}elsif( $arg=~/^-h/){
		usage;
	}elsif( $arg=~/^-/){
		print "Unknown option '$arg' or missed required argument.\n\n";
		usage;
	}else{
		$log_filename=trim_string($arg);
		
	}
}
if($log_filename eq ""){
	print "Logfile name is empty\n";
	exit;
}
#print "Start time: $start_time\n";
#print "End time: $end_time\n";
#print "End timestamp: ".localtime($end_timestamp)."\n";
#exit;
if(!($stat_dir=~/[\\\/]$/)){
	$stat_dir=$stat_dir."/";
}

if (!-d $stat_dir) {
	print "Directory '$stat_dir' does not exist\n";
	exit;
}

if (!-f $log_filename) {
	print "Logfile '$log_filename' does not exist\n";
	exit;
}

my $rcpt_stat=$stat_dir."rcpt.stat";
my $sndr_stat=$stat_dir."sndr.stat";
my $virs_stat=$stat_dir."virs.stat";
my $ip_stat=$stat_dir."ip.stat";
my $total_stat=$stat_dir."total.stat";
my $time_stat=$stat_dir."time.stat";
my $line;
my $msg_id;
my %stats=();
load_stat(\%stats, $rcpt_stat, "rcpt");
load_stat(\%stats, $sndr_stat, "sndr");
load_stat(\%stats, $virs_stat, "virs");
load_stat(\%stats, $ip_stat, "ip");
load_stat(\%stats, $total_stat, "result");
load_time_stat(\%stats, $time_stat);
if(!$append_data){
	set_delete_flag(\%stats, $start_timestamp, $end_timestamp);
}
#delete_stat(\%stats, $start_timestamp, $end_timestamp);

my $new_log_filename;
if($move_file){
#    $new_log_filename = $stat_dir.$log_filename."_".$end_timestamp;
    $new_log_filename = $log_filename."_".$end_timestamp;
    touch($log_filename);
    move($log_filename, $new_log_filename) or die "move '$log_filename' to '$new_log_filename' failed: $!";
#    touch($log_filename);
    $log_filename = $new_log_filename;
}

open(LOG, "<$log_filename")         or die "can't open $log_filename: $!";
while(<LOG>){
	$line = $_;
	if( !parse_stat_line($line, $start_timestamp, $end_timestamp, \%stats) )
	{
	    print("Bad line: $line\n");
	}
	
}
close LOG;

save_stat(\%stats, $rcpt_stat, "rcpt");
save_stat(\%stats, $sndr_stat, "sndr");
save_stat(\%stats, $virs_stat, "virs");
save_stat(\%stats, $ip_stat, "ip");
save_stat(\%stats, $total_stat, "result");
save_time_stat(\%stats, $time_stat);


