#!/usr/bin/perl
# meminfo.pl - show real memory use by processes, or as close to it as possible
# Copyright (C) 2009 Peter Willis <peterwwillis@yahoo.com>
# 
# Stop guessing about how much memory is being used by processes on your system.
# Use this tool to find out for real how much real memory is being used.
# Requires a kernel with 'smaps' support (cat /proc/$$/smaps to check yours)
# 
# Usage is fairly simple: pass it a pid on the command line and it will gather
# memory stats from them and report them.
# 
# If you're trying to get the total memory of a multi-process application, just
# pass all the PIDs and look at the Private RSS. That will include all pages
# used by your application. It *won't* include shared memory from libraries which
# might be used by yet-another-program, but it'll give you a good minimum size
# of your app. To find all pages used by other programs, use --find-all-memory.
# 
# If your kernel supports Pss stats, that is the best example of how much real
# memory a process is using (it's the private rss combined with 'its share' of
# shared memory, so add all the Pss's of your application's processes up and you
# will know how much memory they are using, or at least a little more accurately)
# 
# You may need to be root to make this script really useful.
 

use strict;
$|=1;

if ( ! @ARGV ) {
    die "Usage: $0 PID|TCOMM [..]\nPass a pid or a process executable name.\nTotals and reports on per-process memory usage.\n";
}

# Global Vars
my @smaps_names = qw(Size Rss Pss Shared_Clean Shared_Dirty Private_Clean Private_Dirty Referenced Swap);
my $MEG = 1024; # in kilobytes
my $GIG = $MEG*1024; # in kilobytes
my %procs;
my %procstat;
my $counted_pids = 0;

# Main

# Collect process information
foreach my $arg (@ARGV) {
    my @pids = ( $arg );
    if ( $arg !~ /^\d+$/ ) {
        @pids = grep_pids($arg, \%procstat);
    }

    foreach my $pid (@pids) {
        if ( !exists $procstat{$pid} ) {
            $procstat{$pid} = getstat($pid);
        }
        if ( keys %{ $procstat{$pid} } ) {
            my @a = calc_smaps_pid($pid);
            if ( @a ) {
                $procs{$pid} = \@a;
                $counted_pids++;
            }
        }
    }
}

# Summarize

summarize_pids(\%procs, \%procstat, \@ARGV);
my @totals = summarize_totals(\%procs);

#format_print("\nSummary:\n\tTotal Size =>\t\t\t%f\n\tTotal Rss =>\t\t\t%f\n\tTotal Pss =>\t\t\t%f\n\tTotal Shared_Clean =>\t\t%f\n\tTotal Shared_Dirty =>\t\t%f\n\tTotal Private_Clean =>\t\t%f\n\tTotal Private_Dirty =>\t\t%f\n\tTotal Referenced =>\t\t%f\n\tTotal Swap =>\t\t\t%f\n", @totals);

# Make some guesstimations

# Shared Clean + Private Clean + Private Dirty (Shared Clean may still be an innacurate addition of multiple shared pages!) 1 - 3 4
#my $guessed_mem = ($totals[3] + $totals[5] + $totals[6]);
my $guessed_mem = ( $totals[1] - ($totals[3] + $totals[4]) );
#print "\nAverages:\n";
#format_print("\tTotal Rss - Shared Rss:\t\t%f\n\tNumber of PIDs:\t\t\t$counted_pids\n\tAverage memory per PID:\t\t%f\n", $guessed_mem, ($guessed_mem/$counted_pids));
format_print(" <%f>", $guessed_mem);

exit 0;


# Subroutines


# By default searches *every pid on the system*
sub grep_pids {
    my $arg = shift;
    my $pstat_ref = shift;
    my @foundpids;

    opendir(DIR, "/proc/") || die "Error: let me read /proc/ !!!\n";
    my @dirs = grep(/^\d+$/, readdir(DIR) );
    closedir(DIR);

    # Each of these dirs should be a pid, according to grep above
    foreach my $dir (@dirs) {
        if ( !exists $pstat_ref->{$dir} ) {
            $pstat_ref->{$dir} = getstat($dir);
        }
        if ( keys %$pstat_ref and $pstat_ref->{$dir}->{'tcomm'} eq $arg ) {
            push(@foundpids, $dir);
        }
    }
    return @foundpids;
}


sub getstat {
    my $pid = shift;

    if ( ! open(FILE, "/proc/$pid/stat") ) {
        print STDERR "Could not read stat for $pid: $!\n" if (exists $ENV{VERBOSE});
        return( {} );
    }
    my @stat = split(/\s+/, <FILE>);
    close(FILE);

    my $hashref = { 'tcomm' => $stat[1], 'state' => $stat[2], 'ppid' => $stat[3], 'pgrp' => $stat[4], 'sid' => $stat[5] };
    $hashref->{'tcomm'} =~ tr/()//d;
    return $hashref;
}


sub summarize_pids {
    my $procs_ref = shift;
    my $pstat_ref = shift;
    my $pids = shift;

    foreach my $pid (keys %$procs_ref) {
        print "<$pstat_ref->{$pid}->{tcomm}>";
        for ( my $i=0 ; $i < @{$procs_ref->{$pid}} ; $i++ ) {
            #format_print("\t$smaps_names[$i] => %f\n", $procs_ref->{$pid}->[$i]);
        }
    }
}

# Summarize all proc totals
sub summarize_totals {
    my $procs = shift;
    my ($size, $rss, $pss, $shared_clean, $shared_dirty, $private_clean, $private_dirty, $referenced, $swap) = ( 0, 0, 0, 0, 0, 0, 0, 0, 0 );
    foreach my $proc ( keys %$procs ) {
        $size += $procs->{$proc}->[0];
        $rss += $procs->{$proc}->[1];
        $pss += $procs->{$proc}->[2];
        $shared_clean += $procs->{$proc}->[3];
        $shared_dirty += $procs->{$proc}->[4];
        $private_clean += $procs->{$proc}->[5];
        $private_dirty += $procs->{$proc}->[6];
        $referenced += $procs->{$proc}->[7];
        $swap += $procs->{$proc}->[8];
    }
    return ($size, $rss, $pss, $shared_clean, $shared_dirty, $private_clean, $private_dirty, $referenced, $swap);
}


# Yes, I know about Linux::Smaps.
sub calc_smaps_pid {
    my $pid = shift;
    my ($size, $rss, $pss, $shared_clean, $shared_dirty, $private_clean, $private_dirty, $referenced, $swap) = ( 0, 0, 0, 0, 0, 0, 0, 0, 0 );

    if ( ! open(FILE,"</proc/$pid/smaps") ) {
        print STDERR "Could not read smaps for $pid: $!\n" if (exists $ENV{VERBOSE});
        return ();
    }
    my @smaps = map { chomp $_; $_ } <FILE>;
    close(FILE);

    # FYI: by default these are all kilobytes, assume it'll always be that way...
    for ( my $i=0; $i<@smaps; $i++ ) {
        if ( $smaps[$i] =~ /^Size:\s+(\d+)/ ) {
            $size += $1;
        } elsif ( $smaps[$i] =~ /^Rss:\s+(\d+)/ ) {
            $rss += $1;
        } elsif ( $smaps[$i] =~ /^Pss:\s+(\d+)/ ) {
            $pss += $1;
        } elsif ( $smaps[$i] =~ /^Shared_Clean:\s+(\d+)/ ) {
            $shared_clean += $1;
        } elsif ( $smaps[$i] =~ /^Shared_Dirty:\s+(\d+)/ ) {
            $shared_dirty += $1;
        } elsif ( $smaps[$i] =~ /^Private_Clean:\s+(\d+)/ ) {
            $private_clean += $1;
        } elsif ( $smaps[$i] =~ /^Private_Dirty:\s+(\d+)/ ) {
            $private_dirty += $1;
        } elsif ( $smaps[$i] =~ /^Referenced:\s+(\d+)/ ) {
            $referenced += $1;
        } elsif ( $smaps[$i] =~ /^Swap:\s+(\d+)/ ) {
            $swap += $1;
        }
    }

    return ($size, $rss, $pss, $shared_clean, $shared_dirty, $private_clean, $private_dirty, $referenced, $swap);
}

# Hack to pretty-format any numbers automatically
sub format_print {
    my $format = shift;
    my @args = @_;
    my $c = 0;

    # I suck at formatting
    $format =~ s<\%f>| $args[$c] > $GIG ? sprintf("%.4G GB",$args[$c++]/1024/1024) : $args[$c] > $MEG ? sprintf("%.4G MB",$args[$c++]/1024) : sprintf("%.4G KB",$args[$c++]) |eg;

    print $format;
}

