#!/usr/local/ap-mailfilter3/bin/perl -w
use strict;

use FindBin qw($Bin);
use lib "/usr/local/ap-mailfilter3/control/lib";

use vars qw($CONST $CONFIG $LANG $MENU @ERR);

use CGI qw(:standard);
use CGI::Carp;
#use POSIX qw(strftime);
use Data::Dumper;

BEGIN {
  require 'cgiutil.pl';
  require 'config.pl';
  require 'stsconfig.pl';
  require 'rc/design.pl';
  require 'utils.pl';
};

my %param = GetParams();
my %error;

my ($checks,$defines,$basedef);

$error{error} = $LANG->{error}{cant_access_policy_data} unless( STSCFG_IsReady() );

if(!defined($error{error}) )
{
  STSConfig::compile();
  if( STSCFG_Succeeded(\%error,'STSConfig::compile',$LANG->{error}{cant_build_profiles}, $LANG->{error}{warn_build_profiles}) )
  {
    $error{notice} = $LANG->{message}{applied};
  }

  RestartHelper(\%error);
}

STSCFG_PushErrorLog() if( $error{warning} || $error{error} );


# Build page


ErrorBlock( top=>1, %error ) if( $error{warning} || $error{error} );
ErrorBlock( %error );

