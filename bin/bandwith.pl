#!/usr/bin/perl
use IO::Socket::INET;
use Time::HiRes;
use warnings;

my $host = '81.88.96.44';
my $port = 2001;
my $proto = 'tcp';

$MySocket = new

IO::Socket::INET->new( PeerPort=> $port, Proto=> $proto, PeerAddr=> $host)
or die "connexion impossible au port $port sur $host: $!";

$MySocket->autoflush(1);
$msg1 = sprintf "APPLETFAI\nMEASURE\n";
$msg2 = sprintf "DOWNLOAD\n10\n60000\n";
$msg3 = sprintf "OK\n";
$MySocket->send($msg1);
$t1=Time::HiRes::gettimeofday();
while (<$MySocket>) {
if (m/NO REPORT/) {
$MySocket->send($msg2);
}
elsif (m/OK/) {
$MySocket->send($msg3);
}
else { };
}
$t2=Time::HiRes::gettimeofday();
my $bandwidth = 8*560/($t2-$t1);
printf "%6.2f %s",$bandwidth,"Mb/s\n";
exit 0;
