#!/usr/bin/perl -w

use strict;
use Net::SMTP;

my $Message;
my $Server  = "smtp.servername.com";
my $Address = "foo\@foobar.be";
my $From    = "foo\@foobar.be";

# Slurp the message and detect/remove the from.
while (<>) {
  if (m/^From:(.*)$/) {
    $From = $1;
    next;
  }
  $Message .= $_;
}


my $Mail = Net::SMTP->new($Server) || 
           die "Could not connect to SMTP server $Server : $!";

$Mail->mail($From);
$Mail->recipient($Address);
$Mail->data($Message);
$Mail->quit();
