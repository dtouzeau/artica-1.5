#!/usr/bin/perl -w

use Cyrus::IMAP::Admin;
my $cyrus_server = "127.0.0.1";
my $cyrus_user = "";
my $cyrus_pass = "";
my $command;
# 100 Megs
my $quota_size = "10000";
my $mechanism = "login";
my $newuser;
my $newpasswd;

if (!$ARGV[0]) {
  writeln("Usage: $0 -adduser [admin] [admin password] [newuser name] [newuser pass] [quota_size]\n");
  writeln("Usage: $0 -setquota [admin] [admin password] [user name] [quota_size]\n");  
die;
} else {
  $commandes="$ARGV[0]";
  $cyrus_user="$ARGV[1]";
  $cyrus_pass="$ARGV[2]";
  if(scalar(@ARGV)>3){
	$newuser = "$ARGV[3]";
  	$newpasswd = "$ARGV[4]";
  	}
  if(scalar(@ARGV)>5){
  	$quota_size = "$ARGV[5]";  
  }    
}

if($commandes eq "-adduser"){
	print "Adding User: ", $newuser, "\n";
	createMailbox($newuser,'INBOX');
	createMailbox($newuser,'Sent');
	createMailbox($newuser,'Trash');
	createMailbox($newuser,'Drafts');
	createMailbox($newuser,'Spam');	
	SetAcl($newuser);	
	setQuota($newuser);
}
if($commandes eq "-list"){
	&ListMailBoxes();
	}
	
if($commandes eq "-setquota"){
	print "Set quota User: ", $newuser, "\n";
	$quota_size = "$ARGV[4]";  
	setQuota($newuser);
	}	





sub createMailbox {

        my ($user, $subfolder) = @_;
		my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
        $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);

        if ($subfolder eq "INBOX") {
                $mailbox = "user/$user";
        } else {
                $mailbox = "user/$user/$subfolder";
        }

        $cyrus->create($mailbox);
        if ($cyrus->error) {
                print STDERR "Cyrus.pl -> createMailbox Error: ", $mailbox," ", $cyrus->error, "\n";
        } else {
                print "Cyrus.pl -> createMailbox Created Mailbox: $mailbox \n";
        }

}

sub setQuota {
	
  my ($user) = @_;
  print "Cyrus.pl -> setQuota settings quota " . $quota_size . " for " . $user . "\n";
  my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
  $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
  
  $mailbox = "user/". $user;
  $cyrus->setquota($mailbox,"STORAGE",$quota_size);
  if ($cyrus->error) {
    print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";
  } else {
    print "Setting Quota: $mailbox for $quota_size kb\n";
  }

}

sub SetAcl{
  my ($user) = @_;
  print "Cyrus.pl -> SetAcl settings for " . $user . "\n";
  my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
  $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
  $mailbox = "user/". $user;
  $cyrus->setacl($mailbox ,$cyrus_user, "all");
 if ($cyrus->error) {
    print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";
  } else {
    print "Setting acl: $mailbox\n";
  } 
}

sub GetQuotaRoot{
  my ($user) = @_;
  my $Quota_res;
  my $Quota_used;
  my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
  $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
  $mailbox = $user; 
  my ($root, %quota) = $cyrus->quotaroot($mailbox);
  my @currentquota = $cyrus->listquota($mailbox);
  
  if (not @currentquota) {
  $Quota_used=0;
	}else{
	$Quota_used=$currentquota[1]->[0];}
	$Quota_res=$quota{"STORAGE"}->[1];
	
	
  if(!$Quota_res){$Quota_res="0"};
  return $Quota_res . "/" . $Quota_used;
  
if ($cyrus->error) {
    print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";}   

}

sub ListMailBoxes(){
 my $boxname;
 #print("print list mailboxes..\n");
 my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);	
 $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
 my @list = $cyrus->list("user/*");
  foreach my $box (@list) {
	$boxname = $box->[0];
	print($boxname .   " " . GetQuotaRoot($boxname) . "\n");
	}
}





