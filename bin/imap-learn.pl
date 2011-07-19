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
$spamassassin_bin=spamass_bin_path();

if($ARGV[0] eq "--path"){
 if(imap_connect($ARGV[1])==1){
	  Writelogs("Success connect to remote imap server");
	} 
exit;}	


die();

if(!-e $spamassassin_bin){
	die('Cound not stat spamassassin');
}

if(!-e '/usr/bin/sa-learn'){
	die('Cound not stat /usr/bin/sa-learn');
}





opendir ( DIR, $ETC_DIR ) || die "Error in opening dir $ETC_DIR\n";
while( ($filename = readdir(DIR))){
     if($filename=~ m/SAAutoLearnAccount-(.+)/){
     	Writelogs("$filename");
	if(imap_connect($filename)==1){
	  Writelogs("Success connect to remote imap server\n");
	}
     }
}

}
# -----------------------------------------------------------------------------------------------------------------
sub imap_connect(){
my $ssl;
my $filename=shift;
my $folder;
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

$razorhome=readini('CONF','razorhome',$filename,'/etc/spamassassin/.razor');
$razorconf=readini('CONF','razorconf',$filename,'/etc/spamassassin/.razor/razor-agent.conf');

Writelogs("Configuration file.....: $filename");
Writelogs("Imap server............: $ImapServer");
Writelogs("spamassassin...........: $spamassassin_bin");
Writelogs("Imap username..........: $username");
Writelogs("Imap SSL...............: $UseSSL");
Writelogs("Enable for this mailbox: $enable");
Writelogs("Enable Spam learning...: $enable_spam");
Writelogs("Enable White list learn: $enable_ham");
Writelogs("Razor home dir.........: $razorhome");
Writelogs("Razor conf path........: $razorconf");
if(length($ImapServer)==0){return 0;}
if($enable==0){
Writelogs("This internal account has no parsing enabled.. Aborting");
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
				Debug =>0);
}else{
 $imap = Mail::IMAPClient->new( Server  => "$ImapServer:143",
                                User    => $username,
				Buffer	=> 4096*10,
                              	Password  => $password,Debug=>0,Timeout=>5);
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
	if($enable_spam==1){
		if($folder=~ m/^(Junk|Spam|SPAM|junk|spam|junk)/){
			Writelogs("Found spam folder named $folder");
	 		if(!$imap->select($folder)){
				Writelogs("Unable to enter into $folder folder");
			}else{
		  	   LearnSpam();
    			   $imap->expunge($folder);
			   #$imap->close($folder);
			}
		}

		if($folder=~ m/^INBOX\/(Junk|Spam|SPAM|junk|spam|junk)/){
			Writelogs("Found spam folder named $folder");
	 		if(!$imap->select($folder)){
			Writelogs("Unable to enter into $folder folder");
			}else{
		  		LearnSpam();
				$imap->expunge($folder);
				#$imap->close($folder);
			}
		}
	}


	if($enable_ham==1){
		if($folder=~ m/^INBOX\/(sent|Sent)/){
			Writelogs("Found \"sent\" folder named \"$folder\"");
			if(!$imap->select($folder)){
				Writelogs("Unable to enter into \"$folder\" folder");
			}else{
		  	   ParseSentMailBoxes();
			   $imap->close($folder);
			}
		}
		if($folder=~ m/^(sent|Sent)/){
			Writelogs("Found \"sent\" folder named \"$folder\"");
			if(!$imap->select($folder)){
				Writelogs("Unable to enter into \"$folder\" folder");
			}else{
		  	   ParseSentMailBoxes();
			   $imap->close($folder);
			}
		}

	}

}

$imap->logout();
Writelogs("Shutdown...");

exit;	
}
# --------------------------------------------------------------------------
sub LearnSpam(){
my @list=$imap->messages;
my $MID;
my $count=0;
my $max=scalar(@list);
Writelogs(scalar(@list)." messages in folder");
system("mkdir -p /tmp/train");
foreach $MID (@list){
   $count=$count+1;
   my $struct = $imap->get_bodystructure($MID);
   my $messageId = $imap->get_header($MID, "Message-Id") ;
   if(IsSpamScanned($messageId)==0){
	push(@arraySpam, lc($messageId));
    	Writelogs("Baeysian learning  message $messageId $count/$max ($MID)");
    	my $path = "/tmp/train/artica-salearn-$MID.msg";
    	my $msgtext = $imap->message_string($MID);
    	open(FILE,">$path");
    	print FILE $msgtext;
    	close(FILE);
    	Writelogs("Learn $path with sa-learn...");
    	system("/bin/cat $path|/usr/bin/sa-learn --spam");
    	if(-e '/usr/bin/razor-report'){
	    	Writelogs("Learn $path with razor...");
		system("cat $path|/usr/bin/razor-report -conf=$razorconf");
     		}
    	unlink($path);
	}else{
	  #Writelogs("Message $messageId already scanned");
	}
    Writelogs("Delete message id $MID ($count/$max)");
    $imap->delete_message($MID);

    
   }


WriteSpamCache(); 

}
# --------------------------------------------------------------------------
sub ParseSentMailBoxes(){
my $folder;
my $messageId;
my @list=$imap->messages;
my $count=0;
my $max=scalar(@list);

Writelogs("$max messages in folder Sent");

foreach my $msg (@list) {
  $count=$count+1;
  Writelogs("Scaning message $count/$max");
  my $struct = $imap->get_bodystructure($msg);
  my $date = $imap->get_header($msg,"Date");
  $messageId = $imap->get_header($msg, "Message-Id") ;
  my $cc=$imap->get_header($msg, "cc");
  my $To = $imap->get_header($msg, "To");
  my $from = FormatSMTPMail($imap->get_header($msg, "From"));
  if($cc){$To=$To.",$cc";}
  &SaveRecipient("$To,",$from);
  }

$max=scalar(@arraysent);
Writelogs("Saving cache file with $max entries in \"/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes\"");
if(scalar(@arraysent)>0){
	if(!open FH, ">/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes"){
		Writelogs("Could not open /etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes");
		exit;
	}
	foreach ( @arraysent){
		if(length($_)>0){
    			print FH $_."\n";
		}
	}
	close FH;

}

Writelogs("Parsing Sent mailbox Done..");

}
# --------------------------------------------------------------------------
sub IsSpamScanned(){
 my $msgid=shift;
 my $path="/etc/artica-postfix/imap-learn-$ImapServer-$username";
 my @myarrayfile; 
 if(!-e $path){return 0;}
 if(scalar(@arraySpam)==0){
	if(-e $path){
		 Writelogs("loading cache file $path");
		open(DAT, $path);
		@myarrayfile=<DAT>;
	   	close(DAT); 
		}	

	foreach my $id (@myarrayfile) {
		if(length(trim($id))>0){push(@arraySpam, lc($id));}	
		}
	}

  $msgid=trim(lc($msgid));
  foreach my $id (@arraySpam) {
 	if(trim(lc($id)) eq $msgid){	
		return 1;}
	}
 return 0;
}

# --------------------------------------------------------------------------
sub WriteSpamCache(){
 my $path="/etc/artica-postfix/imap-learn-$ImapServer-$username";
if(scalar(@arraySpam)==0){return 0;}

Writelogs("Saving cache file \"$path\"");
 if(!open FH, ">$path"){
	Writelogs("Could not open $path");
	return 0;
	}
 foreach ( @arraySpam){
	if(length($_)>0){
		print FH trim(lc($_))."\n";
		}
 	}
close FH;
}
# --------------------------------------------------------------------------
sub SaveRecipient(){
  my $mail=shift;
  my $from=shift;
  my @mails= split(/,/, $mail);
  foreach my $recipient (@mails) {
	$recipient=FormatSMTPMail($recipient);
	DatabaseSentMailBoxAdd($recipient,$from);
  }



}
# --------------------------------------------------------------------------
sub FormatSMTPMail(){
 my $recipient=shift;
 $recipient=lc(trim($recipient));
 if ($recipient=~ m/<(.+?)>/){
   $recipient=$1;
 }
return $recipient;
}
# --------------------------------------------------------------------------
sub DatabaseSentMailBoxAdd(){
    my $recipient=shift;
    my $from=shift;
    my $path="/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes";
    if(scalar(@arraysent)==0){
	if(-e $path){
	   	open(DAT, $path);
	   	@arraysent=<DAT>;
	   	close(DAT); 
	}	
     }
    if(scalar(@arraysent)==0){
	push(@arraysent, $recipient);
	Writelogs("Add a new white listed sender \"$recipient\" from Sent mailbox");
	return 0;
    	}

    foreach my $SavedMail (@arraysent) {
	if(trim($SavedMail) eq "$recipient:$from"){return 0;}
   	}

    push(@arraysent, "$recipient:$from");
	Writelogs("Add a new white listed sender \"$recipient\" from Sent mailbox");
	return 0;
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

$filename="/var/log/artica-postfix/artica-imap-learn-$curTimeStamp.debug";
$line="$CurrentDateTime [$ppid] [$username\@$ImapServer]: $line\n";
print($line);
if(!open FH, ">>$filename"){
	return 0;
	}


print FH $line;
close FH;
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







