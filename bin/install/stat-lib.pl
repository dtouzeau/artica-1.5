#!/usr/bin/perl -w
#
my $list_breaker=',';
my $daysec = 86400;

sub parse_stat_line
{
    my ($line, $start_timestamp, $end_timestamp, $hash)= @_;

#<Timestamp>
#<Message size in bytes>
#<Sender address>
#<Recipient address(es)>
#<Verdict(s)>
#<Found virus name(s)>
#<Connection IP>
#<Message id>
    if($line=~/(\d+?)\t(\d*?)\t(.*?)\t(.*?)\t(.*?)\t(.*?)\t([\d.]*?)\t(.*)/){
#	print "Parse timestamp: ".gmtime($1)."\n";
	if( $1<$start_timestamp ){
		return 1;
#		next;
	}
	if( $1>$end_timestamp ){
		return 1;
#		last;
	}

	my %tmp_msg_info=();
	$tmp_msg_info{time}=$1;
	$tmp_msg_info{size}=$2;
	$tmp_msg_info{from}=$3;
	my $rcpt_list=$4;
	my $verdicts_list=$5;
	my $virus_list=$6;
	$tmp_msg_info{ip}=$7;
#	Parse virus list
	parse_list(\@{$tmp_msg_info{to}}, $list_breaker, $rcpt_list);
	parse_list(\@{$tmp_msg_info{virs}}, $list_breaker, $virus_list);
	parse_list(\@{$tmp_msg_info{results}}, $list_breaker, $verdicts_list);

#	print "Virus $virus_list\n";
	add_to_stat(\%tmp_msg_info, $hash);
	return 1;
    }
    return 0;
}

sub load_stat{
	my( $hash, $file,  $key) = @_;
	my $day;
	my $stat_data;
	my $line;

	unless(open(STAT, "<$file")){
		print "Can't open stat $file for reading: $!. Old statistic is not available.\n";
		return;
	}
	while(<STAT>)
	{
		$line=$_;
		if($line=~/^(\d+).(\d+).(\d+)\t(.+?)\t(\d*?)$/)
		{
#			print "Line: $line\n";
			$hash->{"$1.$2.$3"}{$key}{$4}=$5;
		}
	}
	close STAT;
}

sub load_time_stat{
	my( $hash, $file) = @_;
	my $day;
	my $stat_data;
	my $line;

	unless(open(STAT, "<$file")){
		print "Can't open stat $file for reading: $!. Old statistic is not available.\n";
		return;
	}
	while(<STAT>)
	{
		$line=$_;
		if($line=~/^(\d+).(\d+).(\d+)\t(\d+?)\t(\d+?)$/)
		{
			$hash->{"$1.$2.$3"}{first_timestamp}=$4;
			$hash->{"$1.$2.$3"}{last_timestamp}=$5;
		}
	}
	close STAT;
}

sub save_stat{
	my( $hash, $file,  $key) = @_;
	my $day;
	my $stat_data;

	open(STAT, ">$file")         or die "can't open $file: $!";
	foreach $day (keys %{$hash}){
		foreach  $stat_data (keys %{$hash->{$day}{$key}}){
			print STAT "$day\t$stat_data\t$hash->{$day}{$key}{$stat_data}\n";
		}
	}
	close STAT;
}

sub save_time_stat{
	my( $hash, $file) = @_;
	my $day;
	my $stat_data;

	open(STAT, ">$file")         or die "can't open $file: $!";
	foreach $day (keys %{$hash}){
		print STAT "$day\t$hash->{$day}{first_timestamp}\t$hash->{$day}{last_timestamp}\n";
	}
	close STAT;
}

sub trim_string{
	my( $str ) = @_;
	$str =~ s/^\s+//;
	$str =~ s/\s+$//;
	return $str;
}

sub inc_stat{
	my( $hash, $val, $add_unknown ) = @_;
	if( $val eq ""){
	    if($add_unknown==1){
		$val="__unknown__" ;
	    }else{
		return;
	    }
	}
	if(!exists($hash->{$val})){
		$hash->{$val}=1;
	}else{
		$hash->{$val}=$hash->{$val}+1;
	}
}
sub set_delete_flag{
	my ($hash, $start_timestamp, $end_timestamp)=@_;

	my @tm;
	my $str_time;
	my $timestamp=$start_timestamp;
	while($timestamp<=$end_timestamp){
		@tm = localtime($timestamp);
		$str_time = $tm[3].".".($tm[4]+1).".".($tm[5]+1900);
		if(exists($hash->{$str_time})){
			$hash->{$str_time}{for_delete}=1;
		}
		$timestamp+=$daysec;
	}
}
sub delete_stat{
	my ($hash, $start_timestamp, $end_timestamp)=@_;

	my @tm;
	my $str_time;
	my $timestamp=$start_timestamp;
	while($timestamp<=$end_timestamp){
		@tm = localtime($timestamp);
		$str_time = $tm[3].".".($tm[4]+1).".".($tm[5]+1900);
		delete $hash->{$str_time};
		$timestamp+=$daysec;
	}
}

sub add_to_stat{
	my( $tmp_info, $stat) = @_;

#	return if !exists($tmp_info->{$id});
	
	my @date=localtime($tmp_info->{time});
	my $year=$date[5]+1900;
	my $month=$date[4]+1;
	my $mday=$date[3];
	my $stat_date="$mday.$month.$year";
	my $virs="";
	my $virs_found = 0;

	if(exists($stat->{$stat_date}{for_delete})){
		delete $stat->{$stat_date};
	}

#	if(exists($stat->{$stat_date}{last_timestamp}) 
#		&& exists($stat->{$stat_date}{first_timestamp}) 
#		&& $stat->{$stat_date}{last_timestamp} > $tmp_info->{time}
#		&& $stat->{$stat_date}{first_timestamp} < $tmp_info->{time}){
#		return;
#	}
	
	if(!exists($stat->{$stat_date}{last_timestamp}) || $tmp_info->{time} > $stat->{$stat_date}{last_timestamp} ){
		$stat->{$stat_date}{last_timestamp}=$tmp_info->{time};
	}
	if(!exists($stat->{$stat_date}{first_timestamp}) || $tmp_info->{time} < $stat->{$stat_date}{first_timestamp} ){
		$stat->{$stat_date}{first_timestamp}=$tmp_info->{time};
	}
#	$stat->{$stat_date}{last_timestamp}=$tmp_info->{time};
	foreach $virs (@{$tmp_info->{virs}}){
		inc_stat(\%{$stat->{$stat_date}{virs}}, $virs, 0);
#		print "VirusName: '$virs'\n";
		$virs_found = 1;
	}
	my $result;
	foreach $result (@{$tmp_info->{results}}){
#		print "Result:".$result."\n";
		next if $result eq "";

		if($result eq "infected" || $result eq "cured" || $result eq "curefailed" || $result eq "disinfected" || $result eq "suspicious" || $result eq "warning" || $virs_found ){
			my $rcpt="";
			foreach $rcpt (@{$tmp_info->{to}}){
#				print "Rcpt:".$rcpt."\n";
				inc_stat(\%{$stat->{$stat_date}{rcpt}}, $rcpt, 0);
			}
			my $sndr=$tmp_info->{from};
			inc_stat(\%{$stat->{$stat_date}{sndr}}, $sndr, 0);
			inc_stat(\%{$stat->{$stat_date}{ip}}, $tmp_info->{ip}, 1);
		}
		inc_stat(\%{$stat->{$stat_date}{result}}, $result, 0);
	}
#	inc_stat(\%{$stat->{$stat_date}{result}}, "__total_msg");
#	delete $tmp_info->{$id};
}

sub parse_list{
	my( $ref, $breaker, $list) = @_;
	my $entry;

	while($list=~/(.*?)$breaker/){
#		print "List element: $1\n";
		$entry=trim_string($1);
		if( $entry ne "" ){
			push( @$ref, $entry);
		}
		$list=$';
	}
	$entry=trim_string($list);
	if( $entry ne "" ){
		push( @$ref, $entry);
	}
}
