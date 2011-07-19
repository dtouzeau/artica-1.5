#!/usr/bin/perl
use strict;
use warnings;
use Mail::IMAPClient;
use IO::Socket::SSL;
use Config::IniFiles;
use vars qw($imap $ETC_DIR $spamassassin_bin $ImapServer @arraysent $ppid $curTimeStamp $CurrentDateTime $username @arraySpam $razorhome $razorconf );

$ETC_DIR="/etc/artica-postfix/settings/Daemons";

# -----------------------------------------------------------------------------------------------------------------
sub usage(){
print "usage : -u [cyrus admin] -p [cyrus password] -m [mailbox] -q [quota] -a [acls](--delete)\n";
print "--list..........: list mailboxes\n";
print "--exists........: if a mailbox exists\n";
print "--quotag........: mailbox quota\n";
}
# -----------------------------------------------------------------------------------------------------------------
sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}
# -----------------------------------------------------------------------------------------------------------------
# -----------------------------------------------------------------------------------------------------------------
main();

# -----------------------------------------------------------------------------------------------------------------
# -----------------------------------------------------------------------------------------------------------------

sub main{
my $s;
my $filename;
$s = join(' ', @ARGV);

if($ARGV[0] eq "--path"){
	if(imap_connect($ARGV[1])==1){
	  Writelogs("Success connect to remote imap server");
	} 
}else{
	  Writelogs("No configuration file given ($s)");
}	

	  Writelogs("End testing account...");
die();
}
# -----------------------------------------------------------------------------------------------------------------
sub imap_connect(){
my $ssl;
my $filename=shift;
my $folder;

Writelogs("Reading configuration file $filename");
if(!-e $filename){
	$filename=$ETC_DIR."/$filename";
}

$ImapServer=readini('CONF','ImapServer',$filename,'');
$username=readini('CONF','username',$filename,'');
my $password=readini('CONF','password',$filename,'');
my $UseSSL=readini('CONF','UseSSL',$filename,'0');
my $enable=readini('CONF','enable',$filename,'0');
my $enable_spam=readini('CONF','enable_spam',$filename,'0');
my $enable_ham=readini('CONF','enable_ham',$filename,'0');

Writelogs("Configuration file.....: $filename");
Writelogs("Imap server............: $ImapServer");
Writelogs("Imap username..........: $username");
Writelogs("Imap SSL...............: $UseSSL");
if(length($ImapServer)==0){
	Writelogs("No imap server.. Aborting");
	return 0;
}
if(length($UseSSL)==0){$UseSSL=0;}

if($UseSSL==1){
	$ssl = IO::Socket::SSL->new (Proto=>'tcp',
                PeerAddr=>$ImapServer,
                PeerPort=>993);
	if(!defined $ssl){
		Writelogs("SSL says: " . <$ssl> . "");
		return 0;
	}
	
	

 $imap = Mail::IMAPClient->new( Server  => $ImapServer,
				Socket => $ssl,
                        	User    => $username,
                              	Password  => $password,
				Timeout=>5,
				Buffer	=> 4096*10,
				Debug =>1);
}else{
 $imap = Mail::IMAPClient->new( Server  => "$ImapServer:143",
                                User    => $username,
				Buffer	=> 4096*10,
                              	Password  => $password,Debug=>1,Timeout=>5);
}
Writelogs("Establish connexion....");
if(!$imap){
	Writelogs("Unable to connect...");
	return 0;
}else{
   Writelogs("Connexion success...");	
}


$imap->State ($imap->Connected);
Writelogs("Set State : " . $imap->State);
$imap->login();
Writelogs("Logged in $!");

if(!$imap->folders){
	Writelogs("Could not list folders: $@");
	return 0;
}



for my $f ( $imap->folders ) {
	$folder = $f;
	Writelogs("Folder:$f");
	}

$imap->logout();
Writelogs("Shutdown...");

exit;	
}
# --------------------------------------------------------------------------


sub Writelogs(){
my $line=shift;
my $filename;
my ($Sec,$Min,$Heure,$Mjour,$Mois,$Annee,$Sjour,$Ajour,$Isdst);
if(length($line)==0){return 0;}
if(!defined($curTimeStamp)){
	($Sec,$Min,$Heure,$Mjour,$Mois,$Annee,$Sjour,$Ajour,$Isdst) = localtime(time);
	$Annee += 1900;
	$Mois += 1;
	$Mois = 1 if ($Mois == 13);
	if(length($Heure)==1){$Heure="0$Heure";}
	if(length($Sjour)==1){$Sjour="0$Sjour";}
	if(length($Min)==1){$Min="0$Min";}
	if(length($Mois )==1){$Mois="0$Mois";}
	$curTimeStamp="$Annee-$Mois-$Sjour-$Heure";
}

$CurrentDateTime=ToDate();
if(!defined($ppid)){$ppid=getppid();}
if(!defined($ImapServer)){$ImapServer="Unknown";}
$line="$CurrentDateTime [$ppid] [$ImapServer]: $line\n";
print($line);

}
# --------------------------------------------------------------------------

sub readini{
	my $section=$_[0];
	my $key=$_[1];
	my $Path=$_[2];
	my $def=$_[3];
	my $value="";
	my $len=0;
	if(!$Path){return $def;}

	if(!-e $Path){
	   write("ERROR INI $Path\n");
	   return $def;

	}
	if(!$section){return $def;}
	if(!$key){return $def;}
	if(!$Path){return $def;}
	
	my $cfg=Config::IniFiles->new( -file => $Path );


	if(!$cfg){
		write("ERROR INI $Path\n");
		return $def;
	}
	$value=$cfg->val($section,$key,$def);

	if(!$value){
		return $def;
	}
	
return $value;
}
# --------------------------------------------------------------------------
sub spamass_bin_path(){
if( -e '/usr/bin/spamassassin'){
	return "/usr/bin/spamassassin";
	}
if( -e "/usr/local/bin/spamassassin"){
	return "/usr/local/bin/spamassassin";
	}
}
# --------------------------------------------------------------------------
sub ToDate{
my ($Sec,$Min,$Heure,$Mjour,$Mois,$Annee,$Sjour,$Ajour,$Isdst) = localtime(time);
$Annee += 1900;
$Mois += 1;
$Mois = 1 if ($Mois == 13);
if(length($Heure)==1){$Heure="0$Heure";}
if(length($Sjour)==1){$Sjour="0$Sjour";}
if(length($Min)==1){$Min="0$Min";}
if(length($Mois )==1){$Mois="0$Mois";}
if(length($Sec)==1){$Sec="0$Sec";}
return "$Annee-$Mois-$Sjour $Heure:$Min:$Sec"; 	
}
# --------------------------------------------------------------------------







