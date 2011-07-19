#!/bin/sh
# Test for Perl existence and version
PERLPATH=`which perl 2>/dev/null`
if [ $? = "127" ]; then
        echo "'which' utility is not installed. Installation aborted."
        exit 1
fi

if [ -z "$PERLPATH" ]; then
	echo "Perl is not installed. Installation aborted."
	exit 1
fi

if ! perl -e 'require 5.0' 2>/dev/null; then
	echo "You should install Perl 5.0 or greater. Installation aborted."
	exit 1
fi

# Directory for install
tempdir="/tmp/klinstall.$$"
if [ ! -d ${tempdir} ] ; then
	if mkdir ${tempdir} ; then
		true
	else
		exit 1
	fi
fi		
cd ${tempdir}


# drop preinstall.pl
cat > preinstall.pl <<BND1234567890ABCDEFGH_preinstall.pl_EOF
#!/usr/bin/perl -w
#
# Installation script for Kaspersky Lab products for Unix (R)
# Copyright (C) Kaspersky Lab 2003

# Really useful when script is run from the relative path
BEGIN { unshift @INC, \$1 if \$0 =~ /(.*)[\\/]/; }

use strict;
use klinstall;
use appdata;

############################## text strings ##################################
my \$INSTALL_MSG_1 = <<INSTALL_MSG_1_END;
%1 cannot be installed on this system: %2.
INSTALL_MSG_1_END

my \$INSTALL_MSG_2 = <<INSTALL_MSG_2_END;
%1 was not tested under this Linux distribution.
INSTALL_MSG_2_END

my \$INSTALL_MSG_3 = <<INSTALL_MSG_3_END;
%1 was not tested under this version of %2.
INSTALL_MSG_3_END

my \$INSTALL_MSG_4 = <<INSTALL_MSG_4_END;
%1 version %2 is already installed.
INSTALL_MSG_4_END

my \$INSTALL_MSG_5 = <<INSTALL_MSG_5_END;
%1 is not compatible with already installed %2 because of %3.

You should uninstall %2 before installing %1.
INSTALL_MSG_5_END

my \$INSTALL_MSG_6 = <<INSTALL_MSG_6_END;
%1 requires %2 version %3 or greater to work.
INSTALL_MSG_6_END

my \$INSTALL_MSG_7 = <<INSTALL_MSG_7_END;
%1 needs %2 to perform %3.
INSTALL_MSG_7_END

my \$INSTALL_MSG_9 = <<INSTALL_MSG_9_END;
License file (a file with .key extension) is your personal license key. You need to install it to use the application. 
To install it right now, just enter the path to the location of your license file (enter an empty string to continue without key file installation):
INSTALL_MSG_9_END

my \$INSTALL_MSG_10 = <<INSTALL_MSG_10_END;

Please enter another path to the location of your license file (enter an empty string to continue without key file installation):
INSTALL_MSG_10_END

my \$INSTALL_MSG_12 = <<INSTALL_MSG_12_END;
You must install a valid Kaspersky Lab license file to use the application.
To do this, run <B>%1</B>.
INSTALL_MSG_12_END

my \$INSTALL_MSG_13 = <<INSTALL_MSG_13_END;
Latest anti-virus bases are an essential part of your anti-virus protection. Do you want to download the latest anti-virus bases right now to insure your application is up to date? (If you answer 'yes', make sure you are connected to the Internet):
INSTALL_MSG_13_END

my \$INSTALL_MSG_14 = <<INSTALL_MSG_14_END;
Latest anti-virus bases are an essential part of your anti-virus protection. You must run <B>%1</B> to download the latest anti-virus bases before using the application.
INSTALL_MSG_14_END

my \$INSTALL_MSG_15 = <<INSTALL_MSG_15_END;
Latest anti-virus bases are an essential part of your anti-virus protection, and without them the application will not function. You must run %1 to download the latest anti-virus bases before using the application, and then run <B>%2</B> configuration script to set up the %3.
INSTALL_MSG_15_END

my \$INSTALL_MSG_16 = <<INSTALL_MSG_16_END;

%1 is installed.
Configuration file was installed in %2
Binaries were installed in %3

INSTALL_MSG_16_END

my \$INSTALL_MSG_17 = <<INSTALL_MSG_17_END;
If you use an http proxy server to access the Internet, you need to tell the %1 KeepUp2Date component about it. Please enter the address of your http proxy server in one of the following forms, http://proxyIP:port or http://user:pass\\@proxyIP:port. If you don\\'t have or need a proxy server to access the Internet, enter 'no' here
INSTALL_MSG_17_END

my \$INSTALL_MSG_18 = <<INSTALL_MSG_18_END;
%1 has been installed successfully but needs to be properly configured before using. 
Unfortunately, RPM is not able to run scripts interactively, so please run <B>%2</B> script by yourself to configure it.
INSTALL_MSG_18_END

my \$WEBMIN_MSG_1 = <<WEBMIN_MSG_1_END;

Default Webmin configuration file was not found. This means that either Webmin is not installed at all, or is installed into a non-default location.

Webmin (www.webmin.com) is a web-based interface for system administration for various Unix components. If you install it, you'll be able to configure and use Kaspersky Anti-Virus through the web interface. If you want to use this functionality, but haven't installed Webmin yet, you can skip this stage and install this module later using Webmin\\'s built-in installation procedure.
If you have Webmin installed in a non-default path, please enter the path to the location of the Webmin configuration file, or leave blank to skip?
WEBMIN_MSG_1_END

my \$WEBMIN_MSG_2 = <<WEBMIN_MSG_2_END;
If you want to use this module later, you can install it using Webmin\\'s own installation procedure. The module will be placed in %1.
WEBMIN_MSG_2_END

my \$WEBMIN_MSG_3 = <<WEBMIN_MSG_3_END;
Webmin configuration file %1 is incorrect. 
Please enter another path to the Webmin configuration file, or leave blank to skip?
WEBMIN_MSG_3_END

my \$WEBMIN_MSG_4 = <<WEBMIN_MSG_4_END;
Webmin module installed successfully. Check it on 'Others' web page.
Depending on Webmin configuration, you may also need to enable the Kaspersky Anti-Virus Webmin module. To do this, on the Webmin main page go to Webmin -> Webmin users -> <username> -> set the check box on <B>%1</B>.
WEBMIN_MSG_4_END

my \$WEBMIN_MSG_5 = <<WEBMIN_MSG_5_END;
failed to install Webmin module: %1
WEBMIN_MSG_5_END


############################### handlers ####################################
sub sigint_handler
{
	print STDERR "Interrupted.\\n";
	exit 0;
}


############################### routines ####################################
sub setup_webmin_module
{
	my (%APPINFO) = @_;

	my \$webminroot;
	my \$webmincfg = -f "/usr/local/etc/webmin/miniserv.conf" ? "/usr/local/etc/webmin/miniserv.conf" : "/etc/webmin/miniserv.conf";
	my \$webmindircfg = klinstall::internal_dirname (\$webmincfg);

	while ( !-f \$webmincfg )
	{
		\$webmincfg = klinstall::ask_question ("CONFIGURE_WEBMIN_ASKCFGPATH", "", \$WEBMIN_MSG_1);

		if ( !\$webmincfg )
		{
			klinstall::output (\$WEBMIN_MSG_2, \$APPINFO{"COMP_WEBMIN_MODULE"});
			return;
		}
	}

	while ( 1 )
	{
		if ( open FI, "<\$webmincfg" )
		{
			my @roots = grep (/^root=.*/, <FI>);
			close FI;

			\$webminroot = \$1 if @roots && \$roots[0] && \$roots[0] =~ /^root=(.*)\\s*\$/;
		}

		last if \$webminroot;
		\$webmincfg = klinstall::ask_question ("CONFIGURE_WEBMIN_ASKANOTHERCFGPATH", "", \$WEBMIN_MSG_3, \$webmincfg);
	}

	my (\$rc, \$outmsg) = klinstall::exec_cmd ("perl \$webminroot/install-module.pl \$APPINFO{COMP_WEBMIN_MODULE} \$webmindircfg");

	if ( \$rc == 0 )
	{
		klinstall::output (\$WEBMIN_MSG_4, \$APPINFO{"NAME"});
		return 1;
	}
	
	klinstall::warning (\$WEBMIN_MSG_5, \$outmsg);
	return;
}



############################### main() ######################################

	my %cmdline_selected;
	my %cmdline_options_list = 
			("debug" => "Enable debug mode",
			 "force" => "Force installation even if there's a conflict",
			 "upgrade" => "Perform upgrade",
			 "help"  => "This help message");

	# handle Ctrl-C
	\$SIG{'INT'} = "sigint_handler";

	# check for root
	klinstall::check_for_root ("installation");

	# Analyze extra command-line options. 1st arg could be 'used logic'
	my (\$logic, \$pkgmgr) = klinstall::get_work_logic(\$0, \\@ARGV);

	# parse command-line options and display help only if run manually
		my @argslist = @ARGV;
		push @argslist, split ('\\s+', \$ENV{"KLINSTALL"}) if \$ENV{"KLINSTALL"};

		foreach (@argslist)
		{
			next if !\$_;

			my \$arg = \$_;
			\$arg = \$1 if \$arg =~ /^--(.*)\$/;

			if ( !\$cmdline_options_list{\$arg} )
			{
				print "Unsupported command line option: \$_\\n\\n";
				display_cmdline_options(%cmdline_options_list);
				exit 1;
			}

			\$cmdline_selected{\$arg} = 1;
		}

	if ( !\$logic )
	{
		# --help
		if ( \$cmdline_selected{"help"} )
		{
			display_cmdline_options();
			exit 1;
		}
	}

	# pre-install logic
	if ( !\$logic || \$logic eq "pre" )
	{
		# load appdata module
		my %APPINFO = appdata::get_application_info();

		# check for allowed arch/OS/version
		my (\$arch, \$name, \$rev) = klinstall::get_os_info();

		klinstall::debug "Got info: \$arch, os: \$name, ver: \$rev" 
			if \$cmdline_selected{"debug"};

		# Check for arch, OS etc.
		my \$os_ver_reason = appdata::app_check_os_ver (\$arch, \$name, \$rev);
		klinstall::fatal (\$INSTALL_MSG_1, \$APPINFO{"NAME"}, \$os_ver_reason)
			if \$os_ver_reason;

		# check for Linux distribution name
		my %linux_distro = klinstall::get_linux_info();
		if ( \$linux_distro{"NAME"} )
		{
			my \$res = appdata::app_check_linux_distro (lc \$linux_distro{"NAME"}, \$linux_distro{"VER"});

			\$res == 1 and klinstall::warning (\$INSTALL_MSG_2, \$APPINFO{"NAME"})
				or \$res == 2 and klinstall::warning (\$INSTALL_MSG_3, \$APPINFO{"NAME"}, \$linux_distro{"NAME"})
		}

		# Check for required components
		my @req_comps = appdata::app_check_required_components();
		klinstall::fatal (\$INSTALL_MSG_6, \$APPINFO{"NAME"}, \$req_comps[0]{"NAME"}, \$req_comps[0]{"VER"})
			if @req_comps;

		# Check for optional components
		my @opt_comps = appdata::app_check_optional_components();
		foreach (@opt_comps)
		{
			my %rec = %{\$_};
			klinstall::warning (\$INSTALL_MSG_7, \$APPINFO{"NAME"}, \$req_comps[0]{"NAME"}, \$req_comps[0]{"WHAT2DO"});
		}

		# Check whether the application is already installed - only for tar.gz
		if ( !\$pkgmgr && !\$cmdline_selected{"force"} )
		{
			my %already_installed = klinstall::read_app_info (\$APPINFO{"ID"});
			klinstall::fatal (\$INSTALL_MSG_4, \$APPINFO{"NAME"}, \$already_installed{"VERSION"}) if %already_installed;

            # Check for conflicting applications
			my %applist = appdata::app_get_conflict_ids ();
			foreach (keys %applist)
			{
				my %ai = klinstall::read_app_info (\$_);
				next if !%ai;

				klinstall::fatal (\$INSTALL_MSG_5, \$APPINFO{"NAME"}, \$ai{"NAME"}, \$applist{"\$_"});
			}
		}
	}

	my (@installed_files, @installed_dirs, %uniqmatch);

	# install (copy-files) logic
	if ( !\$logic )
	{
		# load appdata module
		my %APPINFO = appdata::get_application_info();

		foreach (split ("\\n", \$APPINFO{"DIRS"}))
		{
			next if !/^(\\d+)\\s+([\\w\\:]+)\\s+(.*)\$/;
			my (\$mode, \$own, \$dest) = (\$1, \$2, \$3);
			my \$realdest = \$dest;

			if ( \$dest =~ /^(\\w+)\\/(.*)\$/ )
			{
				if ( !defined \$APPINFO{\$1} )
				{
					klinstall::warning ("Bug in installer script: file \$2 should be installed in unknown directory \$1.");
					next;
				}

				\$realdest = defined \$2 && length \$2 ? "\$APPINFO{\$1}/\$2" : "\$APPINFO{\$1}";
			}

			next if -d \$realdest;
			push @installed_dirs, \$dest;

			klinstall::output ("Creating directory \$realdest\\n");
			klinstall::install_path (\$realdest, \$mode, \$own, \\@installed_dirs);
		}

		foreach (split ("\\n", \$APPINFO{"FILES"}))
		{
			next if !/^(\\d+)\\s+([\\w\\:]+)\\s+(.*?)\\s+(.*)\$/;
			my (\$mode, \$own, \$dest, \$src) = (\$1, \$2, \$3, \$4);
			my \$realdest = \$dest;

			if ( \$dest =~ /^(\\w+)\\/(.*)\$/ )
			{
				if ( !defined \$APPINFO{\$1} )
				{
					klinstall::warning ("Bug in installer script: file \$2 should be installed in unknown directory \$1.");
					next;
				}

				\$realdest = "\$APPINFO{\$1}/\$2";
			}

			if ( \$realdest =~ /(.*)\\/(.*?)\$/ && !-d \$1 )
			{
				my \$msg = klinstall::make_path (\$1, 0755, \\@installed_dirs);
				klinstall::fatal (\$msg) if \$msg;
			}

			push @installed_files, \$dest;
			klinstall::output ("Installing file \$realdest\\n");
			klinstall::install_file (\$src, \$realdest, \$mode, \$own);
		}

		%uniqmatch = ();
		@installed_files = grep { ! \$uniqmatch{\$_} ++ } @installed_files;
		%uniqmatch = ();
		@installed_dirs = grep { ! \$uniqmatch{\$_} ++ } @installed_dirs;

		print ("\\n");
	}

	# post-install logic
	if ( !\$logic || \$logic eq "post" )
	{
		my %APPINFO = appdata::get_application_info();
		my %INSTINFO = klinstall::read_app_info (\$APPINFO{"ID"});

		# Store generic appinfo
		my %stored_info;
		\$stored_info{"ID"} = \$APPINFO{"ID"};
		\$stored_info{"NAME"} = \$APPINFO{"NAME"};
		\$stored_info{"VERSION"} = \$APPINFO{"VER"};
		\$stored_info{"DEFAULTCONFIG"} = \$APPINFO{"DEFAULT_CONFIG"};
		\$stored_info{"USED_PKGMGR"} = \$pkgmgr if \$pkgmgr;
		\$stored_info{"INSTROOT"} = \$APPINFO{"INSTROOT"};
		\$stored_info{"PATH_BASES"} = \$APPINFO{"PATH_BASES"} if \$APPINFO{"PATH_BASES"};
		\$stored_info{"PATH_LICENSES"} = \$APPINFO{"PATH_LICENSES"} if \$APPINFO{"PATH_LICENSES"};
		if (\$pkgmgr eq 'rpm') {
		    # store upgrade flag for upgrade from older installer what does not use
		    # --upgrade cmd line option due uninstall
		    \$stored_info{"IS_UPDATE"} = \$cmdline_selected{"upgrade"} ? 1 : 0;
		}

		if ( @installed_files || @installed_dirs )
		{
			\$stored_info{"FILE"} = \\@installed_files if @installed_files;
			\$stored_info{"DIR"} = \\@installed_dirs if @installed_dirs;
		}

		klinstall::write_app_info (\$APPINFO{"ID"}, %stored_info);

		if ( \$pkgmgr eq "rpm" )
		{
			klinstall::output (\$INSTALL_MSG_18, \$APPINFO{"NAME"}, \$APPINFO{"COMP_POSTINSTALL_SCRIPT"});
			exit 0;
		}

		appdata::app_modify_config_file();

		my \$av_bases_updated = 0;
		my \$postinstall_message = "";

		# Install licenses (key files)
		if ( \$APPINFO{"REQUIRE_LICENSES"} 
		&& appdata::app_is_license_needed() )
		{
			klinstall::output ("Installing license files.");

			my \$licpath = klinstall::ask_question ("CONFIGURE_ENTER_KEY_PATH", "", \$INSTALL_MSG_9);
			if ( \$licpath )
			{
				while ( 1 )
				{
					my \$errmsg = appdata::install_licenses (\$licpath);
					last if !\$errmsg;

					klinstall::output ("%1", \$errmsg);

					\$licpath = klinstall::ask_question ("CONFIGURE_ENTER_KEY_ANOTHER_PATH", "", \$INSTALL_MSG_10);
					last if !\$licpath;
				}
			}

			\$postinstall_message .= "\\n" . klinstall::translate_string (\$INSTALL_MSG_12, \$APPINFO{"TXT_WRTIL"})
				if !\$licpath;
		}

		# Configure the KeepUp2Date component
		if ( \$APPINFO{"REQUIRE_SETUP_KEEPUP2DATE"} )
		{
			klinstall::output ("\\nConfiguring KeepUp2Date proxy settings.");
			while ( 1 )
			{
				my \$http_proxy = klinstall::ask_question ("CONFIGURE_KEEPUP2DATE_ASKPROXY",
						"no",
						\$INSTALL_MSG_17, 
						\$APPINFO{"NAME"});

				last if !\$http_proxy || \$http_proxy eq "no";
				if ( \$http_proxy =~ /^(http:\\/\\/.*?:.*?\\@[\\w\\-\\d\\.:]+)\\/?\$/
				|| \$http_proxy =~ /^(http:\\/\\/[\\w\\-\\d\\.:]+)\\/?\$/ )
				{
					appdata::setup_http_proxy(\$1);
					last;
				}
			}
		}

		# Set up the AV bases
		if ( \$APPINFO{"PATH_BASES"} )
		{
			my \$updates_path = \$ENV{"KLUPDATES"} ? \$ENV{"KLUPDATES"} : ( -d  "updates" ? "updates" : "../updates" );
			if ( !klinstall::install_AV_bases (\$updates_path, \$APPINFO{"PATH_BASES"}) )
			{
				if ( klinstall::ask_boolean ("CONFIGURE_RUN_KEEPUP2DATE", "yes", \$INSTALL_MSG_13) )
				{
					# run keepup2date
					\$av_bases_updated = 1 if appdata::update_AV_database();
				}

				\$postinstall_message .= "\\n" . klinstall::translate_string (\$APPINFO{"REQUIRE_AV_BASES"} ? \$INSTALL_MSG_15 : \$INSTALL_MSG_14, 
							\$APPINFO{"TXT_WRTUAB"},
							\$APPINFO{"COMP_SETUP_SCRIPT"},
							\$APPINFO{"NAME"}) 
					if !\$av_bases_updated;
			}
			else
			{
				\$av_bases_updated = 1;
			}
		}

		setup_webmin_module (%APPINFO) if \$APPINFO{"COMP_WEBMIN_MODULE"};

		# run setup.pl
		if ( !\$APPINFO{"REQUIRE_AV_BASES"} || \$av_bases_updated )
		{
			system ("perl \$APPINFO{COMP_SETUP_SCRIPT}");
		}

		klinstall::output (\$INSTALL_MSG_16, \$APPINFO{"NAME"}, \$APPINFO{"DEFAULT_CONFIG"}, "\$APPINFO{INSTROOT}/bin");
		klinstall::output ("\\n\$postinstall_message") if \$postinstall_message;
	}

	exit 0;
BND1234567890ABCDEFGH_preinstall.pl_EOF

# drop klinstall.pm
cat > klinstall.pm <<BND1234567890ABCDEFGH_klinstall.pm_EOF
#!/usr/bin/perl -w

package klinstall;

# Constants. We don't use 'use constant' - it is perl 5.6 specific.
my \$INI_ERROR_TEXT = "";
my \$CFG_AUTOANSWERS_FILE = \$ENV{"KLAUTOANSWERS"} ? \$ENV{"KLAUTOANSWERS"} : "autoanswers.conf";
my \$CFG_APPSETUP_FILE = "/var/opt/kaspersky/applications.setup";
my %AUTOANSWERS;

my %PKGMGRS = ("rpm" => "rpm", "deb" => "deb", "pkg" => "pkg", "opkg" => "pkg");
my \$IS_FREEBSD = (\$^O =~ /^freebsd\$/i) ? 1 : 0;

################### module initialization #############################
\$| = 1;


# read autoanswers file if present
if ( open FILE, "<\$CFG_AUTOANSWERS_FILE" )
{
	foreach (<FILE>)
	{
		s/[\\n\\r]//g;
		next if !/^(\\w+)\\s*=\\s*(.*)/;
		\$AUTOANSWERS{\$1} = \$2;
	}

	close FILE;	
}

# every module should return true to be loaded successfully
1;

################## external subs #########################################

sub autoanswer
{
    return \$AUTOANSWERS{shift};
}

# Standard formatted output
sub output
{
	my \$text = translate_string(@_) . "\\n";
    my \$right_margin = 65;
	my \$margin = \$right_margin - 1;
    my (\$para, \$accum, \$outstr);

    foreach \$para (split ("\\n", \$text))
	{
		\$accum = '';
		\$para =~ s/\\s+/ /g;
		\$para .= ' ';
		
		while (\$para =~ s/^([^\\n]{0,\$margin})\\s//)
		{
			\$accum .= \$1 . "\\n";
		}
		
		\$accum .= \$para if length (\$para) > \$margin;
		\$outstr .= \$accum;
    } 

    print \$outstr if defined \$outstr;
	return 1;
}


# Debug output
sub debug
{
	output "DEBUG: " . translate_string(@_) . "\\n";
}

# Terminate installation process
sub fatal
{
	output "\\nFatal error: " . translate_string(@_) . "\\n";
	exit 2;
}

# Generate a warning
sub warning
{
	output "Warning: " . translate_string(@_) . "\\n";
}

# Replace %1, %2 etc. by args. 
# Args can contain %1 too, but they should not be replaced
sub translate_string
{
	my (\$str, @args) = @_;

	# First, convert color sequences
	\$str =~ s/<B>/\\x1B\\x5B1m/g;
	\$str =~ s/<\\/B>/\\x1B\\x5B22m/g;

	for ( my \$i = 0; \$i < scalar @args; \$i++ )
	{
		warn ("Empty argument \$i in string '\$str'\\n") if !defined \$args[\$i];
		my \$pattern = "%" . (\$i + 1);
		\$str =~ s/\$pattern/\$args[\$i]/g;
	}

	return \$str;
}

# Return information about Linux distribution
sub get_linux_info
{
	my %ld;

	\$ld{"NAME"} = "RedHat";
	\$ld{"VERSION"} = "7.2";

	return %ld;
}


# Return application info for current ID
sub read_app_info
{
	my \$id = shift;
	my %appinfo;

	# read settings data
	open F, "<\$CFG_APPSETUP_FILE" or return;
	flock F, 2; # LOCK_EX
	my \$content = join ("", <F>);
	flock F, 8; # LOCK_UN
	close F;
	\$content =~ s/\\r//g;

	return unless \$content =~ /\\[\$id\\]\\n(.*?)\\n\\n/s;
	\$content = \$1;

	foreach (split (/\\n/, \$content))
	{
		next if !/^(.*?)=(.*)\$/;

		if ( !defined \$appinfo{\$1} )
		{
			\$appinfo{\$1} = \$2;
			next;
		}

		# Create an array ref
		if ( ref \$appinfo{\$1} ne "ARRAY" )
		{
			my @arr = (\$appinfo{\$1}, \$2);
			\$appinfo{\$1} = \\@arr;
		}
		else
		{
			push @{\$appinfo{\$1}}, \$2;
		}
	}

	close F;
	return %appinfo;
}


# Stores application info for specified ID (or adds the existing info)
sub write_app_info
{
	my (\$appid, %appinfo) = @_;
	my \$content = "";

	# store all the data in text string
	my %olddata = read_app_info (\$appid);
	%appinfo = (%olddata, %appinfo) if %olddata;

	my \$newdata = "[\$appid]\\n";
	foreach my \$key (sort keys %appinfo)
	{
		my \$value = defined \$appinfo{\$key} ? \$appinfo{\$key} : "";

		if ( ref \$value eq "ARRAY" )
		{
			foreach (@{\$value})
			{
				\$newdata .= "\$key=\$_\\n";
			}
			next;
		}

		\$newdata .= "\$key=\$value\\n";
	}

	\$newdata .= "\\n";

	# read settings data
	if ( open F, "+<\$CFG_APPSETUP_FILE" )
	{
		flock F, 2; # LOCK_EX
		\$content = join ("", <F>);
		\$content =~ s/\\r//g;
		truncate F, 0;
		seek F, 0, 0;
	}
	else
	{
		open F, ">\$CFG_APPSETUP_FILE" or return;
	}

	\$content = \$content =~ /\\[\$appid\\]\\n.*?\\n\\n/s
			? \$\` . \$newdata . \$'
			: "\$content\$newdata";

	print F \$content;
	flock F, 8; # LOCK_UN
	close F;

	return 1;
}


# Removes application info for specified ID
sub remove_app_info
{
	my \$appid = shift;

	# read settings data
	open F, "+<\$CFG_APPSETUP_FILE" or return;
	flock F, 2; # LOCK_EX
	my \$content = join ("", <F>);
	\$content =~ s/\\r//g;

	if ( \$content =~ /\\[\$appid\\]\\n.*?\\n\\n/s )
	{
		\$content = \$\` . \$';
		# and store them
		truncate F, 0;
		seek F, 0, 0;
		print F \$content;
	}

	flock F, 8; # LOCK_UN
	close F;

	unlink \$CFG_APPSETUP_FILE if -s \$CFG_APPSETUP_FILE == 0;
	return 1;
}

# returns directory component from the path
sub internal_dirname
{
	return (\$_[0] =~ /(.*)\\/[^\\/]+\$/) ? \$1 : \$_[0];
}

# Return machine information - arch, os name, os version
sub get_os_info
{
	my \$arch = \`uname -p 2>/dev/null\`;
	my \$rev = \`uname -r\`;
	my \$name = \`uname -s\`;
	\$arch = \`uname -m\` if !\$arch || \$arch =~ /^unknown/;
	
	\$arch =~ s/[\\s\\r\\n]//g;
	\$rev =~ s/[\\s\\r\\n]//g;
	\$name =~ s/[\\s\\r\\n]//g;

    # Special version handling for FreeBSD, OpenBSD...
	\$rev = \$1 if \$name =~ /bsd/i && \$rev =~ /^([\\d\\.]+)/;

	# hey, this is Linux!
	return (\$arch, \$name, \$rev);
}


# Display a simple help about command-line options
sub display_cmdline_options
{
	my %optlist = @_;
	print "Supported command line options:\\n";
	foreach (keys %optlist)
	{
		next if !\$optlist{\$_};
		print ("    \$_ - \$optlist{\$_}\\n");
	}
}

# checks for root permissions, and aborts if not
sub check_for_root
{
	my \$procname = shift;

	return if \$^O eq "cygwin" || \$^O eq "MSWin32";
	klinstall::fatal ("%1 must be run by root!", \$procname) if \$> != 0;
}


# Does a file lookup in all the paths specified in argument list.
sub file_lookup
{
	my (\$fn, @paths) = @_;

	foreach my \$pathlist (@paths)
	{
		my \$path = "\$pathlist/\$fn";
		\$path =~ s/\\/\\//\\//g;
		return \$path if -f \$path;
	}

	return;
}


# Get a pid(s) of a running process with specified name, or undef
sub get_process_pids
{
	my \$processname = shift;
	my (%pids, @pidlist);

	# ps options and patterns are different on different systems
	my @osdata = get_os_info();

	if ( \$osdata[1] =~ /hpux|sunos/i )
	{
		my @plist = split ("\\n", \`ps -ef\`);
		shift @plist;
		foreach (@plist)
		{
			my \$pid_part = substr (\$_, 9, 6);
			my \$cmd_part = substr (\$_, 47);
			next if \$pid_part !~ /\\s*(\\d+)\\s\$/;
			\$pid_part = \$1;

			\$pids{\$pid_part} = \$cmd_part;
			\$pids{\$pid_part} = \$1 if \$pids{\$pid_part} =~ /^(.*?)\\s+/;
		}
	}
	else
	{
		my @plist = split ("\\n", \`ps ax\`);
		shift @plist;
		foreach (@plist)
		{
			s/\\s+\$//;
			next if !/^\\s*(\\d+)/;
			my \$pid_part = \$1;
			my \$cmd_part = substr (\$_, 27);
			\$pids{\$pid_part} = \$cmd_part;

			\$pids{\$pid_part} = \$1 if \$pids{\$pid_part} =~ /^(.*?)\\s+/;
		}
	}

	foreach (keys %pids)
	{
		next if \$pids{\$_} !~ /\\b\$processname\\b/;
		push @pidlist, \$_;
	}

	return @pidlist;
}


# Make recursive path. Mode argument is optional
sub make_path
{
	my (\$dirname, \$mode, \$trackref) = @_;

	return if -d \$dirname;
	if ( \$dirname =~ /(.+)\\/[^\\/]+\$/ )
	{
		my \$msg = make_path (\$1, \$mode, \$trackref);
		return \$msg if \$msg;
	}

	return "Could not create directory \$dirname: \$!" 
		if !mkdir (\$dirname, \$mode);

	push (@{\$trackref}, \$dirname) if \$trackref;
	return;    	
}


# Remove recursive path. Mode argument is optional
sub remove_path
{
	my \$dirname = shift;

	return 1 if !-d \$dirname;
	remove_path (\$1) if \$dirname =~ /\\/([\\/+])\$/;

	warning "Could not delete directory \$dirname: \$!\\n" if !rmdir \$dirname;
}

# Copies a file. Returns the error message if failed
sub copy_file
{
	my (\$src, \$dest) = @_;

	# if \$dest is a directory, add the source filename
	\$dest .= "/\$1" if -d \$dest && \$src =~ /([^\\/]+)\$/;

	open FSRC, "<\$src" or return "Could not read file \$src: \$!";
	open FDST, ">\$dest" or return "Could not write file \$dest: \$!";

	binmode FSRC;
	binmode FDST;
	my \$length;

	while ( (\$length = sysread (FSRC, my \$data, 8192)) > 0 )
	{
		if ( syswrite (FDST, \$data, \$length) != \$length )
		{
			close FSRC;
			close FDST;
				
			unlink \$dest;
			return "Could not write file \$dest: \$!";
		}
	}

	close FSRC;
	close FDST;

	if ( !defined \$length )
	{
		unlink \$dest;
		return "Couldn't read file \$src: \$!";
	}

	# Set the same owner and mode
	my @statinfo = stat (\$src);
	if ( @statinfo )
	{
		chown \$statinfo[4], \$statinfo[5], \$dest;
		chmod ((\$statinfo[2] & 07777), \$dest);
	}

	return;
}


# Copies the matched files. Returns the error messages array if failed
sub copy_files
{
	my (\$srcdir, \$destdir, \$pattern) = @_;
	my @errormessages;

	if ( opendir (DIR, \$srcdir) )
	{
		my @files = grep { -f "\$srcdir/\$_" && /\$pattern/i } readdir DIR;
		closedir DIR;

		foreach (@files)
		{
			my \$msg = klinstall::copy_file ("\$srcdir/\$_", "\$destdir/\$_");
			push @errormessages, \$msg if \$msg;
		}
	}
	else
	{
		push @errormessages, "Could not open directory \$srcdir: \$!";
	}

	return @errormessages;
}


# Alters the file/dir owner/group
sub lazy_chown
{
	my \$usergroup = shift;
	my @targets = @_;

	my @arr = split (/:/, \$usergroup);
	my \$user = (\$arr[0] && getpwnam(\$arr[0])) ? getpwnam(\$arr[0]) : -1;
	my \$group = (\$arr[1] && getgrnam(\$arr[1])) ? getgrnam(\$arr[1]) : -1;

	# Do we need to change ownership?
	return if \$user == -1 && \$group == -1;

	chown (\$user, \$group, @targets);
}


# Copies a file, and sets up its owner/permission
sub install_file
{
	my (\$src, \$dest, \$mode, \$owner) = @_;

	my \$msg = copy_file (\$src, \$dest);
	fatal (\$msg) if \$msg;
	lazy_chown (\$owner, \$dest);
	chmod (oct(\$mode), \$dest) if \$mode;
}


# Creates a directory, and sets up its permission
sub install_path
{
	my (\$dir, \$mode, \$owner, \$trackref) = @_;

	my \$msg = make_path (\$dir, oct(\$mode), \$trackref);
	fatal (\$msg) if \$msg;

	lazy_chown (\$owner, \$dir);
	chmod (oct(\$mode), \$dir);
}


# Asks a question from user, or from autoanswer file
sub ask_question
{
	my (\$qid, \$def_answer, \$question, @args) = @_;

	# don't ask anything if question is already answered
	return \$AUTOANSWERS{\$qid} if defined \$AUTOANSWERS{\$qid};

	my \$qtext = translate_string (\$question, @args);
	chomp (\$qtext);

	output (\$def_answer ? "\$qtext [\$def_answer]: " : \$qtext);

	my \$answer = <STDIN>;
	\$answer = "" if !defined \$answer;
	\$answer =~ s/[\\r\\n]//g;
	\$answer = \$def_answer if length(\$answer) == 0;

	# For debug mode: record the question and the answer in the appropriate file
	if ( defined \$ENV{"KAV_INSTALL_HISTORY_FILE"} && open F, ">>\$ENV{KAV_INSTALL_HISTORY_FILE}" )
	{
		print F "\$qid=\$answer\\n";
		close F;
	}

	output "\\n";
	return \$answer;
}


# Ask a 'yes/no' question
sub ask_boolean
{
	while ( 1 )
	{
		my \$answ = ask_question (@_);

		\$answ = "yes" if \$answ =~ /^yes\$/i || \$answ =~ /^y/i;
		\$answ = "no"  if \$answ =~ /^no\$/i  || \$answ =~ /^n/i;

		return 1 if \$answ eq "yes";
		return 0 if \$answ eq "no";
		
		output "Please answer either 'yes' or 'no'.\\n";
	}
}


# Parses command-line args and script name, gets the work-logic
sub get_work_logic
{
	my (\$parsename, \$argsref) = @_;
	my (\$pkgname, \$part) = ("", "");

	# cut the path
	\$parsename = \$1 if \$parsename =~ /([^\\/]+)\$/;

	# if argv[0] is package manager name, get it
	if ( @{\$argsref} && defined \$PKGMGRS{@{\$argsref}[0]} )
	{
		\$pkgname = \$PKGMGRS{@{\$argsref}[0]};
		shift @{\$argsref};
	}

	# if argv[1] is logic part name, get it
	if ( @{\$argsref} 
	&& (@{\$argsref}[0] eq "pre" || @{\$argsref}[0] eq "post") )
	{
		\$part = @{\$argsref}[0];
		shift @{\$argsref};
		return (\$part, \$pkgname);
	}

	# parse name
	\$part = "pre" if \$parsename =~ /^pre/;
	\$part = "post" if \$parsename =~ /^post/;

	return (\$part, \$pkgname);
}


# Check the source directory for valid AV bases set, and install them
sub install_AV_bases
{
	my (\$srcdir, \$destdir) = @_;

	open ASET, "<\$srcdir/avp.set" or return;
	my @set_content = <ASET>;
	close ASET;

	# check for all the files existance
	foreach (@set_content)
	{
		s/[\\n\\r]//g;
		next if /^[;#]/ || /^\$/;
		return if !-f "\$srcdir/\$_";
	}

	# and copy the files
	klinstall::output ("Found Kaspersky Anti-Virus bases in \$srcdir, copying them.");
	klinstall::copy_files (\$srcdir, \$destdir, ".+");

	return 1;
}


# execute a system command
sub exec_cmd
{
	my \$cmd = shift;
	my \$msg;

	# Escape shell args
	\$cmd =~ s/([\\&;\\\`'\\\\\\|"*?~<>^\\(\\)\\[\\]\\{\\}\\\$\\n\\r])/\\\\\$1/g;

	my \$tmpfile = "/tmp/klinstallexec.\$\$.tmp";
	my \$retcode = system ("\$cmd 1>\$tmpfile 2>&1");
	\$retcode = (\$retcode == -1 ? "undef" : \$?); # -1 means system() failed

	if ( open FILE, "<\$tmpfile" )
	{
		\$msg = join ("", <FILE>);
		close FILE;

		unlink \$tmpfile;
	}

	return (\$retcode, \$msg);
}


# execute a system command, escaping arguments
sub exec_cmd_safe
{
	my @args = @_;
	my (\$msg, \$cmd);

	# Escape shell args
	foreach (@args)
	{
		s/([\\&;\\\`'\\\\\\|"*?~<>^\\(\\)\\[\\]\\{\\}\\\$\\n\\r])/\\\\\$1/g;
	}

	my \$tmpfile = "/tmp/klinstallexec.\$\$.tmp";
	my \$cmdline = join (" ", @args) . " 1>\$tmpfile 2>&1";

	my \$retcode = system (\$cmdline);
	\$retcode = (\$retcode == -1 ? "undef" : \$?); # -1 means system() failed

	if ( open FILE, "<\$tmpfile" )
	{
		\$msg = join ("", <FILE>);
		close FILE;

		unlink \$tmpfile;
	}

	return (\$retcode, \$msg);
}


# execute a system command. If the command doesn't exit with code 0, abort
# with the error message
sub exec_zero
{
	my (\$rc, \$msg) = exec_cmd (@_);

	if ( !defined \$rc		# system() failed
	|| (\$? >> 8) != 0		# exit code != 0
	|| (\$? & 127) != 0 )	# signal num
	{
		fatal ("execute (\$_[0]) failed: \$msg");
	}
}


# Ini file error message
sub get_ini_error
{
	return \$INI_ERROR_TEXT ? \$INI_ERROR_TEXT : undef;
}

# Load some data from the configuration (ini-like) file
sub load_ini_file
{
	if ( !open FILE, "<\$_[0]" )
	{
		\$INI_ERROR_TEXT = "unable to read file \$_[0]: \$!";
		return;
	}

	my \$content = join ("", <FILE>);
	close FILE;

	# just in case
	\$content =~ s/\\r//g;

	my (\$sname, \$sdata) = ("&%HEADER%&", "");
	my (%inicontent);

	foreach my \$line (split ("\\n", \$content))
	{
		# section header
		if ( \$line =~ /^\\s*\\[(.*?)\\]\\s*/ )
		{
			if ( \$sname ne "&%HEADER%&" )
			{
				\$inicontent{"sections"}{\$sname} = \$sdata;
				push @{\$inicontent{"order"}}, \$sname;
			}
			else
			{
				\$inicontent{"header"} = \$sdata;
			}

			(\$sname, \$sdata) = (\$1, "");
			next;
		}

		\$sdata .= \$line . "\\n";
	}

	if ( \$sname ne "&%HEADER%&" )
	{
		\$inicontent{"sections"}{\$sname} = \$sdata;
		push @{\$inicontent{"order"}}, \$sname;
	}
	else
	{
		\$inicontent{"header"} = \$sdata;
	}

	# no sections
	if ( !\$inicontent{"order"} || !@{\$inicontent{"order"}} )
	{
		\$INI_ERROR_TEXT = "no sections found in file \$_[0]";
		return;
	}

	return %inicontent;
}


sub save_ini_file
{
	my \$filename = shift;
	my %inidata = @_;

	if ( !open FILE, ">\$filename" )
	{
		\$INI_ERROR_TEXT = "unable to write file \$filename: \$!";
		return;
	}

	print FILE "\$inidata{header}" if \$inidata{"header"};

	foreach my \$name (@{\$inidata{"order"}})
	{
    	print FILE "[\$name]\\n\$inidata{sections}->{\$name}";
    	print FILE "\\n" if \$inidata{"sections"}->{\$name} !~ /\\n\\n\$/;
	}

    close FILE;
	return 1;
}


# Get ini file value
sub get_ini_value
{
	my (\$ref, \$section, \$key) = @_;

	my \$undefpattern = "^\$key\\\\s*=\\\\s*\\\$";
	my \$pattern = "^\$key\\\\s*=\\\\s*(.+?)\\\$";

	return undef if !\$ref->{"sections"}->{\$section}
		|| \$ref->{"sections"}->{\$section} =~ /\$undefpattern/m
		|| \$ref->{"sections"}->{\$section} !~ /\$pattern/m;

	return \$1;
}


# Set ini file value
sub set_ini_value
{
	my (\$ref, \$section, %data) = @_;

	# add the section if not exist
	if ( !\$ref->{"sections"}->{\$section} )
	{
		push @{\$ref->{"order"}}, \$section;
		\$ref->{"sections"}->{\$section} = "";
	}

	# search through the content
	foreach my \$key (keys %data)
	{
		my \$value = defined \$data{\$key} ? \$data{\$key} : "";

		\$ref->{"sections"}->{\$section} =~ m/^\\s*\$key\\s*=\\s*/m
			and \$ref->{"sections"}->{\$section} =~ s/^\\s*\$key\\s*=.*?\\n/\$key=\$value\\n/m
				or \$ref->{"sections"}->{\$section} = "\$key=\$value\\n\$ref->{sections}->{\$section}";
	}
}


# Delete the whole section, or ini file key=value
sub remove_ini_value
{
	my (\$ref, \$section, @keys) = @_;

	return if !\$ref->{"sections"}->{\$section};

	foreach my \$key (@keys)
	{
		\$ref->{"sections"}->{\$section} =~ s/^\\s*\$key\\s*=\\s*.*\\n?//gm;
	}

	# add delete the section if empty
	if ( !@keys || \$ref->{"sections"}->{\$section} =~ /^\\s*\$/s )
	{
		delete \$ref->{"sections"}->{\$section};
		@{\$ref->{"order"}} = grep { \$_ ne \$section } @{\$ref->{"order"}};
	}
}


# Modify some data in the configuration (ini-like) file
sub update_ini_file
{
	my (\$filename, \$section, %data) = @_;

	# read inifile
	my %inidata = load_ini_file (\$filename);
	return if !%inidata;

	set_ini_value (\\%inidata, \$section, %data);

	return save_ini_file (\$filename, %inidata);
}


# Creates a new user account if not exist, returns undef if OK, or the error message text.
# It also creates homedir if it is not exist.
sub add_user
{
	my (\$username, \$group, \$shell, \$home) = @_;

	return 1 if getpwnam (\$username);
	my @cmd = \$IS_FREEBSD ? ("pw", "useradd", "-n", \$username) : ("useradd");

	push @cmd, ("-g", \$group) if \$group;
	push @cmd, ("-s", \$shell) if \$shell;
	push @cmd, ("-d", \$home) if \$home;

	push @cmd, \$username if !\$IS_FREEBSD;

	make_path (\$home, 0755) if !-d \$home;

	return 1 if system (@cmd) == 0;
	\$@ = "Could not add user '\$username'";
	return undef;
}

sub add_group
{
	my \$group = shift;

	return 1 if getgrnam (\$group);
	my @cmd = \$IS_FREEBSD ? ("pw", "groupadd", "-n", \$group) : ("groupadd", \$group);

	return 1 if system (@cmd) == 0;
	\$@ = "Could not add group '\$group'";
	return undef;
}


sub add_user_to_group
{
	my (\$user, \$group) = @_;

	# Check whether the user is already in this secondary group
	my \$groupmembers = ((getgrnam (\$user))[3]);
	my \$userrx = quotemeta (\$user);
	my @cmd;

	return 1 if \$groupmembers && \$groupmembers =~ /\\b\$userrx\\b/;

	if ( \$IS_FREEBSD ) {
		@cmd = ("pw", "usermod", \$user, "-G", \$group);
	} elsif ( \$^O =~ /^openbsd\$/i ) {
		@cmd =("usermod", "-G", \$group, \$user);
	} else {
		@cmd = ("groupmod", "-A", \$user, \$group);
	}

	return 1 if system (@cmd) == 0;
	\$@ = "Could not add user '\$user' to group 'group'";
	return undef;
}

sub exec_cmd_as_user
{
	my (\$user, @args) = @_;
	my \$uid = getpwnam(\$user);

	return 255 if !defined \$uid;

	my \$pid = fork();

	return -1 if \$pid == -1;

	if ( !\$pid )
	{
		# child
		\$! = 0;
		\$< = \$> = \$uid;

		exit 255 if \$! != 0;
		
		# reopen file descriptors
		open (STDOUT, "> /dev/null"); 
		open (STDERR, "> /dev/null");

		exec @args;
		exit 255;
	}

	wait();
	return \$?;
}
BND1234567890ABCDEFGH_klinstall.pm_EOF

# drop appdata.pm
cat > appdata.pm <<BND1234567890ABCDEFGH_appdata.pm_EOF
package appdata;
use klinstall;

my \$APPID = 1194;

my \$KLUSER = "kluser";
my \$KLGROUP = "klusers";

############################## text strings ##################################
my \$SETUPMSG_1 = <<MSG_END;

<B>Setting up protection with Kaspersky Anti-Virus for Proxy server</B>.

The installation program can automatically configure your ICAP-enabled Squid to be protected by Kaspersky Anti-Virus ICAP server.
 
MSG_END

my \$SETUPMSG_SQUID_MANUAL_HEAD = <<MSG_END;

<B>Manualy setting up Squid proxy server</B>
MSG_END

my \$SETUPMSG_INFO = <<MSG_END;

If you want to configure your Squid later, you can run the configuration script again by running <B>%1</B>.
 
MSG_END

my \$SETUPMSG_3 = <<MSG_END;

********************************** WARNING! *********************************
The configuration file %1 you've provided appears to be from a Squid built without ICAP support. Note that you need to get the ICAP-enabled Squid to use it with Kaspersky Anti-Virus ICAP server. Therefore the configuration changes made by the installation may made your Squid setup unusable.

Are you <B>sure</B> that the configuration file you've entered is from the ICAP-enabled Squid?
MSG_END

my \$SETUPMSG_4 = <<MSG_END;

<B>Configuring Squid to use Kaspersky Anti-Virus ICAP-Server</B>

Proxy server binary path: %1
Proxy server configuration file: %2

Please enter 'Y' to confirm that you want to protect this proxy server with Kaspersky Anti-Virus. Enter 'N' if proxy server has been detected incorrectly, or if you do not want to protect it.
MSG_END

my \$SETUPMSG_5 = <<MSG_END;
Setup was unable to detect an existing installation of a proxy server on your computer. Either it is not installed or has been installed to an unknown location.
If it has been installed, and you know the installation details, enter 'Y'. Otherwise enter 'N' (configuration will be aborted):
MSG_END

my \$SETUPMSG_6 = <<MSG_END;

Please enter the full path to your Squid configuration file (or an empty string to abort configuration):
MSG_END

my \$SETUPMSG_7 = <<MSG_END;

Please enter the full path to your Squid binary:
MSG_END

my \$SETUPMSG_8 = <<MSG_END;

Please choose 1-%1:
MSG_END

my \$SETUPMSG_REMOTE_HEAD = <<MSG_END;

<B>Configure address to listen remote connections</B>
 
MSG_END

my \$SETUPMSG_REMOTE_INFO = <<MSG_END;
Configuring ICAP-server to listen on %1
MSG_END

my \$SETUPMSG_REMOTE_QUERY = <<MSG_END;
Please enter address in <hostname|IP-address>:<port>  format (for example 0.0.0.0:1344 to bind ona all interfeces) or 'cancel' to cancel configuration
MSG_END

my \$CFGMARKER = "Added by Kaspersky Anti-Virus installer";
my \$CFGMARKER1 = "# \$CFGMARKER\\n";
my \$CFGMARKER2 = "# /\$CFGMARKER\\n";

# every module should return true to be loaded successfully
1;


# Returns application information.
#
# Should define at least these hash values:
#
sub get_application_info
{
	my %AI;

	# Take install data
	seek (DATA, 0, 0);
	foreach (<DATA>)
	{
		chomp;
		m/^f\\s+(.*)\$/ and \$AI{"FILES"} .= "\$1\\n"
			or m/^d\\s+(.*)\$/ and \$AI{"DIRS"} .= "\$1\\n"
				or m/^(\\w+)=(.*)\$/ and \$AI{\$1} = \$2;
	}

	klinstall::fatal ("Corrupted installation!")
		if !\$AI{"INSTROOT"};

	\$AI{"ID"} = \$APPID;
	\$AI{"NAME"} = "Kaspersky Anti-Virus for Proxy Server";
	\$AI{"DEFAULT_CONFIG"} = "\$AI{CFGPATH}/kav4proxy.conf";

	\$AI{"PATH_BASES"} = "\$AI{DBROOT}/kav4proxy/bases";
	\$AI{"PATH_LICENSES"} = "\$AI{DBROOT}/kav4proxy/licenses";

	\$AI{"REQUIRE_AV_BASES"} = "0";
	\$AI{"REQUIRE_SETUP_KEEPUP2DATE"} = "1";
	\$AI{"REQUIRE_LICENSES"} = "1";

	\$AI{"TXT_WRTUAB"} = "keepup2date component";
	\$AI{"TXT_WRTIL"}  = "\$AI{INSTROOT}/bin/kav4proxy-licensemanager -a <keyfile>";

	\$AI{"COMP_WEBMIN_MODULE"} = "\$AI{SHAREPATH}/contrib/kav4proxy.wbm";
	\$AI{"COMP_SETUP_SCRIPT"}  = "\$AI{LIBEXEC}/setup/proxy_setup.pl";
	\$AI{"COMP_POSTINSTALL_SCRIPT"} = "\$AI{LIBEXEC}/setup/postinstall.pl";

	return %AI;
}


# Generic checks
sub app_check_os_ver
{
	my (\$arch, \$osname, \$osver) = @_;

	my %AI = appdata::get_application_info();
	my \$req_os_ver  = \$AI{'PKG_OS_VER'};
	my \$req_os_name = \$AI{'PKG_OS_NAME'};

	return "Invalid OS version. This package is only for \$req_os_name"
	    if \$req_os_ver && ("\$osname \$osver" !~ /^\$req_os_ver/i );

	if (\$osname =~ /freebsd/i and \$arch =~ /amd64/i) {
	    return "This package requires 32-bit runtime compatibility libraries"
		if ! -f '/libexec/ld-elf32.so.1';
	}

	my \$ver55root = (\$osname =~ /freebsd/i) ? '/usr/local/share' : '/opt/kav';

	if (-f "\$ver55root/5.5/kav4proxy/bin/kavicapserver") {
	    return  "This package is not compatible with older versions of KAV For Proxy Server.\\n" .
		    "Please uninstall old version before install this package.";
	}

	return 0;
}

sub app_check_linux_distro { return 0; }
sub app_get_conflict_ids { return; }
sub app_check_required_components { return; }

sub app_check_optional_components
{
	my %AI = appdata::get_application_info();

	# Create kluser/klusers if absent
	my \$shell = -f "/sbin/nologin" ? "/sbin/nologin" : "/bin/false";

	klinstall::add_group (\$KLGROUP) or return \$@;
	klinstall::add_user (\$KLUSER, \$KLGROUP, \$shell, \$AI{'DBROOT'}) or return \$@;
	return;
}

sub app_start
{
	my %AI = appdata::get_application_info();

	my \$cmd = "\$AI{RCCMD} start";

	print "Starting ICAP server: ";
	my (\$rc, \$outmsg) = klinstall::exec_cmd (\$cmd);
	print "\$outmsg\\n";

	#if (\$rc != 0) {
	#	print "Executing \$cmd failed: \$outmsg\\n";
	#}
	return \$rc == 0;
}

sub app_stop
{
	my %AI = appdata::get_application_info();
	my \$cmd = "\$AI{RCCMD} stop";

	print "Stopping ICAP server: ";
	my (\$rc, \$outmsg) = klinstall::exec_cmd (\$cmd);
	print "\$outmsg\\n";

	#my (\$rc, \$outmsg) = klinstall::exec_cmd (\$cmd);
	#if (\$rc != 0) {
	#	print "Executing \$cmd failed: \$outmsg\\n";
	#}
}

sub app_is_running
{
	my %AI = appdata::get_application_info();
	my \$cmd = "\$AI{'RCCMD'} status";

	my (\$rc, \$outmsg) = klinstall::exec_cmd (\$cmd);
	return (\$rc >>8) == 0;
}

sub app_checks_preuninstall
{
	return;
}

sub app_is_license_needed
{
	my %AI = get_application_info();
	my \$lictool = "\$AI{INSTROOT}/bin/kav4proxy-licensemanager";

	# Test whether the valid key file is already installed
	my (\$rc, \$msg) = klinstall::exec_cmd ("\$lictool -q -s");
	return \$rc == 0 ? 0 : 1;
}

sub install_licenses
{
	my \$licpath = shift;
	my %AI = get_application_info();
	my \$lictool = "\$AI{INSTROOT}/bin/kav4proxy-licensemanager";

	my \$appinfo = \$AI{"PATH_LICENSES"} . '/appinfo.dat';
	if ( ! -f \$appinfo ) {
		# create empty file
		if (open F, ">\$appinfo") {
		close F;
		}
	}
	klinstall::lazy_chown("\$KLUSER:\$KLGROUP", \$appinfo);
	chmod (0640, \$appinfo);

	opendir LICDIR, \$licpath or return "Could not open directory \$licpath: \$!";
	my @keyfiles = grep { /\\.key\$/i && -f "\$licpath/\$_" } readdir (LICDIR);
	closedir LICDIR;

	return "No license files (files with .key extension) were found in the directory '\$licpath'."
		if !@keyfiles;

	# Check all the key files for validity
	my (@validkeys, @errormsgs);

	foreach (@keyfiles)
	{
		# Check whether it is a KL key file and is valid
		my (\$rc, \$msg) = klinstall::exec_cmd ("\$lictool -q -k \$licpath/\$_");

		\$rc != 0 and push @errormsgs, "\$_ rejected - not a valid Kaspersky Lab license file."
			or \$msg !~ /\\bALLOWED=yes\\b/ and push @errormsgs, "\$_ rejected - not valid for this product."
				or push @validkeys, "\$licpath/\$_";
	}

	if ( !@validkeys )
	{
		klinstall::output ("\\n" . join ("\\n", @errormsgs));
		return "None of the Kaspersky Lab license files found is valid for this product.";
	}

	# Try to install the first good key from the list
	@errormsgs = ();
	foreach my \$keyfile (@validkeys)
	{
		\$keyfile =~ s/\\/+/\\//g; # remove extra /
		my (\$rc, \$msg) = klinstall::exec_cmd ("\$lictool -q -a \$keyfile");
		if ( \$rc == 0 )
		{
			klinstall::output ("The license file %1 has been installed.", \$keyfile);
			return;
		}

		push @errormsgs, "Error installing license file \$keyfile: \$msg";
	}

	klinstall::output ("\\n" . join ("\\n", @errormsgs)) if @errormsgs;
	return "None of the Kaspersky Lab license files found can be installed for this product.";
}

sub app_modify_config_file
{
	my %AI = appdata::get_application_info();
	klinstall::copy_file("\$AI{DEFAULT_CONFIG}.default", \$AI{'DEFAULT_CONFIG'})
	    if \$AI{'PKG_OS_VER'} =~ /^FreeBSD/i && ! -f \$AI{'DEFAULT_CONFIG'};
}

sub setup_http_proxy
{
	my \$addr = shift;
	my %AI = get_application_info();
	klinstall::warning ("Could not set up proxy address: ", klinstall::get_ini_error())
		if !klinstall::update_ini_file ( \$AI{"DEFAULT_CONFIG"}, "updater.options", ("ProxyAddress" => \$addr, "UseProxy" => "yes") );
}

sub update_AV_database
{
	my %AI = get_application_info();
	my \$updater_cmd = "\$AI{INSTROOT}/bin/kav4proxy-keepup2date -k -c \$AI{DEFAULT_CONFIG}";

	# Escape shell args
	\$updater_cmd =~ s/([\\&;\\\`'\\\\\\|"*?~<>^\\(\\)\\[\\]\\{\\}\\\$\\n\\r])/\\\\\$1/g; #'

	my \$retcode = system ("\$updater_cmd");

	system("sh \$AI{LIBEXEC}/setup/keepup2date.sh -prompt-install");
	
	# return code 1 is 256. perldoc -f wait
	return (defined \$retcode && (\$retcode eq 0 || \$retcode eq 256)) ? 1 : 0;
}

sub set_value
{
	my (\$ref, \$var, \$val) = @_;

	return if (\${\$ref} =~ s/^(#\$var\\s+.+)/\$1/m);
	return if (\${\$ref} =~ s/^\$var\\s+.+/\$var \$val/m);
}

sub add_or_replace
{
	my (\$ref, \$var, \$val) = @_;

	return if (\${\$ref} =~ s/^\$var\\s+.+/\$var \$val/m);
	return if (\${\$ref} =~ s/^(#\\s*\$var\\s+.+\\n(#.*\\n)*)/\$1\$var \$val\\n/m);

	return if (\${\$ref} =~ s/(\$CFGMARKER1.*\\n)(\$CFGMARKER2)/\$1\$var \$val\\n\$2/sm);
	\${\$ref} .= "\\n\${CFGMARKER1}\${var} \$val\\n\${CFGMARKER2}";
}

sub add_or_replace_named
{
	my (\$ref, \$var, \$name, \$val) = @_;

	return if (\${\$ref} =~ s/^\$var\\s+\$name\\s+.+/\$var \$name \$val/m);
	return if (\${\$ref} =~ s/^(#\\s*\$var\\s+.+\\n(#.*\\n)*)/\$1\$var \$name \$val\\n/m);

	return if (\${\$ref} =~ s/^(\$CFGMARKER1.*\\n)(\$CFGMARKER2)/\$1\$var \$name \$val\\n\$2/sm);
	\${\$ref} .= "\\n\${CFGMARKER1}\$var \$name \$val\\n\${CFGMARKER2}";
}

sub remove_named
{
	my (\$ref, \$var, \$name) = @_;

	\${\$ref} =~ s/^(#?\\s*\$var\\s+\$name\\s+.+\\n)//m;
}

sub find_installed_proxy
{
	my @found;

	my @squid_paths = (
		{
			'conf' => '/usr/local/squid/etc/squid.conf',
			'bin'  => '/usr/local/squid/sbin/squid',
		},
		{
			'conf' => '/usr/local/etc/squid/squid.conf',
			'bin'  => '/usr/local/sbin/squid',
		},
		{
			'conf' => '/etc/squid/squid.conf',
			'bin'  => '/usr/sbin/squid',
		},
	);

	# Find the squid proxy server the customer is using
 	foreach my \$paths (@squid_paths) {
		next if (! -x \$paths->{'bin'}) or (! -f \$paths->{'conf'});
		\$paths->{'type'} = 'squid';
		\$paths->{'title'} = 'Squid ('.\$paths->{'conf'}.')';
		push @found, \$paths;
	}

	return @found;
}

sub configure_squid
{
	my %APPINFO = appdata::get_application_info();
	
	my \$data = shift;

	return if klinstall::ask_boolean ('KAV4PROXY_CONFIRM_FOUND', 'Y', \$SETUPMSG_4, \${\$data}{'bin'}, \${\$data}{'conf'}) != 1;

	\$squidbinfile = \$data->{'bin'};
	\$squidcfgfile = \$data->{'conf'};
	
	if ( !open F, "<\$squidcfgfile" ) {
		klinstall::output ("Could not read the configuration file %1: %2\\n", \$squidcfgfile, \$!);
		return;
	}
	\$squidcfgcontent = join ('', <F>);
	close F;

	# Make a backup
	open F, ">\$squidcfgfile.kavbackup" or klinstall::fatal ("Could not create backup file \$squidcfgfile.kavbackup: \$!");
	print F \$squidcfgcontent;
	close F;

	my \$squid_ver = \`\$squidbinfile -v\`;
	\$squid_ver = (\$squid_ver =~ /Version\\s+(\\S+)/) ? \$1 : 'unknown';

	# Enable or add the ICAP options
	add_or_replace (\\\$squidcfgcontent, "icap_enable", "on");

	add_or_replace (\\\$squidcfgcontent, "icap_send_client_ip", "on");

	add_or_replace_named (\\\$squidcfgcontent, "icap_service", "is_kav_req",  "reqmod_precache 0 icap://localhost:1344/av/reqmod");
	add_or_replace_named (\\\$squidcfgcontent, "icap_service", "is_kav_resp", "respmod_precache 0 icap://localhost:1344/av/respmod");

	if (\$squid_ver =~ /^2\\./) {
		# workarround for bug in Squid 2.x - reqmod is not sent when it's class defined after respmod
		# but works stable if both defined in one class
		add_or_replace_named (\\\$squidcfgcontent, "icap_class",   "ic_kav", "is_kav_req is_kav_resp");
		add_or_replace_named (\\\$squidcfgcontent, "icap_access",  "ic_kav", "allow all");
	} else {
		add_or_replace_named (\\\$squidcfgcontent, "acl",		  "acl_kav_GET", "method GET");
		add_or_replace_named (\\\$squidcfgcontent, "icap_class",   "ic_kav_req",  "is_kav_req");
		add_or_replace_named (\\\$squidcfgcontent, "icap_class",   "ic_kav_resp", "is_kav_resp");

		add_or_replace_named (\\\$squidcfgcontent, "icap_access",  "ic_kav_resp", "allow all");
		add_or_replace_named (\\\$squidcfgcontent, "icap_access",  "ic_kav_req",  "allow all !acl_kav_GET");
	}

	open F, ">\$squidcfgfile" or klinstall::fatal ("Could not write the Squid configuration file \$squidcfgfile: \$!");
	print F \$squidcfgcontent;
	close F;

	my %appdata = klinstall::read_app_info (\$APPID);
	\$appdata{'SQUID_CONF'} = \$squidcfgfile;
	\$appdata{'SQUID_BIN'}  = \$squidbinfile;
	klinstall::write_app_info (\$APPID, %appdata);
	
	return 1;
}

sub configure_proxy_none_cb
{
	my \$data = shift;
	return;
}

sub configure_proxy_remote_cb
{
	my \$data = shift;

	my \$listen;

	my %APPINFO = appdata::get_application_info();

	require Socket;

	klinstall::output( \$SETUPMSG_REMOTE_HEAD);

	while (1) {
		\$listen = klinstall::ask_question('KAV4PROXY_SETUP_LISTENADDRESS', '0.0.0.0:1344', \$SETUPMSG_REMOTE_QUERY);

		return if (\$listen =~ /^\\s*CANCEL\\s*\$/i);

		if (\$listen =~ /^(.*):(\\d+)\$/) {
			my (\$host, \$port) = (\$1, \$2);

			my \$ip = Socket::inet_aton( \$host );

			if (!length(\$ip)) {
				klinstall::output("\\n'%1' is not valid hostname or IP-address!\\n", \$host);
				next;
			}

			if (\$port <= 0 or \$port > 65535) {
				klinstall::output("\\n'%1' is not valid port number!\\n", \$host);
				next;
			}

			last;
		} else {
			klinstall::output("\\n'%1' is not in valid format. Please specify in hostname:port format\\n", \$listen);
		}
	}

	klinstall::output(\$SETUPMSG_REMOTE_INFO,  \$listen);

	# Configure ListenAddress in section [icapserver.network]
	my \$ok = klinstall::update_ini_file ( \$APPINFO{"DEFAULT_CONFIG"}, "icapserver.network", ("ListenAddress" => \$listen) );
	if (\$ok) {
		return 1;
	} else {
		klinstall::warning ("Could not set up listen address: %1", klinstall::get_ini_error())
	}

	return;
}

sub configure_proxy_squid_manual_cb
{
	my \$data = shift;

	klinstall::output(\$SETUPMSG_SQUID_MANUAL_HEAD);

	my (\$squidcfgfile, \$squidbinfile);

	while ( 1 ) {
		\$squidcfgfile = klinstall::ask_question ('KAV4PROXY_SETUP_CONFPATH', "", \$SETUPMSG_6);
		\$squidcfgfile =~ s/\\s//g;

		return if ( !\$squidcfgfile );

		# check for given path exists
		if ( ! -f \$squidcfgfile ) {
			klinstall::output ("File not found: %1", \$squidcfgfile);
			next;
		}
	
		\$squidbinfile = klinstall::ask_question ('KAV4PROXY_SETUP_BINPATH', '', \$SETUPMSG_7);
		\$squidbinfile =~ s/\\s//g;
		if ( !\$squidbinfile ) {
			next;
		}

		# check for given path exists
		if ( ! -f \$squidbinfile || ! -x \$squidbinfile ) {
			klinstall::output ("File not found, or not an executable: %1", \$squidbinfile);
			next;
		}

		if ( !open F, "<\$squidcfgfile" ) {
			klinstall::output ("Could not read the configuration file %1: %2\\n", \$squidcfgfile, \$!);
			next;
		}
		\$squidcfgcontent = join ('', <F>);
		close F;

		\$squidcfgcontent =~ s/\\r//g;

		next if \$squidcfgcontent !~ /^#?\\s*icap_enable/m
			&& klinstall::ask_boolean ("KAVICAP_SETUP_NONICAPCFG", "N", \$SETUPMSG_3, \$squidcfgfile) == 0;

		last;
	}

	return configure_squid({
		'bin'  => \$squidbinfile,
		'conf' => \$squidcfgfile,
	});
}

sub configure_proxy_squid_cb
{
	my \$data = shift;
	return configure_squid(\$data);
}

sub configure_proxy
{
	my %APPINFO = appdata::get_application_info();

	my \$result = undef;
	my @found;

	push @found, {
		'type'  => 'none',
		'title' => 'No integration',
	};
	push @found, {
		'type'  => 'remote',
		'title' => 'Configure to work with remote proxy',
	};
	push @found, {
		'type'  => 'squid_manual',
		'title' => 'Configure Squid manually',
	};
	push @found, find_installed_proxy();

	klinstall::output (\$SETUPMSG_1);

	while (1) {
		for (\$i=0; \$i < scalar(@found); \$i++) {
			my \$data = \$found[\$i];
			klinstall::output("  <B>%1)</B> %2", \$i+1, \$data->{'title'});
		}

		my \$sel = klinstall::ask_question('KAV4PROXY_SETUP_TYPE', "", \$SETUPMSG_8, scalar(@found));

		if (\$sel =~ /^\\d+\$/ and \$sel > 0 and \$sel <= scalar(@found)) {
			my \$data = \$found[\$sel-1];
			my \$subname = 'configure_proxy_' . \${data}->{'type'} . '_cb';
			if (&{\$subname}(\$data)) {
				\$result = \$data;
			}
			last;
		} else {
			
		}
	}

	klinstall::output (\$SETUPMSG_INFO, \$APPINFO{'COMP_SETUP_SCRIPT'})
		if !\$result;

	return \$result;
}

sub app_register
{
	klinstall::check_for_root ("setup script");

	my \$upgrade = shift;
	my \$configured;

	if (!\$upgrade) {
	    \$configured = configure_proxy();
	}

	app_stop() if app_is_running();
	app_start();

	if (\$configured && defined \$configured->{'bin'}) {
		# reload Squid
		print "Reconfigure Squid - ";
		system (\$configured->{'bin'} . ' -k reconfigure');
		print "success\\n" if ( (\$? >> 8) == 0);
	}
}

sub unconfigure_squid
{
	my %appdata = klinstall::read_app_info (\$APPID);
	my \$squidcfgfile = \$appdata{'SQUID_CONF'};
	my \$squidbinfile = \$appdata{'SQUID_BIN'};

	return if !defined(\$squidcfgfile) or (\$squidcfgfile eq '');

	if ( open F, "<\$squidcfgfile" ) {
		my \$squidcfgcontent = join ("", <F>);
		close F;

		# Make a backup
		open F, ">\$squidcfgfile.kavremove" or klinstall::fatal ("Could not create backup file \$squidcfgfile.kavbackup: \$!");
		print F \$squidcfgcontent;
 		close F;

		set_value (\\\$squidcfgcontent, "icap_enable", "off");

		#remove all texts marked as added by installer
		\$squidcfgcontent =~ s/^(\\n\$CFGMARKER1.*\$CFGMARKER2)//sm;

		remove_named (\\\$squidcfgcontent, "acl",		  "acl_kav_GET");
		remove_named (\\\$squidcfgcontent, "icap_service", "is_kav_req");
		remove_named (\\\$squidcfgcontent, "icap_service", "is_kav_resp");
		remove_named (\\\$squidcfgcontent, "icap_class",   "ic_kav_req");
		remove_named (\\\$squidcfgcontent, "icap_class",   "ic_kav_resp");
		remove_named (\\\$squidcfgcontent, "icap_access",  "ic_kav_req");
		remove_named (\\\$squidcfgcontent, "icap_access",  "ic_kav_resp");

		# remove squid 2.x definitions
		remove_named (\\\$squidcfgcontent, "icap_class",   "ic_kav");
		remove_named (\\\$squidcfgcontent, "icap_access",  "ic_kav");

		if (open F, ">\$squidcfgfile") {
			print F \$squidcfgcontent;
			close F;
		}

		# reload Squid
		print "Reconfiguring Squid - ";
		system (\$squidbinfile . ' -k reconfigure');
		print "success\\n" if ( (\$? >> 8) == 0);
	} else {
		klinstall::output ("Could not read the configuration file %1: %2\\n", \$squidcfgfile, \$!);
	}
}

sub app_unregister
{
	my \$upgrade = shift;

	my %AI = appdata::get_application_info();

	if (not \$upgrade) {
	    system ("sh \$AI{LIBEXEC}/setup/keepup2date.sh -uninstall");
    	    unconfigure_squid();
	}
}

__DATA__
VER=5.5.39
INSTROOT=/opt/kaspersky/kav4proxy
RCCMD=/etc/init.d/kav4proxy
CFGPATH=/etc/opt/kaspersky
DBROOT=/var/opt/kaspersky
LIBSPATH=/opt/kaspersky/kav4proxy/lib
LIBEXEC=/opt/kaspersky/kav4proxy/lib/bin
SHAREPATH=/opt/kaspersky/kav4proxy/share
PKG_OS_VER=
PKG_OS_NAME=
BND1234567890ABCDEFGH_appdata.pm_EOF
# Run script
perl preinstall.pl rpm
exitcode=$?
cd /
rm -rf ${tempdir}
exit ${exitcode}

