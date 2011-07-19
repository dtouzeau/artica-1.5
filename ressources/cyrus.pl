#!/opt/artica/bin/perl -w

use Cyrus::IMAP::Admin;
my $cyrus_server = "localhost";
my $cyrus_user = "cyrus";
my $cyrus_pass = "CYRUSPASSWORD";

# 100 Megs
my $quota_size = "1000000";
my $mechanism = "login";



if (!$ARGV[0]) {
  die "Usage: $0 [user to add] passwd \n";
} else {
  $newuser = "$ARGV[0]";
  $newpasswd = "$ARGV[1]";    
}

sub createMailbox {

  my ($user, $subfolder) = @_;

  my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
  $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);

  if ($subfolder eq "INBOX") {
    $mailbox = "user.". $user;
  } else {
    $mailbox = "user.". $user .".". $subfolder;
  } 

  $cyrus->create($mailbox);
  if ($cyrus->error) {
    print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";
  } else {
    print "Created Mailbox: $mailbox \n";
  }

}

sub setQuota {

  my ($user) = @_;

  my $cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
  $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
  
  $mailbox = "user.". $user;
  $cyrus->setquota($mailbox,"STORAGE",$quota_size);
  if ($cyrus->error) {
    print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";
  } else {
    print "Setting Quota: $mailbox at $quota_size \n";
  }

}

print "Adding User: ", $newuser, "\n";

createMailbox($newuser,'INBOX');
createMailbox($newuser,'Sent');
createMailbox($newuser,'Trash');
createMailbox($newuser,'Drafts');
createMailbox($newuser,'Junk');

setQuota($newuser);

# This portion below will set a password for the user you wanted to 
# add.  

system "echo ". $newpasswd ." > .saslpass.tmp";
system "saslpasswd2 -p $newuser < .saslpass.tmp";
print "Generated Password: Completed \n";
unlink(".saslpass.tmp");

