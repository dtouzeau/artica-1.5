#! /usr/bin/perl
use strict;
use warnings;

open(STAT, "</proc/stat");
my ($junk, $cpu_user, $cpu_nice, $cpu_sys, $cpu_idle) = split(/\s+/,<STAT>); 
close(STAT);
my $cpu_total1 = $cpu_user + $cpu_nice + $cpu_sys + $cpu_idle; 
my $cpu_load1 = $cpu_user + $cpu_nice + $cpu_sys; 
sleep 1; 
open(STAT,"</proc/stat");
($junk, $cpu_user, $cpu_nice, $cpu_sys, $cpu_idle) = split(/\s+/,<STAT>); close(STAT);
my $cpu_total2 = $cpu_user + $cpu_nice + $cpu_sys + $cpu_idle; 
my $cpu_load2 = $cpu_user + $cpu_nice + $cpu_sys; 
my $a = $cpu_load2 - $cpu_load1;
my $b = $cpu_total2 - $cpu_total1;
printf("%4.1f\n", 100.0*$a/$b);


