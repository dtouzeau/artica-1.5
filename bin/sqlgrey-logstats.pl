#!/usr/bin/perl

# sqlgrey: a postfix greylisting policy server using an SQL backend
# based on postgrey
# Copyright 2004 (c) ETH Zurich
# Copyright 2004 (c) Lionel Bouton

#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#

package sqlgrey_logstats;
use strict;
use Pod::Usage;
use Getopt::Long qw(:config posix_default no_ignore_case);
use Time::Local;
use Date::Calc;

my $VERSION = "1.7.6";

# supports IPv4 and IPv6
my $ipregexp = '[\dabcdef\.:]+';

######################
# Time-related methods
my %months = ( "Jan" => 0, "Feb" => 1, "Mar" => 2, "Apr" => 3, "May" => 4, "Jun" => 5,
	       "Jul" => 6, "Aug" => 7, "Sep" => 8, "Oct" => 9, "Nov" => 10, "Dec" => 11 );

sub validate_tstamp {
    my $self = shift;
    my $value = shift;
    my ($monthname, $mday, $hour, $min, $sec);
    if ($value =~ /^(\w{3}) ([\d ]\d) (\d\d):(\d\d):(\d\d)$/) {
        ($monthname, $mday, $hour, $min, $sec) = ($1, $2, $3, $4, $5);
    } else {
	$self->debug("invalid date format: $value\n");
        return undef;
    }
    my $month = $months{$monthname};
    my $year = $self->{year};
    if ($month > $self->{month}) {
	# yes we can compute stats across years...
	$year--;
    }
    my $epoch_seconds = Time::Local::timelocal($sec, $min, $hour, $mday, $month, $year);
    if (! $epoch_seconds) {
	$self->debug("can't compute timestamp from: $value\n");
        return undef;
    }
    if ($epoch_seconds < $self->{begin} or $epoch_seconds > $self->{end}) {
	$self->debug("date out of range: $value\n");
        return undef;
    }
    return $epoch_seconds;
}

# What was the tstamp yesterday at 00:00 ?
sub yesterday_tstamp {
    # Get today 00:00:00 and deduce one day
    my ($day, $month, $year) = reverse Date::Calc::Add_Delta_Days(Date::Calc::Today(), -1 );
    # Adjust Date::Calc 1-12 month to 0-11
    $month--;
    return Time::Local::timelocal(0,0,0,$day,$month,$year);
}

# What was the tstamp today at 00:00 ?
sub today_tstamp {
    # Get today 00:00:00
    return Time::Local::timelocal(0, 0, 0, ((localtime())[3,4,5]));
}

# set time period
sub yesterday {
    my $self = shift;
    $self->{begin} = $self->yesterday_tstamp();
    $self->{end} = $self->{begin} + (60 * 60 * 24);
}

sub today {
    my $self = shift;
    $self->{begin} = $self->today_tstamp();
    $self->{end} = time();
}

sub lasthour {
    my $self = shift;
    my $now = time();
    $self->{begin} = $now - (60 * 60);
    $self->{end} = $now;
}

sub last24h {
    my $self = shift;
    my $now = time();
    $self->{begin} = $now - (60 * 60 * 24);
    $self->{end} = $now;
}

sub lastweek {
    my $self = shift;
    $self->{end} = $self->today_tstamp();
    $self->{begin} = $self->{end} - (60 * 60 * 24 * 7);
}

##################
# Argument parsing
sub parse_args {
    my $self = shift;
    my %opt = ();

    GetOptions(\%opt, 'help|h', 'man', 'version', 'yesterday|y', 'today|t',
	       'lasthour', 'last24h|d', 'lastweek|w', 'programname', 'debug',
	       'top-domain=i', 'top-from=i', 'top-spam=i', 'top-throttled=i',
	       'print-delayed')
	or pod2usage(1);

    if ($opt{debug}) {
	$self->{debug} = 1;
    }

    if ($opt{help})    { pod2usage(1) }
    if ($opt{man})     { pod2usage(-exitstatus => 0, -verbose => 2) }
    if ($opt{version}) { print "sqlgrey-logstats.pl $VERSION\n"; exit(0) }

    my $setperiod_count = 0;
    if ($opt{yesterday}) {
	$self->yesterday();
	$setperiod_count++;
    }
    if ($opt{today}) {
	$self->today();
	$setperiod_count++;
    }
    if ($opt{lasthour}) {
	$self->lasthour();
	$setperiod_count++;
    }
    if ($opt{last24h}) {
	$self->last24h();
	$setperiod_count++;
    }
    if ($opt{lastweek}) {
	$self->lastweek();
	$setperiod_count++;
    }
    if ($setperiod_count > 1) {
	pod2usage(1);
    }

    if ($opt{'top-domain'}) {
	$self->{top_domain} = $opt{'top-domain'};
    }
    if ($opt{'top-from'}) {
	$self->{top_from} = $opt{'top-from'};
    }
    if ($opt{'top-spam'}) {
	$self->{top_spam} = $opt{'top-spam'};
    }

    if ($opt{'top-throttled'}) {
	$self->{top_throttled} = $opt{'top-throttled'};
    }

    if ($opt{'print-delayed'}) {
	$self->{print_delayed} = 1;
    }

    # compute current year and month
    ($self->{month}, $self->{year}) = (localtime)[4,5];

    if ($opt{programname}) {
	$self->{programname} = $opt{programname};
    }
}

################
# percent string
sub percent {
    my $portion = shift;
    my $total = shift;
    if ($total == 0) {
	return "N/A%";
    }
    return sprintf ("%.2f%%", ($portion / $total) * 100);
}

# quick debug function
sub debug {
    my $self = shift;
    if (defined $self->{debug}) {
	print shift;
    }
}

sub split_date_event {
    my ($self, $line) = @_;

    if ($line =~
	m/^(\w{3} [\d ]\d \d\d:\d\d:\d\d)\s\S+\s$self->{programname}: (\w+): (.*)$/o
	) {
	my $time = $self->validate_tstamp($1);
	if (! defined $time) {
	    return (undef,undef,undef);
	} else {
	    #$self->debug("match: $time, $2, $3\n");
	    return ($time, $2, $3);
	}
    } else {
	$self->debug("not matched: $line\n");
	return (undef,undef,undef);
    }
}

sub parse_grey {
    my ($self, $time, $event) = @_;
    ## old format
    if ($event =~ /^domain awl match: updating ($ipregexp), (.*)$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{domain_awl_match}{$1}{$2}++;
	$self->{domain_awl_match_count}++;
    } elsif ($event =~ /^from awl match: updating ($ipregexp), (.*)$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{from_awl_match}{$1}{$2}++;
	$self->{from_awl_match_count}++;
    } elsif ($event =~ /^new: ($ipregexp), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{new}{$1}++;
	$self->{new_count}++;
    } elsif ($event =~ /^throttling: ($ipregexp), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{throttled}{$1}{$2}++;
	$self->{throttled_count}++;
    } elsif ($event =~ /^early reconnect: ($ipregexp), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{early}{$1}++;
	$self->{early_count}++;
    } elsif ($event =~ /^reconnect ok: ($ipregexp), (.*) -> (.*) \((.*)\)/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{reconnect}{$1}{$2}++;
	$self->{reconnect_count}++;
    ## new format
    } elsif ($event =~ /^domain awl match: updating ($ipregexp)\($ipregexp\), (.*)$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{domain_awl_match}{$1}{$2}++;
	$self->{domain_awl_match_count}++;
    ## new format for from_awl match (deverp log)
    } elsif ($event =~ /^from awl match: updating ($ipregexp)\($ipregexp\), (.*)\(.*\)$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{from_awl_match}{$1}{$2}++;
	$self->{from_awl_match_count}++;
    } elsif ($event =~ /^from awl match: updating ($ipregexp)\($ipregexp\), (.*)$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{from_awl_match}{$1}{$2}++;
	$self->{from_awl_match_count}++;
    } elsif ($event =~ /^new: ($ipregexp)\($ipregexp\), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{new}{$1}++;
	$self->{new_count}++;
    } elsif ($event =~ /^throttling: ($ipregexp)\($ipregexp\), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{throttled}{$1}{$2}++;
	$self->{throttled_count}++;
    } elsif ($event =~ /^early reconnect: ($ipregexp)\($ipregexp\), (.*) -> (.*)$/i) {
	$self->{events}++;
	$self->{early}{$1}++;
	$self->{early_count}++;
    } elsif ($event =~ /^reconnect ok: ($ipregexp)\($ipregexp\), (.*) -> (.*) \((.*)\)/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{reconnect}{$1}{$2}++;
	$self->{reconnect_count}++;
    } elsif ($event =~ /^domain awl: $ipregexp, .* added$/i) {
	## what?
    } elsif ($event =~ /^from awl: $ipregexp, .* added$/i) {
	## what?
    } elsif ($event =~ /^from awl: $ipregexp, .* added/i) {
	## what?
    } elsif ($event =~ /^domain awl: $ipregexp, .* added/i) {
	## what?
    } else {
	$self->debug("unknown grey event at $time: $event\n");
    }
}

sub parse_whitelist {
    my ($self, $time, $event) = @_;
    if ($event =~ /^.*, $ipregexp\(.*\) -> .*$/i) {
	$self->{events}++;
	$self->{passed}++;
	$self->{whitelisted}++;
    } else {
	$self->debug("unknown whitelist event at $time: $event\n");
    }
}

sub parse_spam {
    my ($self, $time, $event) = @_;
    if ($event =~ /^([\d\.]+): (.*) -> (.*) at (.*)$/) {
	$self->{rejected_count}++;
	$self->{rejected}{$1}{$2}++;
    } else {
	$self->debug("unknown spam event at $time: $event\n");
    }
}

# TODO
sub parse_perf {
}

# distribute processing to appropriate parser
sub parse_line {
    my ($self, $line) = @_;

    my ($time, $type, $event) = $self->split_date_event($line);
    if (! defined $time) {
	return;
    }
    # else parse event
    if ($type eq 'grey') {
	$self->parse_grey($time, $event);
    } elsif ($type eq 'whitelist') {
	$self->parse_whitelist($time, $event);
    } elsif ($type eq 'spam') {
	$self->parse_spam($time, $event);
    } elsif ($type eq 'perf') {
	$self->parse_perf($time, $event);
    } # don't care for other types
}

# format a title
sub print_title {
    my $self = shift;
    my $title = shift;
    my $ln = length($title);
    my $line = ' ' . '-' x ($ln + 2) . ' ';
    print $line . "\n";
    print "| $title |\n";
    print $line . "\n\n";
}

# breaks down and print an hash
sub print_distribution {
    my $self = shift;
    my $hash_to_print = shift;
    my $max_to_print = shift;
    my $title = shift;

    my @top;
    my $idx;
    my $count = 0;
    foreach my $id (keys(%{$hash_to_print})) {
	$count++;
	my $hash;
	$hash->{count} = 0;
	$hash->{id} = $id;
	foreach my $subval (keys(%{$hash_to_print->{$id}})) {
	    $hash->{count} += $hash_to_print->{$id}{$subval};
	}
	$top[$#top+1] = $hash;
	@top = reverse sort { $a->{count} <=> $b->{count} } @top;
	pop @top if (($max_to_print != -1) && ($#top >= $max_to_print));
    }
    if ($max_to_print != -1) {
	$self->print_title("$title (top " . ($#top + 1) . ", " . ($#top + 1 - $count) . " hidden)");
    } else {
	$self->print_title($title);
    }
    for ($idx = 0; $idx <= $#top; $idx++) {
	my @dtop;
	foreach my $subval (keys(%{$hash_to_print->{$top[$idx]->{id}}})) {
	    my $hash;
	    $hash->{count} = $hash_to_print->{$top[$idx]->{id}}{$subval};
	    $hash->{domain} = $subval;
	    $dtop[$#dtop+1] = $hash;
	    @dtop = sort { $a->{count} <=> $b->{count} } @dtop;
	}
	@dtop = reverse @dtop;
	print "$top[$idx]->{id}: $top[$idx]->{count}\n";
	for (my $didx = 0; $didx <= $#dtop; $didx++) {
	    print "            $dtop[$didx]->{domain}: $dtop[$didx]->{count}\n";
	}
    }
    print "\n";
}
sub print_domain_awl {
    my $self = shift;
    $self->print_distribution($self->{domain_awl_match}, $self->{top_domain},
			      "Domain AWL");
}

sub print_from_awl {
    my $self = shift;

    $self->print_distribution($self->{from_awl_match}, $self->{top_from},
			      "From AWL");
}

sub print_spam {
    my $self = shift;

    $self->print_distribution($self->{rejected}, $self->{top_spam},
			      "Spam");
}

sub print_delayed {
    my $self = shift;

    if (! defined $self->{print_delayed}) {
	return;
    }
    $self->print_distribution($self->{reconnect}, -1,
			      "Delayed");
}

sub print_throttled {
    my $self = shift;

    $self->print_distribution($self->{throttled}, $self->{top_throttled},
			      "Throttled");
}

sub print_stats {
    my $self = shift;
    print "##################\n" .
	"## Global stats ##\n" .
	"##################\n\n";
    print "Events        : " . $self->{events} . "\n";
    print "Passed        : " . $self->{passed} . "\n";
    print "Early         : " . $self->{early_count} . "\n";
    print "Delayed       : " . $self->{new_count} . "\n\n";

    print "Probable SPAM : " . $self->{rejected_count} . "\n";
    print "Throttled     : " . $self->{throttled_count} . "\n\n";

    print "###############################\n" .
	"## Whitelist/AWL performance ##\n" .
	"###############################\n\n";
    print "Breakdown for $self->{passed} accepted messages:\n\n";

    print "Whitelists  : " .
        percent($self->{whitelisted}, $self->{passed}) .
	"\t($self->{whitelisted})\n";
    print "Domain AWL  : " .
        percent($self->{domain_awl_match_count}, $self->{passed}) .
        "\t($self->{domain_awl_match_count})\n";
    print "From AWL    : " .
	percent($self->{from_awl_match_count}, $self->{passed}) .
	"\t($self->{from_awl_match_count})\n";
    print "Delayed     : " .
	percent($self->{reconnect_count},$self->{passed}) .
	"\t($self->{reconnect_count})\n\n";

    $self->print_domain_awl();
    $self->print_from_awl();
    $self->print_spam();
    $self->print_throttled();
    $self->print_delayed();
}

# create parser with no period limits
# and counters set to 0
my $parser = bless {
    begin => 0,
    end => (1 << 31) - 1,
    programname => 'sqlgrey',
    events => 0,
    passed => 0,
    whitelisted => 0,
    rejected_count => 0,
    new_count => 0,
    throttled_count => 0,
    early_count => 0,
    domain_awl_match_count => 0,
    from_awl_match_count => 0,
    domain_awl_match => {},
    from_awl_match => {},
    rejected => {},
    reconnect => {},
    reconnect_count => 0,
    top_domain => -1,
    top_from => -1,
    top_spam => -1,
    top_throttled => -1,
}, 'sqlgrey_logstats';

$parser->parse_args();

while (<STDIN>) {
    chomp;
    $parser->parse_line($_);
}

$parser->print_stats();

__END__

=head1 NAME

sqlgrey-logstats.pl - SQLgrey log parser

=head1 SYNOPSIS

B<sqlgrey-logstats.pl> [I<options>...] < syslogfile

 -h, --help             display this help and exit
     --man              display man page
     --version          output version information and exit
     --debug            output detailed log parsing steps

 -y, --yesterday        compute stats for yesterday
 -t, --today            compute stats for today
     --lasthour         compute stats for last hour
 -d, --lastday          compute stats for last 24 hours
 -w, --lastweek         compute stats for last 7 days

     --programname      program name looked into log file

     --top-from         how many from AWL entries to print (default: all)
     --top-domain       how many domain AWL entries to print (default: all)
     --top-spam         how many SPAM sources to print (default: all)
     --top-throttled    how many throttled sources to print (default: all)
     --print-delayed    print delayed sources (default: don't)

=head1 DESCRIPTION

sqlgrey-logstats.pl ...

=head1 SEE ALSO

See L<http://www.greylisting.org/> for a description of what greylisting
is and L<http://www.postfix.org/SMTPD_POLICY_README.html> for a
description of how Postfix policy servers work.

=head1 COPYRIGHT

Copyright (c) 2004 by Lionel Bouton.

=head1 LICENSE

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

=head1 AUTHOR

S<Lionel Bouton E<lt>lionel-dev@bouton.nameE<gt>>

=cut
