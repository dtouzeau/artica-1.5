#!/usr/bin/perl -w

use strict;
use Net::SMTP_auth;

my $Debug    = 1;
my $Message;
my $Server   = "smtp.servername.com";
my $Address  = "foo\@foobar.be";
my $From     = "foo\@foobar.be";
my $AuthType = "login";
my $UserName = "MyUserName";	# For the smtp server.
my $Password = "MyPassword";	# For the smtp server.

# Slurp the message and detect/remove the from.
while (<>) {
  #if (m/^From (.*)$/) {
  #  $From = $1;
  #  next;
  #}
  $Message .= $_;
}

my $Smtp = Net::SMTP_auth->new(Host=>$Server,Debug=>$Debug) || 
           die "Could not connect to SMTP server $Server : $!";

$Smtp->auth($AuthType,$UserName,$Password);
$Smtp->mail($From);
$Smtp->to($Address);
$Smtp->data();
$Smtp->datasend($Message);
$Smtp->dataend();
$Smtp->quit();
