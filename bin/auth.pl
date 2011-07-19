#!/usr/bin/perl
#apt: libauthen-simple-perl
use Authen::Simple::Passwd;
verify_user(@ARGV[0],@ARGV[1]);

sub verify_user{
my ($username, $password )= @_;

    my $passwd = Authen::Simple::Passwd->new( 
        path => '/etc/passwd'
    );

    if ( $passwd->authenticate( $username, $password ) ) {
        print("ok");
    }else{
	print "failed $username, $password\n";}

}




