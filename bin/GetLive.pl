#!/usr/bin/perl -w

########################################################################################################################
#
# GetLive - perl script to get mail from hotmail (live) mailboxes.
#
# $Id: GetLive.pl,v 1.43 2008/07/05 19:55:41 jdla Exp $
# $Name: Release_0_57 $
#
# Copyright (C) 2007 Jos De Laender <jos.de_laender@pandora.be>
#
# This work is inspired and partly reuses code from 
# gotmail :
#   Copyright (C) 2000-2003 Peter Hawkins <peterhawkins@ozemail.com.au>
#   Copyright (C) 2005 Jon Phillips <jon@rejon.org>
#   Copyright (C) 2005 Michael Ziegler.
#   Copyright (C) 2005-2006 Jos De Laender <jos.de_laender@pandora.be>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
########################################################################################################################

use strict;
use File::Spec;
use URI::Escape;

########################################################################################################################
#
# XXX
# XXX This is inserted to cope with French characters in the folder names.
# XXX Not too sure about. It works on my LANG=nl_BE.UTF-8 box, but I'm afraid it may screw up other boxes ...
# XXX In my case also use encoding("UTF-8") worked (it's my locale after all). 
# XXX
#
########################################################################################################################

eval "use encoding(\":locale\");";

########################################################################################################################
#
# Global constants and variables.
#
########################################################################################################################

my $ProgramName = "GetLive";
my $Revision    = '$Revision: 1.43.1 $';		# Meant for RCS.

# Constants of configuration.
my $Proxy                   = "";
my $ProxyAuth               = "";
my $Login                   = "";
my $Password                = "";
my $Domain                  = 'hotmail.com';
my $CurlCommand             = 'curl -k';
my $Verbosity               = 1; 									  # 0:Silent; 1:Normal; 2:Verbose; 10:debug; 100:heavy debug 
my $MailProcessor           = '/usr/bin/procmail';	# Any program taking mbox formatted at stdin will do.
my $DownloadedIdsFile       = "";                   # Local file with Ids of already downloaded messages.
my $RetryLimit              = 2;
my $MarkRead                = "No";                 # No,Yes : But never when downloaded before !
my $Delete                  = "No";                 # No,Yes : But never when downloaded before !
my $FetchOnlyUnread         = "No";                 # If Yes, only messages marked unread are downloaded.
                                                    # Unlike gotmail, this is completely orthogonal to the
                                                    # DownloadedIdsFile, i.e. it is the one or the other.
my $MoveToFolder            = "";                   # The name of the folder to move to after the download. "" is not.
                                                    # If it begins with @ it is reference to a filename that
                                                    # contains the folder to move to. This is a hook for 
                                                    # autoclassifying the mail on the server, including spam filtering.

# Quirk. MS generates unended <pre> tags. Hope this is a temporary weakness in their mind.
# (Hope makes living, we say in Dutch ...)
# If we want to allow it set this to one. Currently as of 8/9/2007 it must be accepted or the
# message is not downloadable.

my $AllowUncompletePreTag = 1;

# Yet another quirk. See bug 1875392. We'll call it the DarrenQuirk.
my $CorrectDarrenQuirk = 1;

# Files in a temporary directory.
my $TmpDir             = File::Spec->tmpdir() . "/$ProgramName.$$";
my $TmpCurlHeadersFile = "$TmpDir/Headers";
my $TmpCookiesFile     = "$TmpDir/Cookies";
my $TmpFormDataFile    = "$TmpDir/Form";
my $TmpCurlStderrFile  = "$TmpDir/CurlStderr";
my $TmpCurlStdoutFile  = "$TmpDir/CurlStdout";
my $TmpCurlTraceFile   = "$TmpDir/CurlTrace";

# Messages retrieved from a folder.
my $NrMessagesDetected = 0;
my $NrMessagesUnread   = 0;
my @MessagesFrom    = ();
my @MessagesSubject = ();
my @MessagesId      = ();
my @MessagesAd      = ();
my @MessagesRead    = ();

# Various variables.
my $BaseUrl; 						# The one in the logged in screen used for fetching folders.
my $NParameter;

my @FolderHrefs      = ();	# The Hrefs found for the different folders.
my @FolderIds        = ();	# The Ids found for the different folders.
my @FolderNames      = ();	# The names found for the different folders.
my @FolderNrMessages = ();	# The number of messages found for the different folders.
my $NrFolders        = 0;		# The number of folders found.

my %FoldersToProcess = ();  # The folders to process (empty will be considered as all). Otherwise FolderName=>1 assoc.

my $CurlRun    = 0;			# Increased with each Curl run. Basically for debug reasons.
my $ConfigFile;

my $RequestHandler		= "";  
my $SessionId			    = "";
my $AuthUser			    = "";
my $TrashFolderId		  = "";


########################################################################################################################
# 
# Catchall signal handler. Just observes death and cleans up the mess.
#
########################################################################################################################

$SIG{INT} = $SIG{TERM} = $SIG{__DIE__} = sub {
  my($Text) = @_;
	print STDERR "$ProgramName died with message: '$Text'.";
	CleanTempFiles();
	exit(1);
};

########################################################################################################################
# 
# Display some text.
# First parameter : text to be displayed.
# Then a number of named parameters that are optional. 
# See %args.
#
########################################################################################################################

sub Display($%) {
	my $Text = shift;
	my %Args = (MinVerbosity					=> 0,
							stderr								=> 0,
							@_);

  # stderr messages are under no circumstances suppressed.
	if ($Args{'stderr'}) {
		print STDERR $Text;
		return;
	}

	# Filter out the ones for which the verbosity is too high.
	return if ($Args{'MinVerbosity'} > $Verbosity);

	# And finally print ;-)
  # Stdout is flushed immediate , not to miss error messages.
  my $WasSelected = select(STDOUT);
  $|=1;
  select($WasSelected);

	print STDOUT $Text;

	return;
}

########################################################################################################################
# 
# Display the introduction text.
# Text as argument, stderr as optional named argument to redirect to stderr.
#
########################################################################################################################

sub DisplayIntroText(%) {
	my %Args = (stderr => 0,
              MinVerbosity => 1,
              @_);
	my $Text = 
	  "\n".
	  "$ProgramName $Revision Copyright (C)2007 Jos De Laender.\n".
		"$ProgramName comes with ABSOLUTELY NO WARRANTY.\n".
		"This is free software, and you are welcome to redistribute it\n".
		"under certain conditions; see the file License for details.\n".
		'$Name: Release_0_57 $' . "\n".
		'$Id: GetLive.pl,v 1.43 2008/07/05 19:55:41 jdla Exp $' . "\n".
    "Running at ".localtime(time)." for user $Login.\n";
	Display($Text,%Args);
}

########################################################################################################################
# 
# This is only called in error conditions. Output will go to stderr.
#
########################################################################################################################

sub DisplayUsageAndExit() {
	DisplayIntroText(stderr => 1);
	Display("Usage: $ProgramName --config-file ConfigFile [--verbosity -1..100]\n",stderr => 1);
	exit(1);
}

########################################################################################################################
# 
# Parse the command line
#
########################################################################################################################

sub ParseArgs() {
  my $ArgvAsString =  join(" ",@ARGV);

  # --config-file is a mandatory argument.
  if ($ArgvAsString !~ m/--config-file\s+([\w\/\\~\.\-]+)/si) {
    DisplayUsageAndExit();
  }
  $ConfigFile =  $1;
  $ArgvAsString = $` . $';	  # The matched stuff removed.

  # --verbosity is an optional argument.
  if ($ArgvAsString =~ m/--verbosity\s+(\d+)/si) {
    $Verbosity = $1;
    $ArgvAsString = $` . $';	# The matched stuff removed.
  }
  # Should have no other arguments.
  $ArgvAsString =~ s/\s//sg;
  if ($ArgvAsString ne "") {
    Display("Wrong command line arguments '$ArgvAsString'.\n",stderr => 1);
    DisplayUsageAndExit();
  }
}

########################################################################################################################
# 
# Parse the Configuration File
#
########################################################################################################################

sub ParseConfig {

	open (CONFIG,$ConfigFile) || die "Configuration file '$ConfigFile' could not be opened : $!.";

	# Parse the file
	while (<CONFIG>) {
		my $Line = $_;
		next if ($Line =~ /^#/); # Comment.
		next if ($Line =~ /^\s*$/); # Empty line.
		if (not $Line =~ m/^([a-zA-Z0-9-_]+)/) {
			Display("Wrong configuration line : '$_'.\n",stderr=>1);
			DisplayUsageAndExit();
		}
		my $Option      = $1;
		my $OptionValue = "";
		$Line           = $'; # The remaining of the line.
		if (not $Line =~ m/\s*=\s*\S+/) {
		  Display("Wrong configuration line : '$_' (no value).\n",stderr => 1);
			DisplayUsageAndExit();
		}
		# Remove equals sign and leading, trailing whitespace.
		$Line =~ s/=//;		
		$Line =~ s/^\s+|\s+$//g;
		$OptionValue = $Line;

    if ($Option =~ m/^UserName$/i) {
      $Login = $OptionValue;
    } elsif ($Option =~ m/^Password$/i) {
      $Password = $OptionValue;
    } elsif ($Option =~ m/^Mode$/i) {
      warn "\n'Mode = ...' in the config file is ignored.\nThis version works only for 'Live' mailboxes !\n"; 
    } elsif ($Option =~ m/^Domain$/i) {
      $Domain = $OptionValue;
    } elsif ($Option =~ m/^Proxy$/i) {
      $Proxy = $OptionValue;
    } elsif ($Option =~ m/^ProxyAuth$/i) {
      $ProxyAuth = $OptionValue;
    } elsif ($Option =~ m/^Downloaded$/i) {
      $DownloadedIdsFile = $OptionValue;
    } elsif ($Option =~ m/^RetryLimit$/i) {
      $RetryLimit = $OptionValue;
    } elsif ($Option =~ m/^Processor$/i) {
      $MailProcessor = $OptionValue;
    } elsif ($Option =~ m/^CurlBin$/i) {
      $CurlCommand = $OptionValue;
    } elsif ($Option =~ m/^Folder$/i) {
      $FoldersToProcess{lc $OptionValue} = 1;
    } elsif ($Option =~ m/^FetchOnlyUnread$/i) {
      $FetchOnlyUnread = $OptionValue;
    } elsif ($Option =~ m/^MarkRead$/i) {
      $MarkRead = $OptionValue;
    } elsif ($Option =~ m/^Delete$/i) {
      $Delete = $OptionValue;
    } elsif ($Option =~ m/^MoveToFolder$/i) {
      $MoveToFolder = $OptionValue;
    } else {
		  Display("Wrong configuration line : '$_' (unknown option).\n",stderr=>1);
			DisplayUsageAndExit();
    }
	}
	close(CONFIG);

  # Some sanitychecks.
  if ($Login eq "") {
    Display("UserName should be specified in the configuration file.\n",stderr=>1); 
    DisplayUsageAndExit(); 
  }
  if ($Password eq "") {
    Display("Password should be specified in the configuration file.\n",stderr=>1); 
    DisplayUsageAndExit(); 
  }
  if ($FetchOnlyUnread !~ m/^(No|Yes)$/i) {
    Display("FetchOnlyUnread should take No or Yes as argument in the configuration file.\n",stderr=>1);
    DisplayUsageAndExit(); 
  }
  if ( ($FetchOnlyUnread =~ m/^No$/i) && ($DownloadedIdsFile eq "") ) {
    Display("Downloaded should be specified in the configuration file.\n",stderr=>1); 
    DisplayUsageAndExit(); 
  } 
  if ( ($FetchOnlyUnread =~ m/^Yes$/i) && ($DownloadedIdsFile ne "") ) {
    Display("Downloaded should not be specified in the configuration file when FetchOnlyUnread=Yes.\n",stderr=>1); 
    DisplayUsageAndExit(); 
  } 
  if ($MarkRead !~ m/^(No|Yes)$/i) {
    Display("MarkRead should take No or Yes as argument in the configuration file.\n",stderr=>1);
    DisplayUsageAndExit(); 
  }
  if ($Delete !~ m/^(No|Yes)$/i) {
    Display("Delete should take No or Yes as argument in the configuration file.\n",stderr=>1);
    DisplayUsageAndExit(); 
  }
  if (($Delete =~ m/^Yes$/i) && ($MoveToFolder ne "")) {
    Display("Delete must be 'No' when MoveToFolder is also specified in the configuration file.\n",stderr=>1);
    DisplayUsageAndExit(); 
  }
}

########################################################################################################################
# 
# Clean up any temporary files which are collected in a temporary directory.
#
########################################################################################################################

sub CleanTempFiles() {
  return if ($Verbosity >9);		# Considered debug mode and thus keep the files !
  return if (! -e $TmpDir);     # We're even not at the point that the tmpdir exists ...
  opendir (TMPDIR,$TmpDir) || die "Could not open '$TmpDir' : $!.";
  while (my $FileName = readdir(TMPDIR)) {
    next if $FileName =~ m/^\.$/;			# Not the .
    next if $FileName =~ m/^\.\.$/;		# Nor .. directory
    unlink("$TmpDir/$FileName") || warn "Could not unlink $TmpDir.$FileName : $!";
  }
  closedir (TMPDIR);
  # Finally get rid of the temporary directory itself.
  rmdir($TmpDir) || warn "Could not unlink $TmpDir";
}

########################################################################################################################
# 
# Unescape html characters, widechars become blank along the conversion.
#
# Based on a function with copyright: Bryant H. McGill - 11c Lower Dorset Street, Dublin 1, Ireland
# Use Terms: Free for non-commercial use, commercial use with notification.
#
########################################################################################################################

sub HtmlUnescape($) {
  my $String = shift;
  $String =~ s[&(.*?);]{
    local $_ = $1;
    /^amp$/i ? "&" :
    /^quot$/i ? '"' :
    /^gt$/i ? ">" :
    /^lt$/i ? "<" :
    /^nbsp$/i ? " " :
    /^#(\d+)$/ ? ($1>255 ? "":chr($1)) :
    /^#x([0-9a-f]+)$/i ? (hex($1)>255 ? "": chr(hex($1))) :
    $_
    }gex;
  return $String;
}

########################################################################################################################
# 
# Get a html page, basically via curl. 
# Returns the page as one big string.
# Returns a second string with the latest url.
# The parameters should be reasonably clear. FollowForward will follow a redirection.
#
########################################################################################################################

sub GetPage($%) {
  my %Args = (Url           => "",
							CurlDataArg		=> "",
							FollowForward	=> 0,
							@_);
  my $Url           = $Args{'Url'};
  my $CurlDataArg   = $Args{'CurlDataArg'};
  my $FollowForward = $Args{'FollowForward'};

  die "'No Cookies Alarm' in '$Url'. Structure of hotmail changed ?" if ($Url =~ m/reason=nocookies/i);

	$CurlRun++;

  my $OptionsToCurl = "";

	if ($Proxy) {
    $OptionsToCurl .= "--proxy $Proxy "; 
  }
	if ($ProxyAuth) { 
    $OptionsToCurl .= "--proxy-user $ProxyAuth "; 
  }

  # The files with the Cookies.
  $OptionsToCurl .= "-b $TmpCookiesFile -c $TmpCookiesFile ";

	if ($CurlDataArg ne "") { 
    $OptionsToCurl .= "--data \"$CurlDataArg\" ";
  }

	# Curl is put silent (but with error output) 
	# when not interactive or low verbosity.
	if ( (not -t STDOUT) || ($Verbosity <= 1) ) { 
    $OptionsToCurl .= "-s -S " 
  }

	if ($Verbosity > 9) { 
    $OptionsToCurl .= "-v --trace $TmpCurlTraceFile.$CurlRun" 
  }

	# JDLA curl outputs info via stderr. Catched in file and appended
	# to stdout output in debug mode.
  my $CommandLine = 
		"$CurlCommand --stderr $TmpCurlStderrFile.$CurlRun \"$Url\" " .
    "$OptionsToCurl -i -m 600 -D $TmpCurlHeadersFile.$CurlRun " .
		"-A \"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.5) Gecko/20061201 Firefox/2.0.0.5 (Ubuntu-feisty)\"";
  Display("Curl run $CurlRun.\nCommandLine : '$CommandLine'.\n", MinVerbosity => 10);
  my $NrTries = 0;
	my @CurlOutput = ();
	while (!@CurlOutput && $NrTries++ < $RetryLimit) {
		Display("Trying [$NrTries/$RetryLimit].\n",MinVerbosity => 2);
		@CurlOutput = `$CommandLine`;
		# Copy output. Only in very high debug levels.  # We have it in file anyway.
    if ($Verbosity > 99) {	# The if around makes it a bit more efficient over the loop.
		  foreach my $Line (@CurlOutput) { 
		    Display($Line,MinVerbosity => 100); 
      }
    }
		open (CURL_STDERR,"$TmpCurlStderrFile.$CurlRun") || die "Could not open $TmpCurlStderrFile.$CurlRun : $!.";
		# Copy curl stderr.
		Display("\nstderr of curl :\n",MinVerbosity => 10);
		while(<CURL_STDERR>) {
			my $Line = $_;
			my $PasswordToBlank = uri_escape($Password,"^A-Za-z");
			$Line =~ s/$PasswordToBlank/YouThinkThisIsThePassword/g;
		  Display("$Line",MinVerbosity => 10);
		}
		close(CURL_STDERR);
		Display("\nEnd of stderr of curl.\n",MinVerbosity => 10);

    # Some checking on the HTTP response to see if there's no 5** Server errror or 4** Client error.
    # In general : 2** is Success, 3** is Redirection, 4** is Client Error and 5** is Server Error.

    if ($CurlOutput[0] !~ m/HTTP[^ ]+ (\d{3})/) {
      die("Irregular HTTP header '$CurlOutput[0]' received.");
    }
    my $HttpCode = $1;
    if ($HttpCode =~ m/(1|2|3)\d{2}/) {
      Display("Http Status OK : $HttpCode.\n",MinVerbosity=>2);
    } elsif ($HttpCode =~m/4\d{2}/) {
      Display("Http Client Error : $HttpCode.\n",MinVerbosity=>2);
      @CurlOutput = (); # Force retry.
    } elsif ($HttpCode =~m/5\d{2}/) {
      Display("Http Server Error : $HttpCode.\n",MinVerbosity=>2);
      @CurlOutput = (); # Force retry.
    } else {
      die("Unexpected HTTP status : '$HttpCode'.");
    }
	}

  # In debug mode (Verbosity>9) we copy the output to a file.
  if ($Verbosity > 9) {
    open (CURL_STDOUT,">$TmpCurlStdoutFile.$CurlRun") || 
		  die "Could not open $TmpCurlStdoutFile.$CurlRun : $!.";
    print CURL_STDOUT @CurlOutput;
    close(CURL_STDOUT);
  }

	if (!@CurlOutput && $NrTries > $RetryLimit) {
    Display("Curl run $CurlRun.\nCommandLine : '$CommandLine'.\n",stderr => 1);
		die("An error was encountered getting the page.");
	}

	# Redirect search in headers.
	my $Redirection = "";
  open (CURL_HEADERS,"$TmpCurlHeadersFile.$CurlRun") || die "Could not open $TmpCurlHeadersFile.$CurlRun : $!.";
	while (<CURL_HEADERS>) {
		if (m/^Location: (\S+)\s/) {
			$Redirection = $1;
      last;
		}
	}
  close(CURL_HEADERS);

	# If we have been asked to follow Location: headers
	if ($FollowForward) {
		if ($Redirection ne "") {
      if ($Redirection !~ m/^http.*/i) {
        if ($Url =~ m/(http?:\/\/[^\/]+)\//i) {
          $Redirection = $1 . $Redirection;
        }
      }
			Display("Following redirect to $Redirection.\n",MinVerbosity => 2);
			return &GetPage(Url => $Redirection,FollowForward => $FollowForward);
		}
	}

	return (join("",@CurlOutput),$Url);
}

########################################################################################################################
# 
# Do the HotMail login process - log in until we have the URL of the inbox.
#
########################################################################################################################

sub Login() {

	Display("Getting hotmail index loginpage.\n", MinVerbosity =>2);

  my ($LoginPageAsString,$GetPageUrl) = GetPage(Url => "http://mail.live.com",FollowForward => 1);

  # We expect here a number of functions now (aug 2007) to be hidden in a javascript
  # that is loaded separately. Let's load and append.
  # XXX JDLA It can turnout that after all we don't use anything of it, but reconstruct.
  # Then one can speed up by leaving this JSPageAsString out.
  
  my $BaseHref = "";
  if ($LoginPageAsString =~ m/<base\s+href=\"([^\"]+)\"/) {
    $BaseHref = $1;
    Display("Found base href to be '$BaseHref'.\n",MinVerbosity => 10);
  }

  my $JavaScriptHref = "";
  if ($LoginPageAsString =~ m/<script\s+type=\"text\/javascript\"\s+src=\"([^\"]+)\"/ ) {
    $JavaScriptHref = $1;
    Display("Found javascript href to be '$JavaScriptHref'.\n",MinVerbosity => 10);
  }
  
  die "Expected javascript href at this stage." unless $JavaScriptHref;
 
  Display("Fetching the JS href.\n",MinVerbosity => 10);
  my ($JSPageAsString,$JSGetPageUrl) = GetPage(Url => "${BaseHref}$JavaScriptHref",FollowForward => 1);

  # Append the JS stuff into our page.

  $LoginPageAsString .= $JSPageAsString;

  # We would look to :
  #
  # function FormStart(){var s="
  # <form name=\"f1\" method=\"POST\" target=\"_top\" action=\""+g_urlPost+"\" 
  # onsubmit=\"return WLSubmit(this)\">";
  # s+=WL_HiddenField("idsbho","IDSBHO","1");
  # s+=WL_HiddenField("PwdPad","i0340",null);
  # s+=WL_HiddenField("LoginOptions","LoginOptions","3");
  # s+=WL_HiddenField("CS","CS",null);
  # s+=WL_HiddenField("FedState","FedState",null);
  # s+=WL_HiddenField("PPSX","i0326",g_sRBlob);
  # s+=WL_HiddenField("type","type",null);return s;}
  # 
  # The WL_HiddenField = 'name','identifier','value'. Identifier unimportant.
  # But assume g_urlPost is always there. So action is g_urlPost --> srf_uPost
  # Assume also g_sRBlob -->srf_sRBlob


  # FormStart as in herebove described analysis.
  if ($LoginPageAsString !~ m/function FormStart\(\)\s*\{([^\}]+)\}/i) {
    die "Page doesn't contain FormStart as expected.";
  }
  my $FormStart = $1;
	Display("FormStart detected as '$FormStart'.\n", MinVerbosity => 10 );

  # Hidden fields as described above.
  my %Fields = ();
  while ($FormStart =~ m/s\+=WL_HiddenField\(\"([^\"]+)\",[^,]+,([^\)]+)\)/gc) {
    my $Key = $1;
    my $Val = $2;
    if ($Val =~ m/\"([^\"]+)\"/) {
      $Val = $1;
    }
    if ($Val eq "null") {
      $Val = "";
    }
    $Fields{$Key} = $Val;
    Display("Detected HiddenField : $Key->$Val\n", MinVerbosity => 10);
  }

REENTRY_DARREN_QUIRK:
  # Assumed g_urlPost
	if ($LoginPageAsString !~ m/var srf_uPost='(\S+)'/i) {
		die "Page doesn't contain var srf_uPost in the expected place.";
	}
  my $LoginUrl = $1;
	Display("LoginUrl detected as '$LoginUrl'.\n", MinVerbosity => 10 );

  # Transformation of LoginUrl (mimick part of the g_DO in javascript ..).
  if ($LoginUrl !~ m/(http[s]?):\/\/([^\/]+)\/(.*)/ ) {
    die "Malformed LoginUrl : '$LoginUrl'.";
  }
  my $ProtocolLoginUrl   = $1;
  my $FirstPartLoginUrl  = $2;
  my $SecondPartLoginUrl = $3;
  if ($Domain eq "msn.com") {
    $FirstPartLoginUrl = "msnia.login.live.com";
  }
  $LoginUrl = "$ProtocolLoginUrl://$FirstPartLoginUrl/$SecondPartLoginUrl";
	Display("LoginUrl transformed as '$LoginUrl'.\n", MinVerbosity => 10 );
  
  # Assumed g_sRBlob (and hence PPSX)
	if ($LoginPageAsString !~ m/var srf_sRBlob='(\S+)'/i) {
		die "Page doesn't contain var srf_sRBlob in the expected place.";
	}
	Display("PPSX detected as '$1'.\n", MinVerbosity => 10 );
  $Fields{"PPSX"} = $1;
  
  # PPFT is a normal (ie non JS) hidden input type.
  if( $LoginPageAsString !~ m/<\s*input\s+.*name=\"PPFT\"(\s+id="\S+")?\s+value=\"(\S*)\"/ ) {
    die "Page doesn't contain input field PPFT as expected.";
	}
	Display("PPFT detected : '$2'.\n",MinVerbosity => 10 );
  $Fields{"PPFT"} = $2;

  # A number of other assumption that are peeled deep out of JS.
  # I'm afraid that the need for an embedded JS interpreter is coming closer ...
  $Fields{"type"} = "11";
  $Fields{"NewUser"} = "1";
  $Fields{"i1"} = "0";
  $Fields{"i2"} = "0";
  
  # Hope the password padding still works ...
	my $Padding = "BovenGentRijstEenzaamEnGrijsHetOudBelfort";
	my $PwdPad = substr( $Padding, 0, length($Padding)-length($Password) ); 
	Display("PwdPad constructed : '$PwdPad'.\n",MinVerbosity => 10 );
  $Fields{"PwdPad"} = $PwdPad;

  #login and password.
  $Fields{"login"} =  uri_escape($Login . '@' . $Domain, "^A-Za-z");
  $Fields{"passwd"} = uri_escape($Password, "^A-Za-z");
  

  # Construct the form with above in a temporary file.
  open (FORMFILE,">$TmpFormDataFile") || die "Could not open $TmpFormDataFile : $!.";
  my $HaveAlreadyArgument = 0;
  foreach my $Key (keys %Fields) {
    if ($HaveAlreadyArgument) { print FORMFILE "\&"; }
    print FORMFILE "$Key=$Fields{$Key}";
    $HaveAlreadyArgument = 1;
  }
	close FORMFILE;
  
  # Second step of login. The form is provided as a curl --data argumetn.
	Display("Logging in.\n",MinVerbosity => 1);

	($LoginPageAsString,$GetPageUrl) = GetPage(Url => $LoginUrl,CurlDataArg => "\@$TmpFormDataFile",FollowForward => 1);
  # XXX JDLA This is old and needs to be checked. But for the moment I 
  # don't care for bad password notices.
	if ($LoginPageAsString =~ /password is incorrect/i) {
    # Bug correction : Darren Quirk !
    if ($CorrectDarrenQuirk) {
      $CorrectDarrenQuirk = 0; # Avoid looping on *really* wrong password.
      Display("Recycling for the 'Darren Quirk'.\n",MinVerbosity=>10);
      goto REENTRY_DARREN_QUIRK
    }
		die("There was an error logging in. Please check that your username and password are correct.");
	}

	if ($LoginPageAsString !~ m/window\.location\.replace\(\"(.*)\"\);/i && 
      $LoginPageAsString !~ m/<meta http-equiv=\"REFRESH\" content=\"0;\sURL=(.*)\"></i) { 
		die("Hotmail's login structure has changed! (redirloc).");
  }
	$LoginUrl = $1; 

  Display("LoginUrl 2 : '$LoginUrl'.\n",MinVerbosity => 10);
  # Following the redirect : Third step of login.
	Display("Following redirect.\n",MinVerbosity => 2);
  ($LoginPageAsString,$GetPageUrl) = GetPage(Url => $LoginUrl,FollowForward => 1);

  $LoginUrl = $GetPageUrl;

  if ($LoginUrl !~ m/(http[s]?:\/\/([^\/]+\/)+)/) {
    die "Could not detect BaseUrl.";
  } 
  $BaseUrl = $1;
  $NParameter = "";
  if ($LoginUrl =~ m/(n=\d+)/) {
    $NParameter = $1;
  }
  Display("LoginUrl    : $LoginUrl.\n",MinVerbosity => 10);
  Display("BaseUrl     : $BaseUrl.\n",MinVerbosity => 10);
  Display("NParameter  : $NParameter.\n",MinVerbosity => 10);

  # At this moment we assume we are logged in, but there should be some 'markers' to  
  # check this reasonably.

  my $LoggedIn = 0;
  if ($LoginPageAsString =~ m/href=\"ManageFoldersLight.aspx/) {
    $LoggedIn = 1;
  } elsif ($LoginPageAsString =~ m/MSNPlatform\/browsercompat.js/) {
    $LoggedIn = 1;
  }
 
  die "Could not log in. Maybe structure has changes or was not foreseen." unless $LoggedIn;
    
	Display("Got MainPage.\n",MinVerbosity => 1);
}

########################################################################################################################
#
# Search for Cookie in the CookiesFile.
# Argument : The cookie to be found.
# Returns its value.
#
########################################################################################################################

sub FindCookie($) {
  my ($CookieToFind) = @_;
  open (COOKIES,$TmpCookiesFile) || die "Could not open '$TmpCookiesFile'.";
  while (<COOKIES>) {
    chomp;
    next if m/^#/;  # Comment
    next if m/^$/;  # Empty line.
    my @SplittedLine = split /\t/;
    if ($SplittedLine[5] eq $CookieToFind) {
      close COOKIES;
      return $SplittedLine[6];
    }
  }
  close COOKIES;
  return "";
}

########################################################################################################################
# 
# Move the email message to a folder.
# MessageIdx and FolderName as argument.
#
########################################################################################################################

sub MoveToFolder($$$) {
  my ($MessageIdx,$TargetFolderName,$SourceFolderIdx) = @_;
  my $MessageId = $MessagesId[$MessageIdx];
  my $MessageAd = $MessagesAd[$MessageIdx];

  # Find out which folder (the index in @FolderIds) is meant.
  my $TargetFolderIdx   = 0;
  my $TargetFolderFound = 0; 
  while ((not $TargetFolderFound) && $TargetFolderIdx<$NrFolders) {
    if (lc $TargetFolderName eq lc $FolderNames[$TargetFolderIdx]) {
      $TargetFolderFound = 1;
    } else {
      $TargetFolderIdx++;
    }
  }

  # Let's die the hard way if we do not find that folder.
  die "Folder with name '$TargetFolderName' used in MoveToFolder could not be located." unless $TargetFolderFound;
      
	Display("Moving email message to folder '$TargetFolderName'.\n",MinVerbosity => 1);

  my $ToBox   = $FolderIds[$TargetFolderIdx];
  my $FromBox = $FolderIds[$SourceFolderIdx];
  my $MT = FindCookie("mt");
  my $Url = "${BaseUrl}$RequestHandler?cnmn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox.MoveMessages&". 
			      "a=$SessionId&".
			      "au=$AuthUser";
  my $PostData = "cn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox&".
                 "d=".uri_escape("\"$FromBox\",\"$ToBox\",[\"$MessageId\"],[{\"$MessageAd$FromBox\",null}],null,null,0,false,Date,false,true")."&".
                 "mn=MoveMessages&".
                 "mt=$MT&".
                 "v=1";

  # Do The move ...
	my ($EmailPageAsString,$GetPageUrl) = GetPage(Url => $Url,CurlDataArg => $PostData); 
}

########################################################################################################################
# 
# Delete the message.
# MessageIdx as argument.
#
########################################################################################################################

sub DeleteMessage($$) {
  my ($MessageIdx,$SourceFolderIdx) = @_;
  my $MessageId = $MessagesId[$MessageIdx];
  my $MessageAd = $MessagesAd[$MessageIdx];

	Display("Deleting email message.\n",MinVerbosity => 1);

  my $FromBox = $FolderIds[$SourceFolderIdx];

  my $MT = FindCookie("mt");
  my $Url = "${BaseUrl}$RequestHandler?cnmn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox.MoveMessages&". 
			      "a=$SessionId&".
			      "au=$AuthUser";
  my $PostData = "cn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox&".
                 "d=".uri_escape("\"$FromBox\",\"$TrashFolderId\",[\"$MessageId\"],[{\"$MessageAd$FromBox\",null}],null,null,0,false,Date,false,true")."&".
                 "mn=MoveMessages&".
                 "mt=$MT&".
                 "v=1";

  # Do The Delete ...
	my ($EmailPageAsString,$GetPageUrl) = GetPage(Url => $Url,CurlDataArg => $PostData); 
}

########################################################################################################################
# 
# Mark the email message as read
# MessageIdx as argument.
#
########################################################################################################################

sub MarkRead($) {
  my ($MessageIdx) = @_;
  my $MessageId = $MessagesId[$MessageIdx];

	Display("Marking email message as read.\n",MinVerbosity => 1);

  my $Url = "${BaseUrl}$RequestHandler?cnmn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox.MarkMessages&".
			      "a=$SessionId&".
			      "au=$AuthUser";
  my $MT = FindCookie("mt");
  my $PostData = "cn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox&".
                 "d=true%2C%5B%22$MessageId%22%5D&".
                 "mn=MarkMessages&".
			           "mt=$MT&".
                 "v=1";
  
  # Mark as Read ...
  my ($EmailPageAsString,$GetPageUrl) = GetPage(Url => $Url,CurlDataArg => $PostData); 
}

########################################################################################################################
# 
# Return the email message (mbox format) as one big string.
# MessageIdx and FolderName as argument.
#
########################################################################################################################

sub GetEmail($$) {
  my ($MessageIdx,$FolderName) = @_;
  my $MessageId = $MessagesId[$MessageIdx];

	Display("Getting email message.\n",MinVerbosity => 1);

  my $Url = "${BaseUrl}GetMessageSource.aspx?msgid=$MessageId";
	my ($EmailPageAsString,$GetPageUrl) = GetPage(Url => $Url,FollowForward => 1);

  $EmailPageAsString =~ s/^[\s\n]*//; 
  $EmailPageAsString = HtmlUnescape($EmailPageAsString); # Strips all HTML artifacts from the message body.
  $EmailPageAsString =~ s/\r\n/\n/gs; # Force unix line endings.

  if ($AllowUncompletePreTag == 0) {
	  if ($EmailPageAsString !~ /<pre>[\s\n]*(.*?)<\/pre>/si) {
		  die "Unable to download email message.";
	  }
    $EmailPageAsString = $1;
  } else {
	  if ($EmailPageAsString !~ /<pre>[\s\n]*(.*?)<[^<]+$/si) {
		  die "Unable to download email message.";
	  }
    $EmailPageAsString = $1;
  }

  # Fallback envelope sender and date, case it would not be in the message.
	my $FromAddress = "$Login\@$Domain";
	my $FromDate    = scalar gmtime;

	# Strip "From whoever" when found on the first line- the format is wrong for mbox files anyway.
	if ($EmailPageAsString =~ s/^From ([^ ]*) [^\n]*\n//s) { 
    $FromAddress = $1; 
  } elsif ($EmailPageAsString =~ m/^From:[^<]*<([^>]*)>/m) { 
    $FromAddress = $1;  
  }

	# Apply >From quoting
	$EmailPageAsString =~ s/^From ([^\n]*)\n/>From $1/gm;

	# If an mboxheader was desired, make up one
	if ($EmailPageAsString =~ m/^\t (\w+), (\d+) (\w+) (\d+) (\d+):(\d+):(\d+) ([+-]?.+)/m) {
		my $DayOfWeek = $1;
		my $Month     = $3;
		my $Day       = $2;
		my $Hour      = $5;
		my $Minute    = $6;
		my $Second    = $7;
		my $Year      = $4;
		my $TimeZone  = $8;

		# Put date in mboxheader in UTC time
		$Hour -= $TimeZone;
		while ($Hour < 0)  { $Hour += 24; }
		while ($Hour > 23) { $Hour -= 24; }

		$FromDate = sprintf ("%s %s %02d %02d:%02d:%02d %d",$DayOfWeek,$Month,$Day,$Hour,$Minute,$Second,$Year);
	}

	# Add an mbox-compatible header
  # And add some identifying headers.
	$EmailPageAsString =~ s/^/From $FromAddress $FromDate\nX-$ProgramName-Version: $Revision\nX-$ProgramName-Folder: $FolderName\nX-$ProgramName-User: $Login\n/;

	return $EmailPageAsString;
}

########################################################################################################################
# 
# Get the messages from the folder with Idx as argument.
# 
########################################################################################################################

sub GetMessagesFromFolder($) {
  my ($FolderIdx)        = @_;
  my $FolderName         = $FolderNames[$FolderIdx];
  my $FolderId           = $FolderIds[$FolderIdx];
  my $ReportedNrMessages = $FolderNrMessages[$FolderIdx];

	Display("Loading folder '$FolderName'.\n",MinVerbosity => 1);

	my $Page          = 0;
	my $StillPageToGo = 1;

  my $pnAm          = "";
  my $pnAd          = "";

	my $PageAsString;
  my $GetPageUrl;

  # Reinitialize the global variable back to 0.
  $NrMessagesDetected = 0;
  $NrMessagesUnread   = 0;

	while ($StillPageToGo) {
		$StillPageToGo = 0;
		$Page++;

    Display("Handling page $Page.\n",MinVerbosity => 2);

    my $MT  = FindCookie("mt");
    if ($RequestHandler) {
      my $Url = "${BaseUrl}$RequestHandler?cnmn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox.GetInboxData&". 
	    		      "a=$SessionId&".
	    		      "au=$AuthUser&".
                "ptid=0";
      my $PostData = "";
      if ($Page == 1) {
        $PostData = "cn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox".
                    "&".
                    "mn=GetInboxData".
                    "&".
                    "d=true,true,{".uri_escape("\"$FolderId\"").",25,0,0,Date,false,null,null,".
                      "1,1,false,null,false,-1},false,null".
                    "&".
                    "v=1".
                    "&".
                    "mt=$MT";
      } else {
        $PostData = "cn=Microsoft.Msn.Hotmail.Ui.Fpp.MailBox".
                    "&".
                    "mn=GetInboxData".
                    "&".
                    "d=true,true,{".uri_escape("\"$FolderId\"").",25,NextPage,0,Date,false,".
                      uri_escape("\"$pnAm\"") . "," .
                      uri_escape("\"$pnAd\"") . "," .
                      "$Page,2,false,null,false,$ReportedNrMessages},false,null".
                    "&".
                    "v=1".
                    "&".
                    "mt=$MT";
      }

	    ($PageAsString,$GetPageUrl) = GetPage(Url => $Url,CurlDataArg => $PostData); 

      # XXX JDLA ???
      # For God knows which reason all of the " are now suddenly \" in the html output ...
      # Well in fact it is no html output, it is one big argument of a javascript. Let's do some substitutions to help.
      $PageAsString =~ s/\\\"/\"/g;
      $PageAsString =~ s/\\\"/\"/g;
      $PageAsString =~ s/\\r/\r/g;
      $PageAsString =~ s/\\n/\n/g;
    } else {
      # First time : not  yet 'RequestHandler'
      Display("Getting session id, request handler, and other data necessary for requests.\n", MinVerbosity =>2);
      my $PageUrl = $BaseUrl.$FolderHrefs[$FolderIdx];
      ($PageAsString,$GetPageUrl) = GetPage(Url => $PageUrl,FollowForward => 1);
  
      # get the ID for the trash folder
      $TrashFolderId = $PageAsString;
      $TrashFolderId =~ m/sysFldrs\s*?:\s*?{\s*?trashFid\s*?:\s*?\"(.*?)\".*?}/si;
      $TrashFolderId = $1;
  
      # get the session variables as well as the request handler
      $PageAsString =~ m/fppCfg\s*?:\s*?{\s*?RequestHandler\s*?:\s*?\"(.*?)\".*?SessionId\s*?:\s*?\"(.*?)\".*?AuthUser\s*?:\s*?\"(.*?)\".*?}/si;
      $RequestHandler = $1;
      $SessionId = $2;
      $AuthUser = $3;
      die "Could not find RequestHandler." unless $RequestHandler;
    }

    # To start with we limit us to a MessagesArea between
    # <table class="dItemListContentTable"..>  ... </table..>
    if ($PageAsString !~ m/<table class=\"d?ItemListContentTable[^>]*>(.*?)<\/table/si) {
      die "Could not correctly parse the messages table.";
    }
    my $MessagesArea = $1;

    # In this message area there's the body of the table containing messages.
    # <tbody ..> ... </tbody..>
    if ($MessagesArea !~ m/<tbody\s*.*?>(.*?)<\/tbody\s*>/si) {
      die "Could not correctly parse the messages table.";
    }
    $MessagesArea = $1;

    # MessagesArea now contains the body of the messages table.
    # Table rows <tr ..> .. : Description of the messages.
    # The class=".." tag hints on Unread or not.
    while ($MessagesArea =~ m/<tr(.*?)>/si) {
      $MessagesArea = $';
      my $RowAttributes = $1;
      
      if ($1 =~ m/ContentItemUnread/i) {
        $MessagesRead[$NrMessagesDetected] = 0;
        $NrMessagesUnread++;
      } else {
        $MessagesRead[$NrMessagesDetected] = 1;
      }
      
      $RowAttributes =~ m/id=\"(.*?)\".*?mad=\"(.*?)\"/si;
      my $MessageId = $1;
      my $MessageAd = $2;
      
      # Goto 5th column.(to get the from)
      my $TdLine = "";
      for (my $Idx=0;$Idx<4;$Idx++) {
        $MessagesArea =~ m/<td(.*?)>(.*?)<\/td\s*>/i;
        $MessagesArea = $';
        $TdLine = $2;
      }
      if ($TdLine !~ m/class=\"(truncate)?from\">(<[^>]+>)?(.*?)<[^>]+>/si) {
        die "Parse error for 'from'.";
      } 
      my $From = HtmlUnescape($3);
      Display("From '$From'.\n",MinVerbosity => 10);
      $MessagesFrom[$NrMessagesDetected] = $From;

      # Further to the subject column. There we pick up also the href of the message.
      $MessagesArea =~ m/<td(.*?)>(.*?)<\/td\s*>/i;
      $MessagesArea = $';
      $TdLine = $2;
      if ($TdLine !~ m/<a href=\"(.*?)\"\s*>(.*?)<\/a>/si) {
        die "Parse error for 'subject'.";
      }
      my $Subject = HtmlUnescape($2);
      Display("Subject   '$Subject'.\n",MinVerbosity => 10);
      Display("MessageId '$MessageId'.\n",MinVerbosity => 10);
      Display("MessageAd '$MessageAd'.\n",MinVerbosity => 10);
      Display("Read      '$MessagesRead[$NrMessagesDetected]'.\n",MinVerbosity => 10);

      $MessagesSubject[$NrMessagesDetected] = $Subject;
      $MessagesId[$NrMessagesDetected] = $MessageId;
      $MessagesAd[$NrMessagesDetected] = $MessageAd;

      $NrMessagesDetected++;
    }

    Display("Total messages reported : $ReportedNrMessages.\n" .
            "Nr messages detected    : $NrMessagesDetected.\n" ,
             MinVerbosity => 10);

    # If the number of messages we detected already is smaller than the
    # reported total , we still have to look for another page and reloop.
    if ($NrMessagesDetected < $ReportedNrMessages) {
      $StillPageToGo = 1;
      Display("Search for one more page.\n",MinVerbosity => 10);
      # Search for 'next page' href 
      my $NextPageAd = "";
      if ($PageAsString =~ 
          m/<li([^>]*)>\s*<a href=\"([^\"]+)\"[^>]*><img src=\"[^\"]*\" class=\"i_nextpage\".*?><\/a>/si) {
        $NextPageAd = $1;
      }
      die "Could not find an expected next page href. Probably page structure changed." unless $NextPageAd;
     
      if ($NextPageAd !~ m /pnAm=\"([^\"]*)/) {
        die "Could not find pnAm in '$NextPageAd'.";
      }
      $pnAm = $1;

      if ($NextPageAd !~ m /pnAd=\"([^\"]*)/) {
        die "Could not find pnAd in '$NextPageAd'.";
      }
      $pnAd = HtmlUnescape($1);
      $pnAd =~ s/\:/\\\:/g;  # XXX JDLA seems necessary ...

      Display("Next page Ad : '$pnAd'.\n",MinVerbosity => 10);
      Display("Next page Am : '$pnAm'.\n",MinVerbosity => 10);
    }
	}
}

########################################################################################################################
# 
# Process the messages retrieved from a folder.
# Acts on global variables @Messages ...
# It just takes FolderIdx for knowing the name. (and now also for the MoveToFolder/Delete command)
# 
########################################################################################################################

sub ProcessMessagesFromFolder ($) {
  my ($FolderIdx) = @_;
  my $FolderName = $FolderNames[$FolderIdx];
  # Now let's run through all detected messages ..
  my $MessageIdx;
  for ($MessageIdx=0;$MessageIdx<$NrMessagesDetected;$MessageIdx++) {
    if ($DownloadedIdsFile) {
      # First we check and or create the file with the downloaded Ids.
      if (not -e $DownloadedIdsFile) {
        open (DOWNLOADED,">$DownloadedIdsFile") || die "Could not open $DownloadedIdsFile : $!.";
        print DOWNLOADED "-- This is an automatically generated file by $0 containing the id of downloaded messages\n";
        close (DOWNLOADED);
      }
     
      # Run through the downloaded Ids to check if we still have to download.
      my $HaveMessageAlready = 0;  
      open (DOWNLOADED,"$DownloadedIdsFile") || die "Could not open $DownloadedIdsFile : $!.";
      while(my $TmpId = <DOWNLOADED>) {
        chomp ($TmpId);
        if (uc($MessagesId[$MessageIdx]) eq uc($TmpId)) {
          $HaveMessageAlready = 1;
          Display("The message $MessageIdx with id '$TmpId' is already downloaded.\n",MinVerbosity => 10);
          last;
        }
      }
      close (DOWNLOADED);
  
      # All with this message if we downloaded already.
      next if ($HaveMessageAlready);
    }

    next if ( ($FetchOnlyUnread =~ m/Yes/i) && ($MessagesRead[$MessageIdx] == 1) );

    # Identifying a bit the message for the log.
    Display("Handling mail\n".
            "  from    : '$MessagesFrom[$MessageIdx]'\n".
            "  subject : '$MessagesSubject[$MessageIdx]'\n",MinVerbosity => 1);

    # JDLA getEmail , provided that HaveMessageAlready was not set.
    my $Message = GetEmail($MessageIdx,$FolderName);

    # Pipe it through a processor such as procmail.
    Display("Sending mail to '$MailProcessor'.\n",MinVerbosity => 1);
		open PR,"|$MailProcessor";
		print PR $Message;
		close PR || die "Sending mail to '$MailProcessor' did not succeed. See error log.";

    if ($DownloadedIdsFile) {
      # We don't have it yet. Add it to the downloaded.
      open (DOWNLOADED,">>$DownloadedIdsFile") || die "Could not open $DownloadedIdsFile : $!.";
      print DOWNLOADED "$MessagesId[$MessageIdx]\n";
      close (DOWNLOADED);
    }

    # And maybe we have to mark it read too ?
    if ($MarkRead =~ m/^Yes$/i) {
      MarkRead($MessageIdx);
    }
 
    # Maybe we even have to move it !
    if ($MoveToFolder ne "") {

      # If MoveToFolder is of the format @FileName, get the folder name from that FileName.
      if ($MoveToFolder =~ m/^@(.*)$/) {
        my $MoveToFolderName = $1;
        open(IN,$MoveToFolderName) || die "Could not open '$MoveToFolderName' : $!";
        $MoveToFolder = <IN>;
        chomp $MoveToFolder;
        close(IN);
      }

      # Do the move.
      MoveToFolder($MessageIdx,$MoveToFolder,$FolderIdx);
    }
   
    # Or maybe we have to remove it.
    if ($Delete =~ m/^Yes$/i) {
      DeleteMessage($MessageIdx,$FolderIdx);
    }

		Display("Done.\n",MinVerbosity => 1);
	}
}

########################################################################################################################
# 
# Get a list of the folders we have to deal with and parse them one by one.
# 
########################################################################################################################

sub GetFolders() {
  my ($FolderPageAsString,$GetPageUrl) = GetPage(Url => "${BaseUrl}ManageFoldersLight.aspx?$NParameter",
                                                 FollowForward => 1);
  if ($FolderPageAsString =~ m/Internal Server Error/i) {
    die "Internal Server Error reported. Page structure might have changed.";
  }
  # Scan the line for all folders, their href and title.
  # NrFolders on the fly;
  while ($FolderPageAsString =~ 
         m/<td class=\"d?ManageFoldersFolderNameCol\"><a.*?\s*href=\"([^\"]*)\"\s*>(.*?)<\/a>\s*<\/td>\s*<td class=\"d?ManageFoldersTotalCountCol[^\"]*\">(\d+)<\/td>/gc) { 
    $FolderHrefs[$NrFolders]      = $1;
    $FolderNames[$NrFolders]      = HtmlUnescape($2);
    $FolderNrMessages[$NrFolders] = $3;
    if ( $FolderHrefs[$NrFolders] !~ m/FolderID=([^&]*)/ ) {
       die "Could not detect FolderId.";
    }
    $FolderIds[$NrFolders] = $1;

    Display(
     "Folder $NrFolders - $FolderIds[$NrFolders] - $FolderNames[$NrFolders] - $FolderNrMessages[$NrFolders].\n", 
      MinVerbosity => 10);
    $NrFolders++;
  }
  die "No folders detected. Likely the page structure has changed." unless $NrFolders;
}

########################################################################################################################
# 
# The 'main' program.
#
########################################################################################################################

# Don't allow others to read our temp files
umask(077);
# The temporary directory creation.
mkdir($TmpDir) || die "Could not create $TmpDir : $!.";

ParseArgs();
ParseConfig();
DisplayIntroText();
Login();
GetFolders();

for (my $FolderIdx=0;$FolderIdx<$NrFolders;$FolderIdx++) {
  next if (scalar keys %FoldersToProcess && not exists $FoldersToProcess{lc $FolderNames[$FolderIdx]});
  Display("\nProcessing folder $FolderNames[$FolderIdx].\n",MinVerbosity => 1);
  GetMessagesFromFolder($FolderIdx);
  Display("$NrMessagesDetected/$NrMessagesUnread Messages/Unread.\n",MinVerbosity => 1);
  ProcessMessagesFromFolder($FolderIdx);  # Takes no arguments, works on globals. FolderIdx just for name calculation.
}
Display("All done.\n",MinVerbosity => 1);
CleanTempFiles();

exit(0);

########################################################################################################################
# 
# $Log: GetLive.pl,v $
# Revision 1.43  2008/07/05 19:55:41  jdla
# Bug 1962937 : Could not correctly parse the messages table
#    (after MS started changing things again around 1/7/2008)
#
# Revision 1.42  2008/03/11 19:32:11  jdla
# Corrected stupidity (even did not compile) on previous change.
#
# Revision 1.41  2008/03/07 22:23:01  jdla
# Bug 1909801 : Locale does not work in Windows.
#
# Revision 1.40  2008/02/02 17:43:30  jdla
# Bug 1881842 : Does not handle folder names containing non-ASCII characters
#
# Revision 1.39  2008/01/19 18:44:32  jdla
# Bug 1875392 : Login on msn.com does fail !
#
# Revision 1.38  2008/01/19 12:30:55  jdla
# Bug 1871076 : GetLive died with Unexpected HTTP status : '100'
#
# Revision 1.37  2007/12/02 14:38:46  jdla
# *) Feature 1778902 : deletewhenread=yes option
#
# Revision 1.36  2007/12/02 11:15:40  jdla
# *) Feature 1792688 : Option to get a count of unread messages only
#
# Revision 1.35  2007/12/02 09:52:55  jdla
# *) Bug 1796107 : HTTP/500 etc should be catched.
#
# Revision 1.34  2007/11/11 19:46:31  jdla
# Merged in erroneously created branch 1.33.2.1.
#
# Revision 1.33.2.1  2007/11/11 19:39:05  jdla
# *) Bug 830063 : Doesn't work anymore on some accounts.
#
# Revision 1.33  2007/09/08 18:21:28  jdla
#
# *) Bug 1784876 : Command line parsing error.
# *) Bug 1789899 : Unable to Download.
#
# Revision 1.32  2007/09/04 21:14:39  jdla
# [ 1784876 ] bug in command line argument parser
#
# Revision 1.31  2007/08/24 17:16:54  jdla
# *) Bug 1780285 : MARK READ
#
# Revision 1.30  2007/08/23 21:40:07  jdla
# *) Bug 1779371 : Manageforlderslight error
#
# Revision 1.29  2007/08/22 21:28:01  jdla
# *) Bug 1779788 : Some Accounts do not work.
#
# Revision 1.28  2007/08/21 21:22:01  jdla
#
# *) Revamping to catch up with MS changing the login to live login.
#    From now on only supports 'Live' boxes.
#    Please convert old ones. It's lossless.
#
# Revision 1.27  2007/08/18 07:52:43  jdla
#
# *) Bug 1774546 (second part, because in fact two unrelated bugs
#    were entered into the same) :
#    Live or dead: Could not find expected url
#    (After change of interface by MS)
#
# Revision 1.26  2007/08/16 12:56:36  jdla
#
# *) Bug 1774546 : Live or dead: Could not find expected url.
#    (After change of interface by MS)
#
# Revision 1.25  2007/08/04 19:49:16  jdla
# *) Changed Curl quoting to support Windows (thx to 'gharkink').
# *) Adapted SmtpForward.pl (also thx to 'gharkink').
# *) Added alternate SmtpAuthForward.pl (thx to 'runemaagensen').
# *) Update manual with above (and the info on working versions)
#
# Revision 1.24  2007/07/29 14:35:38  jdla
#
# *) Bug 1763128 : msn.com problems : See submitted patch 1758859
# *) Inclusion of sample SmtpForward.pl in the distribution.
#
# Revision 1.23  2007/06/24 17:37:41  jdla
#
# *) Bug 1742447 : Could not find expected url.
#    (After change of interface by MS)
# *) Bug 1742493 : GetLive doesn't die on wrong 'MailProcessor'.
#
# Revision 1.22  2007/06/19 20:30:26  jdla
# *) Bug 1739263 : --verbosity 0 should be silent.
# *) Request 1724728 : only fetch unread messages w/o id file
#
# Revision 1.21  2007/05/24 19:13:03  jdla
#
# *) Bug 1722346 : MoveToFolder : sometimes read , sometimes not read.
#
# Revision 1.20  2007/05/23 22:02:18  jdla
# *) Bug 1722346 : MoveToFolder : sometimes read, sometimes not read.
#    Now for sure (and thanks to a tool Live Http Headers in Mozilla)
#    solved decently for the Live branch.
#
# Revision 1.19  2007/05/22 19:49:07  jdla
# *) Bug 1722346 : MoveToFolder : sometimes read , sometimes not read.
#    Solved (I think ...) for the 'Dead' (old gotmail) mode.
#
# Revision 1.18  2007/05/20 18:53:47  jdla
# *) MoveToFolder now possible on downloading.
#
# Revision 1.17  2007/05/20 12:45:34  jdla
# Merged in the 1.16.2.1 that was by mistake done on a the release branch.
#
# Revision 1.16.2.1  2007/05/20 12:39:34  jdla
# *) MarkRead is now possible on downloading.
#
# Revision 1.16  2007/05/18 17:22:43  jdla
# *) Request 1721287 : Folder selection
#
# Revision 1.15  2007/05/18 14:59:21  jdla
# *) Bug 1719819 : Improve error message if Downloaded not specified.
#
# Revision 1.14  2007/05/18 14:10:40  jdla
# *) After the problem of Alex [dahaas] in which gotmail (the predecessor
#    of GetLive) was not able to correctly load his account, an overhaul
#    was made for correcting the counting of the messages per folder and
#    for detection of the correct NextPage url (page=n&wo=...) in his case.
#    Confirmed working for him and no regression for me.
#
# Revision 1.13.2.7  2007/05/18 12:19:12  jdla
# Overhaul in the detection of the number of messages per folder.
# Now as per suggesion of Alex [Dahaas] (dahaas@hotmail.com) based
# on the 'Manage Folders' function that is in Hotmail (the old and
# the live one).
#
# Revision 1.13.2.6  2007/05/18 08:10:43  jdla
# Further moving around of debug output for Alex' problem.
#
# Revision 1.13.2.5  2007/05/18 07:37:18  jdla
# Additional debug output ...
#
# Revision 1.13.2.4  2007/05/16 15:04:22  jdla
# Removed wrong debugoutput in a non-matched branch of if statement.
#
# Revision 1.13.2.3  2007/05/16 14:06:45  jdla
# Some additional debug info and some further adaptation of the pattern
# matching for NrMessages detection.
#
# Revision 1.13.2.2  2007/05/16 13:12:57  jdla
# Some improved debug info.
# NextPage search : corrected non-greedy search to negated class search.
#
# Revision 1.13.2.1  2007/05/15 21:03:56  jdla
# Try solving a stubborn problem coming from gotmail :
#   [ 1714743 ] Gotmail fails to download if box contains >100 messages
#
# Revision 1.13  2007/05/14 17:29:31  jdla
# *) Support 1717590 : error message => Classic named Dead now.
#
# Revision 1.12  2007/05/12 09:47:55  jdla
# *) Support 1717590 : error message => Improved error message.
#
# Revision 1.11  2007/05/07 18:27:52  jdla
# *) Bug 1714417 : execution fails if the config file name contains a dot
#
# Revision 1.10  2007/05/05 11:48:42  jdla
#
# *) Bug 1713304 : Strange characters in 'Processing folder Verwijderd'.
#
# Revision 1.9  2007/05/04 19:00:57  jdla
#
# *) Bug 1712959 : GetLive chokes on hotmail folders with 'Concepts' in it.
# *) Bug 1712958 : File with Ids incompatible between gotmail and GetLive.
#
# Revision 1.8  2007/05/02 22:01:23  jdla
# Comparison of downloaded Ids on a case independent way.
#
# Revision 1.7  2007/04/22 15:32:10  jdla
# Changed some MinVerbosity settings.
# Spelling error corrected.
# Reported Messages calculation only on first page !
#
# Revision 1.6  2007/04/22 10:17:19  jdla
# Corrected problem with fetching unread mail on Classic.
# (due to line colour attribute).
# Corrected problem of one page mailboxes by assuming that the
# number of reported messages equals the number of found messages.
# (but still warning on the situation to crosscheck)
#
# Revision 1.5  2007/04/20 22:24:28  jdla
# Added Name keyword for release tracking.
#
# Revision 1.4  2007/04/19 20:40:30  jdla
# Correction of the DisplayIntroText.
#
# Revision 1.3  2007/04/19 19:04:04  jdla
# Added case insensitivity to Mode check.
# Removed a MainPageAsString redefinition that screwed up Live mailbox
# fetch.
# Added initialization of NrMessagesDetected to the
# GetMessagesFromFolderLive.
#
# Revision 1.2  2007/04/18 21:49:22  jdla
# Bug correction : Initialization of NrMessagesDetected before each
# folder.
#
# Revision 1.1.1.1  2007/04/18 18:58:10  jdla
# Initial version of GetLive
#
# 
########################################################################################################################

# vim:et:sw=2:ts=2:filetype=perl:columns=120:lines=50:
