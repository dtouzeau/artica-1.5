#!/usr/bin/perl -w

# htmlspy.pl: Mail spy log htmlizer and summarizer
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
# 
# This program summarises the mailspy log from stdin, creating files in a
# directory named as ARGV[0].  (A separate program, mailspy, creates this log -
# traditionally /var/log/mailspy/mailspy.log)  htmlspy.pl is called from
# cronspy.sh which manages the log rotation and directory naming.

#
# Summary mode
#
#	For each [sender|sender domain|recipient|recipient domain]
#		Top 60 by number of messages
#		Top 60 by total size of messages (bytes)
#		Top 60 by number of attachments
#
# Detail mode
#
#	For a [sender|sender domain|recipient|recipient domain]
#		Date	From	To...	Size	Subject		File(s)
#
$revision = '$Id: htmlspy.pl,v 1.33 2006/05/16 12:52:15 andrewm Exp $';
$revision =~ s/(^.Id: htmlspy,v | 20.*)//g;

$mailsperpage = 200;	# show log in batches of n
$toplimit = 100;	# show the top n of other domains
$mytoplimit = 300;	# show the top n of our domain (there must be a limit, since we may have something silly like a mailing list)
$maxnamelength = 20;
$maxfilelength = 20;
$manglewidth = 30;	# HTML optional break width
$subjectlimit = 20;
$mydomains = '.*ledge\.co\.za|localhost';
$excludelocalmail = 0;	# we don't reporreport about mail from us to ourselves if this is 1
$bodyattr = 'bgcolor=white text=#000000 link=#0000C0 vlink=#0000C0';
$cellheadcolour = 'bgcolor=#DDDDDD';
$maxlogentries = 250;	# 250 mails is a hang of a lot ...
$maillogdir="mail";
$domainlogdir="domain";
$logdir="log";
$listrel='<BASE HREF="../">';
$startingtime=time();
$linecount=0;
$linecountunused=0;
$linecountmailswithlocalrecipunreported=0;
sub gettimezone()
{
	@ar=localtime(0); # 1 jan 1970 - at least 00h00 I hope
	 #  0    1    2     3     4    5     6     7
	 # ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday) =
	 #                                                                                gmtime(time);
	my $zone=$ar[2];
	if ($zone>12) { $zone-=24; }
	return $zone;  # JHB = 2
}
$timezone=gettimezone();

# here's a chance for you to redefine $mydomains
# you can also set $excludelocalmail=1
-e '/etc/mailspy.conf' && require '/etc/mailspy.conf';
# Whee! Don't try this with someone else's mailspy.conf in the path
-e 'mailspy.conf' && require 'mailspy.conf';

# Debug code for ismydomain ...
#foreach $user ( 'joe@ledge.co.za', 'dx@tonto.ledge.co.za', 'fred@frod.fx' ) {
#	print "ismydomain($user)=" . ismydomain($user) . "\n";
#}

use English;
no warnings qw{uninitialized};

my $path=$ARGV[0] || die "Usage: $0 output_dir [user list file] [manglewords] < /var/log/mailspy/mailspy.log\n";
# Special magic
my $debugoptions=$ARGV[2];

# The userlist file is a list of names who should be included in the report
# Format is:
#	# Comments ...
#	Full name: e-mail address : other information
if (($userlistfilename=$ARGV[1])) {
	open USERLIST, "<$userlistfilename" || die "$userlistfilename: $!\n";
	while (<USERLIST>) {
		if ( m/^\s*#/ ) { next; }
		if ( m/^\s*(\S+)/ ) {
			$desc=$1;
			$user=simplifyemailaddr($1);
			$userlist{$user}=$desc;
		}
	}
	close USERLIST;
	$useuserlist = 1;
}
else {
	$useuserlist = 0;
}
chdir $path || die "chdir: $!\n";

# A log directory for mails
mkdir $maillogdir;
mkdir $domainlogdir;
mkdir $logdir;

$log = "log";
$basename="mails";
$basedom="domains";
$ext=".html";
$messagelog="log$ext";
$indexfile="range.txt";

$trownowrap="<TR><TD valign=top nowrap>";
$trow="<TR><TD valign=top>";
$tsep="</TD><TD valign=top>";
$tosep="; ";  # Between to-addresses (possibly they want a BR)
$filesep="<BR>";
$trowend="</TD></TR>";

# A title for an output page
sub htmlheader
{
	my $headline = shift;
	my $title = shift;
	my $headelement = shift;
	return "<HTML>
<HEAD>$headelement
<TITLE>$title</TITLE>
</HEAD>
<BODY $bodyattr>
".fileheader()."
<H1>$headline</H1>
";
}

sub htmlfooter()
{
	my $secs = time() - $startingtime;
	my $note = '';
	if ($excludelocalmail) {
		$note = "$linecountunused local mails unreported,".
		"$linecountmailswithlocalrecipunreported local mails partially reported ";
	}
	return "<HR><FONT size=-4>Generated at " . 
	localtime . " by " . $ENV{'HOSTNAME'}.
	"<br>[ $usedlinecount of $linecount records in " .  sprintf("%d:%02d",$secs/60 , $secs%60) . " $note]" .
	"<br> $revision</FONT></BODY></HTML>";
}

sub cleanupname($)
{
	my $name = shift;
	$name =~ s/[\W]/-/g;
	return $name;
}

sub htmlescape($)
{
	my $data = shift;
	$data =~ s/\&/&amp;/g;
	$data =~ s/\</&lt;/g;
	$data =~ s/\>/&gt;/g;
	return $data;
}

sub htmlmangle($)
{
	my $data = shift;
	$data =~ s/(.{$manglewidth})/$1-MARKER-/g;
	$data =~ s/\&/&amp;/g;
	$data =~ s/\</&lt;/g;
	$data =~ s/\>/&gt;/g;
	$data =~ s/-MARKER-/<!-- -->/g;
	return $data;
}

sub logtableheader()
{
	return "
<TR>
<TD $cellheadcolour><u>date</u></TD>
<TD $cellheadcolour><u>from &gt;&gt; to</u></TD>
<TD $cellheadcolour><u>size</u></TD>
<TD $cellheadcolour><u>subject</u></TD>
<TD $cellheadcolour><u>attach</u></TD>
</TR>
";
}

sub fileheader()
{
	return "<p align=center>
	<A href=$basename-m$ext>Top addresses</A> |
	<A href=$basedom-m$ext>Top domains</A> |
	<A href=$messagelog>Message log</A>
	</p>
	";
}

sub logfileheader($$)
{
	my $num = shift;
	my $logtime = shift;
	return "<HTML>
<HEAD>
<TITLE>Message log ($num)</TITLE>
$listrel
</HEAD>
<BODY $bodyattr>
".fileheader()."
<H1>Message log: from message $num [ $logtime ] </H1>
<TABLE cellpadding=2>
" . logtableheader();
}

sub logtablefooter()
{
	return "</TABLE>" ;
}

# How we name log files
sub logfilename($)
{
	return "$logdir/log$logcount$ext";
}

# Write the latest line to a couple of logs
sub newlogline($)
{
	my $logtime = shift;
	$logcount++;
	if ($logcount == 1) {
		$reportstarttime = $logtime;
	}
	$reportendtime=$logtime;
	if ( ($logcount % $mailsperpage) == 1) {
		if ($logcount != 1) {
			print LOGFILE logtablefooter() . htmlfooter();
			close LOGFILE;
		}
		open LOGFILE,"> " . logfilename($logcount);
		$logfiles{$logcount} = logfilename($logcount);
		$currentlog=$logcount;
		print LOGFILE logfileheader($logcount, $logtime);
		$logfirst{$currentlog}=$logtime;
	}
	$loglast{$currentlog}=$logtime;
}

# Convert an email address to a html file name
sub emailtofilename($$)
{
	my $from = shift;
	my $email = shift;
	# my $filename="$from$email.html";
	$email =~ s/[^.0-9a-zA-Z-]/-/g;
	return "$maillogdir/$email$ext";
}

# Convert a domain name to a html file name
sub domaintofilename($$)
{
	my $from = shift;
	my $email = shift;
	$email =~ s/[^.0-9a-zA-Z-]/-/g;
	return "$domainlogdir/$email$ext";
}

sub emailtologfilename($$)
{
	my $from = shift;
	my $email = shift;
	my $filename="$email.$from" . "log";
	$filename =~ s/[^.0-9a-zA-Z-]/-/g;
	return $filename;
}

# Write the email to a log file for the address (from-joe.log and to-joe.log)
sub logtoemailfile($$$)
{
	my $from = shift;
	my $email = shift;
	my $logline = shift;
	my $filename=emailtologfilename($from,$email);
	if ( ! $knownfile{$filename}++ ) {
		open QLOG,">$filename" || die "$filename: $!\n";
	}
	else {
		if ($knownfile{$filename} > $maxlogentries) {
			return;
		}
		open QLOG,">>$filename" || die "$filename: $!\n";
	}
	if ($knownfile{$filename} == $maxlogentries) {
		print QLOG thelogisfull();
	}
	else {
		print QLOG $logline;
	}
	close(QLOG);
}

# Generate a log line for a mail
sub genlogline($$$$$$)
{
	my $logtime = shift;
	my $from = shift;
	my $to = shift;
	my $size = shift;
	my $files = shift;
	my $email = shift;

	my $first = 1;
	my $logline = $trownowrap . $logtime . $tsep;
	my $havefrom = 0;
	if ( $from ne $email || ! @$to) {
		$from =~ m/(.*)@(.*)/;
		my $one = $1;
		my $two = $2;
		my $dom = $two;
		$two =~ s/\Q$email\E//;  # if it's a domain name
		$logline = $logline .
		"<A HREF=" . emailtofilename("from",$from) . ">" .  htmlmangle($one) . "</A>" .
		"<A HREF=" .  domaintofilename("from",$dom) . ">@" .  htmlmangle($two) . "</A>" ;
		$havefrom = 1;
	}
	foreach $to (@$to) {
		if ( $to ne $email) {
			if ( $first ) {
				$first=0;
				if ($havefrom) {
					$logline = $logline ." &gt;&gt; " ;
				}
			}
			else {
				$logline = $logline . $tosep;
				# separate entries with ';'
			}
			$to =~ m/(.*)@(.*)/;
			my $one = $1;
			my $two = $2;
			my $dom = $two;
			$two =~ s/\Q$email\E//;  # if it's a domain name
			$logline = $logline .
			"<A HREF=" . emailtofilename("to",$to) . ">" .  htmlmangle($one) . "</A>" .
			"<A HREF=" .  domaintofilename("to",$dom) . ">@" .  htmlmangle($two) . "</A>" ;
		}
	}
	$logline = $logline . "$tsep " . showsize($size) . " $tsep " .
		htmlescape($subject) .
		"$tsep";
	$notfirst = 0;
	foreach my $file (@$files) {
		# Shorten file names by removing stuff before the .
		if (length($file)>$maxfilelength) {
			my $ofile = $file;
			my $extra = length($file)-$maxfilelength;
			if ( ! ( $file =~ s/(.*).{$extra}\.(.*)/$1..$2/ ) ) {
				$extra++;
				$file =~ s/(.*).{$extra}($)/$1../;
			}
		}
		if ($notfirst) {
			$logline = $logline . $filesep;
		}
		$notfirst=1;
		$logline = $logline . htmlescape($file);
	}
	$logline = $logline . "$trowend\n";
	return $logline;
}

# Message for a full log file
sub thelogisfull()
{
	return "<TR bgcolor=FF8080><TD valign=top colspan=5>The log contains
		more than $maxlogentries entries, the remainder of which are not
		displayed.</TD></TR>";
}

# Make an e-mail address manageable (and chop if too long)
sub simplifyemailaddr($)
{
	my $mail = shift;
	$mail =~ s/^[\s\<]+|[\s\>]+$//g;
	$mail =~ tr/A-Z/a-z/;
	# handle long from names
	$mail =~ s/(.{$maxnamelength}).*(\@.*)/$1..$2/ ;
	if ($mail eq "") {
		$mail = "bounce\@returned";
	}
	return $mail;
}

#	For each [sender|sender domain|recipient|recipient domain]
#		Top 60 by number of messages
#		Top 60 by total size of messages (bytes)
#		Top 60 by number of attachments


# make the size human readable
sub showsize($)
{
	my $bytes = shift;
	if ($bytes < 1024) {
		return "$bytes";
	}
	elsif ($bytes < 1024*1024) {
		return sprintf("%d", ($bytes+512) / 1024) . "k";
	}
	else {
		return sprintf("%d", ($bytes+512*1024) / 1024 /1024 ) . "M";
	}

}

# Check whether the address is addr@mydomains
sub ismydomain($)
{
	my $address = shift;
	# print "$address >> " , ( $address =~ $mydomainsregex ) + 0 , "\n";
	return ( $address =~ $mydomainsregex ) + 0;
}

# Highlight the cell we are sorting by
#	highlightsortcell($suffix,$basename,"mails","m").
sub highlightsortcell($$$$)
{
	my ($suffix,$basename,$prompt,$thissfx) = @_;
	if ($suffix eq $thissfx) {
	return "<td $cellheadcolour><b><a href=$basename$thissfx$ext>$prompt</a></b></td>";
	}
	else {
	return "<td $cellheadcolour><a href=$basename$thissfx$ext>$prompt</a></td>";
	}
}
sub dosortedblock($$$$$$)
{
	my $prompt = shift;
	my $hash = shift;
	my $from = shift;
	my $isdomain = shift;
	my $basename = shift;
	my $suffix = shift;
	my $user, $href, $uhref;
	print SUMMARY "<TABLE width=100%>\n";
	if ($isdomain) { @ismydomainopts = ( 0 ); }
	else { @ismydomainopts = ( 1, 0 ); }

	foreach $requiremydomain ( @ismydomainopts ) {
	my $count = 0;
	print SUMMARY "
	<tr>
	<td $cellheadcolour>Address</td>
	<td $cellheadcolour colspan=3>Total</td>
	<td $cellheadcolour colspan=3>Sender</td>
	<td $cellheadcolour colspan=3>Recipient</td>
	</tr>
	<tr>
	<td $cellheadcolour>&nbsp;</td>".
	highlightsortcell($suffix,$basename,"mails","-m").
	highlightsortcell($suffix,$basename,"size","-s").
	highlightsortcell($suffix,$basename,"attach","-a").
	highlightsortcell($suffix,$basename,"mails","-sm").
	highlightsortcell($suffix,$basename,"size","-ss").
	highlightsortcell($suffix,$basename,"attach","-sa").
	highlightsortcell($suffix,$basename,"mails","-rm").
	highlightsortcell($suffix,$basename,"size","-rs").
	highlightsortcell($suffix,$basename,"attach","-ra").
	"
	</tr>\n";
	foreach $user (sort { $$hash{$b} <=> $$hash{$a} } keys %$hash) {
		if ($requiremydomain != ismydomain($user)) {
			next;
		}
		if ($isdomain) {
			$href = "<A href=" .  domaintofilename($from,$user) . ">";
		}
		else {
			$href = "<A href=" .  emailtofilename($from,$user) . ">";
		}
		$uhref = "</A>";
		# sorted by $$hash{$user}
		if ($isdomain) {
			$totalcount = $fromdomaincount{$user} + $todomaincount{$user};
			$totalsize = $fromdomainsize{$user} + $todomainsize{$user};
			$totalfiles = $fromdomainfiles{$user} + $todomainfiles{$user};
			print SUMMARY "
				$trow $href$user$uhref
				$tsep $totalcount
				$tsep " . showsize($totalsize) . "
				$tsep $totalfiles
				$tsep $fromdomaincount{$user}
				$tsep " . showsize($fromdomainsize{$user}) . "
				$tsep $fromdomainfiles{$user}
				$tsep $todomaincount{$user}
				$tsep " . showsize($todomainsize{$user}) . "
				$tsep $todomainfiles{$user}
				$trowend\n";
		}
		else {
			print SUMMARY "
				$trow $href$user$uhref
				$tsep $mailcount{$user}
				$tsep " . showsize($mailsize{$user}) . "
				$tsep $mailfiles{$user}
				$tsep $fromcount{$user}
				$tsep " . showsize($fromsize{$user}) . "
				$tsep $fromfiles{$user}
				$tsep $tocount{$user}
				$tsep " . showsize($tosize{$user}) . "
				$tsep $tofiles{$user}
				$trowend\n";
		}
		$count++;
		if ($requiremydomain) {
			$count == $mytoplimit && last ;
		}
		else {
			$count == $toplimit && last ;
		}
	}
	}
	print SUMMARY "</TABLE>\n\n";
}

# total               sender              recipient
# mail  size  attach  mail  size  attach  mail  size  attach
# -m    -s    -a      -sm   -ss   -sa     -rm   -rs   -ra
sub dosortedreport($$$$$$)
{
	my $prompt = shift;
	my $hash = shift;
	my $from = shift;
	my $isdomain = shift;
	my $basename = shift;
	my $suffix = shift;

	open SUMMARY,">$basename$suffix$ext" || die "$basename$suffix$ext: $!\n";
	print SUMMARY htmlheader("$prompt $reporttimespan","$reporttimespan");
	# print SUMMARY "<H2>$prompt</H2>\n";
	dosortedblock($prompt, $hash, $from, $isdomain,$basename,$suffix);
	print SUMMARY htmlfooter();
	close(SUMMARY);
}

# Collate from.log and to.log into a domain or e-mail address log and summary
# page
sub collatemessages($$)
{
	my $email = shift;
	my $isdomain = shift;
	my $filename;
	if ($isdomain) {
		$filename = domaintofilename("for",$email);
	}
	else {
		$filename = emailtofilename("for",$email);
	}
	open QLOG,">$filename" || die "$filename: $!\n";
	print QLOG htmlheader("Summary for $email",$email,$listrel);
	print QLOG "<TABLE cellpadding=2>";
	if ($isdomain) {
		print QLOG "
		<TR>
		<TD $cellheadcolour align=right>&nbsp;</TD>
		<TD $cellheadcolour align=left><u>Mails</u></TD>
		<TD $cellheadcolour align=left><u>Size</u></TD>
		<TD $cellheadcolour align=left><u>Attachments</u></TD>
		</TR>
		<TR>
		<TD align=right>As sender</TD>
		<TD align=left>$fromdomaincount{$email}</TD>
		<TD align=left>" . showsize($fromdomainsize{$email}) . "</TD>
		<TD align=left>$fromdomainfiles{$email}</TD>
		</TR>
		<TR>
		<TD align=right>As recipient</TD>
		<TD align=left>$todomaincount{$email}</TD>
		<TD align=left>" . showsize($todomainsize{$email}) . "</TD>
		<TD align=left>$todomainfiles{$email}</TD>
		</TR>
		<TR>
		<TD align=right>Total mails</TD>
		<TD align=left>$domaincount{$email}</TD>
		<TD align=left>" . showsize($domainsize{$email}) . "</TD>
		<TD align=left>$domainfiles{$email}</TD>
		</TR>";
	}
	else {
		print QLOG "
		<TR>
		<TD $cellheadcolour>&nbsp;</TD>
		<TD $cellheadcolour><u>Mails</u></TD>
		<TD $cellheadcolour><u>Size</u></TD>
		<TD $cellheadcolour><u>Attachments</u></TD>
		</TR>
		<TR>
		<TD align=right>As sender</TD>
		<TD>$fromcount{$email}</TD>
		<TD>" . showsize($fromsize{$email}) . "</TD>
		<TD>$fromfiles{$email}</TD>
		</TR>
		<TR>
		<TD align=right>As recipient</TD>
		<TD>$tocount{$email}</TD>
		<TD>" . showsize($tosize{$email}) . "</TD>
		<TD>$tofiles{$email}</TD>
		</TR>
		<TD align=right>Total mails</TD>
		<TD>$mailcount{$email}</TD>
		<TD>" . showsize($mailsize{$email}) . "</TD>
		<TD>$mailfiles{$email}</TD>
		</TR>";
	}

	print QLOG "</TABLE>\n\n<TABLE cellpadding=2>";
	my $logfile=emailtologfilename("from",$email);
	if ($knownfile{$logfile}) {
		print QLOG "<TR><TD colspan=5><H2>Mail from $email</H2></TD></TR>";
		print QLOG logtableheader();
		open MAILS,"<$logfile";
		print QLOG <MAILS>;
		close MAILS;
		unlink($logfile);
	}

	$logfile=emailtologfilename("to",$email);
	if ($knownfile{$logfile}) {
		print QLOG "<TR><TD colspan=5><H2>Mail to $email</H2></TD></TR>";
		print QLOG logtableheader();
		open MAILS,"<$logfile";
		print QLOG <MAILS>;
		close MAILS;
		unlink($logfile);
	}
	print QLOG logtablefooter();
	print QLOG htmlfooter();
	close QLOG;
}

# Replace words in a phrase with junk words
sub domanglewords($)
{
	$phrase = shift;
	if ($manglewords) {
		$phrase =~ s/(\W*)?(\w+)(\W*)/$1.domangleword($2).$3/eg;
	}
	return $phrase;
}

$manglewords =  ( $debugoptions =~ /manglewords/ ) ;
if ($manglewords) {
	open WORDS,"</usr/share/dict/words";
	while (<WORDS>) {
		chomp;
		my $word=$_;
		push @{$words[length($word)]}, $word;
	}
	close WORDS;
	$test = "joe\@domain.com's test mangle: This is a test sentence of words to test.\n";
	print $test;
	print domanglewords($test);
	print "Domains: $mydomains\n" . 
	      "Domains: " . ( $mydomains = domanglewords($mydomains) ) . "\n";
}
$mydomainsregex = qr{\@($mydomains)$}i;

# Translate =?...?...?...?= to ...
sub demime($)
{
	my $word = shift;
	$word =~ s/\Q=?\E(.*?)\Q?\E(.*?)\Q?\E(.*?)\Q?\E(=|$)/$3/g;
	return $word;
}

# Replace a word with a random dictionary value of the same length
sub domangleword($)
{
	my $word = shift;
	if ($uniqueword{$word}) {
		return $uniqueword{$word};
	}
	my $count = scalar @{$words[length($word)]};
	my $index = int(rand($count));
	return $uniqueword{$word} = $words[length($word)][$index] || $word;
	# if we don't find a word of the same length use the original..hey, 
	# why not ...
}


# main()
# Process file 
while (<STDIN>) {
	undef @to;
	undef @files;
	undef $filecount;

	$linecount++;
	# processor for our log file format
	m/(.*?)\t/g;
	$logtime=$1;
	while (m/(.*?)=([^\t]*?)(\t|\n|$)/g) { 
		$fld = $1;
		$val = $2;
		if ($fld eq "size" || $fld eq "body") { $size = $val; }
		else {
		$val = domanglewords($val);
		if ($fld eq "from") { $from = $val; }
		elsif ($fld eq "to") { push @to, $val; }
		elsif ($fld eq "subject") { 
			$subject = $val;
			$subject = demime($subject);
			$subject =~ s/(.{$subjectlimit}).*/$1../;
			#                   chs==     enc==     text=
		}
		elsif ($fld eq "file") { push @files, demime($val); $filecount++; }
		}
	}

	# Canonicize and analyse
	$from = simplifyemailaddr($from);

	# eliminate names not in my list if mail is from `outsider'
	if ($useuserlist) {
		if (!$userlist{$from}) {
			@newto = ();
			foreach $to (@to) {
				if ($userlist{simplifyemailaddr($to)} ) { 
					push @newto, $to;
				}
			}
			if ($#newto == -1) { # Eliminate stuff with our email addresses missing
				next;
			}
			@to = @newto;
		}
	}

	# Eliminate local mail if we are set up that way
	if ($excludelocalmail && ismydomain($from)) {
		@newto = ();
		$removedrecipient = 0;
		foreach $to (@to) {
			if ( ismydomain(simplifyemailaddr($to)) ) { 
				$removedrecipient = 1;
			}
			else {
				push @newto, $to; # keep it
			}
		}
		if ($#newto == -1) {
			# Only local recipients, so we exclude it
			$linecountunused++;
			next;
		}
		$linecountmailswithlocalrecipunreported+=$removedrecipient;
		@to = @newto;
	}
	$usedlinecount++;

	$from =~ m/(.*)@(.*)/;
	$fromdomain = $2;
	$fromname = $1;

	$fromcount{$from} += 1;
	$fromsize{$from}  += $size;
	$fromfiles{$from} += $filecount;

	$mailcount{$from} += 1;
	$mailsize{$from}  += $size;
	$mailfiles{$from} += $filecount;

	$domaincount{$fromdomain} += 1;
	$domainsize{$fromdomain}  += $size;
	$domainfiles{$fromdomain} += $filecount;

	$fromdomaincount{$fromdomain} += 1;
	$fromdomainsize{$fromdomain}  += $size;
	$fromdomainfiles{$fromdomain} += $filecount;

	foreach $to (@to) {
		$to=simplifyemailaddr($to); # FIXME - we might have done this already
		$to =~ m/@(.*)/;
		$todomain = $1;
		$tocount{$to} += 1;
		$tosize{$to}  += $size;
		$tofiles{$to} += $filecount;
		$mailcount{$to} += 1;
		$mailsize{$to}  += $size;
		$mailfiles{$to} += $filecount;

		$todomaincount{$todomain} += 1;
		$todomainsize{$todomain}  += $size;
		$todomainfiles{$todomain} += $filecount;

		$domaincount{$todomain} += 1;
		$domainsize{$todomain}  += $size;
		$domainfiles{$todomain} += $filecount;
	}
	# FROM HERE things get mangled for HTML display

	# FIXME: we need to generate different log lines depending on context
	# for a domain, exclude @domain.  For an e-mail address, drop the
	@days = (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	# Oops! Mailspy logged in GMT - convert to localtime() with day,
	# month and year wrap-around
	if ($logtime =~ m/(\d+)-(\d+)-(\d+)\s+(\d+):(\d+)/) {
		$ologtime=$logtime;
		$yy=$1; $mm=$2; $dd=$3; $HH=$4+$timezone; $MM=$5;
		if ( $HH >= 24 ) { # Positive time zone adjustment
			$HH -= 24;
			$dd++;
			if ( ( $dd>$days[$mm-1] ) ||
				( $mm==2 && $dd==28 && ($yy%4)!=0 ) ) {
				$dd = 1;
				$mm++;
				if ($mm>12) { $mm=1; $yy++; }
			}
		}
		if ( $HH < 0 ) { # Negative timezone adjust - never tested
			$HH+=24;
			$dd--;
			if ($dd==0) {
				$mm--;
				if ($mm==0) { $yy--; $mm=12; }
				$dd=$days[$mm-1];
				if ($mm==2 && (%yy%4)==0) { $dd--; }
			}
		}
		$logtime = sprintf "%.4d-%.2d-%.2d %.2d:%.2d",
			$yy+2000,$mm,$dd,$HH,$MM;
	}
	else {
		next; # Ag- it's probably rubbish
	}

	newlogline($logtime);
	$logtime =~ s/ /&nbsp;/g;  # neater?
	$from =~ m/(.*)@(.*)/;

	$logline = genlogline($logtime,$from,\@to, $size, \@files, '');
	print LOGFILE $logline;
	logtoemailfile("from",$from,
		genlogline($logtime,$from,\@to, $size, \@files, $from));
	logtoemailfile("from",$fromdomain,
		genlogline($logtime,$from,\@to, $size, \@files, $fromdomain));
	foreach $to (@to) {
		$to =~ m/@(.*)/;
		$todomain = $1;
		logtoemailfile("to",$to,
			genlogline($logtime,$from,\@emptyarr, $size, \@files, $to));
		logtoemailfile("to",$todomain,
			genlogline($logtime,$from,\@to, $size, \@files, $todomain));
	}
}
print LOGFILE logtablefooter() . htmlfooter();
close LOGFILE;
 
$reporttimespan = "$reportstarttime to $reportendtime";

# Differently sorted stats for email addresses
dosortedreport("Top addresses by message count",\%mailcount, "from", 0, $basename, "-m");
dosortedreport("Top addresses by size",          \%mailsize, "from", 0, $basename, "-s");
dosortedreport("Top addresses by attachments",  \%mailfiles, "from", 0, $basename, "-a");

dosortedreport("Top senders by message count",\%fromcount, "from", 0, $basename, "-sm");
dosortedreport("Top senders by size",          \%fromsize, "from", 0, $basename, "-ss");
dosortedreport("Top senders by attachments",  \%fromfiles, "from", 0, $basename, "-sa");

dosortedreport("Top recipients by message count",\%tocount, "to", 0, $basename, "-rm");
dosortedreport("Top recipients by size",          \%tosize, "to", 0, $basename, "-rs");
dosortedreport("Top recipients by attachments",  \%tofiles, "to", 0, $basename, "-ra");

# Differently sorted stats for domains
dosortedreport("Top domains by message count",\%domaincount, "from", 1, $basedom, "-m");
dosortedreport("Top domains by size",          \%domainsize, "from", 1, $basedom, "-s");
dosortedreport("Top addresses by attachments",  \%domainfiles, "from", 1, $basedom, "-a");

dosortedreport("Top domains of senders by message count",\%fromdomaincount, "from", 1, $basedom, "-sm");
dosortedreport("Top domains of senders by size",          \%fromdomainsize, "from", 1, $basedom, "-ss");
dosortedreport("Top domains of senders by attachments",  \%fromdomainfiles, "from", 1, $basedom, "-sa");

dosortedreport("Top domains of recipients by message count",\%todomaincount, "to", 1, $basedom, "-rm");
dosortedreport("Top domains of recipients by size",          \%todomainsize, "to", 1, $basedom, "-rs");
dosortedreport("Top domains of recipients by attachments",  \%todomainfiles, "to", 1, $basedom, "-ra");

open INDEX, ">$messagelog" || die "$messagelog: $!\n";
print INDEX htmlheader("Message log for $reporttimespan",$reporttimespan) .  "
	Message log ($mailsperpage per page)

	<table cellpadding=16 border=1 width=100%><tr><td>
";
$filecount = scalar keys %logfiles;
$columns = 4;
$columnlength = int (($filecount + $columns - 1 ) / $columns);
# User web counts and sizes, sorted by user name
$remaining = $columnlength;
foreach $lognum (sort {$a <=> $b} keys %logfiles) {
	# end column
	if ($remaining == 0) {
		print INDEX "</table></td><td>";
		$remaining = $columnlength;
	}
	# begin new column
	if ( --$remaining == $columnlength-1) {
		print INDEX "<table><tr><td>&nbsp;</td><td>Time</td></tr>"
	}
	print INDEX "<tr>
		<td align=right>$lognum.</td>
		<td><A href=$logfiles{$lognum}>$logfirst{$lognum}</A></td>
		</tr>\n";
}
print INDEX "</table></td>
	</tr></table>";

print INDEX "</BODY></HTML>";
close INDEX;

# Convert .tolog and .fromlog to .html
foreach $email ( keys %mailcount ) { collatemessages($email,0); }
foreach $email ( keys %domaincount  ) { collatemessages($email,1); }

# Drop a timestamp file so that we can rebuild the indexes
open INDEX, ">$indexfile" || die "$indexfile: $!\n";
print INDEX "$reportstarttime\n";
close INDEX;

# Guess who is really a lazy person:
print qx{
	cp $basename-m$ext index.html
	cp $basename-m$ext index.htm
};
