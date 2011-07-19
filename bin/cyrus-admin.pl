#!/usr/bin/perl

use Cyrus::IMAP::Admin;

my $cyrus;
my $cyrus_user;
my $cyrus_pass;
my $user;
my $mechanism="login";
my $cyrus_server="127.0.0.1";
my $acl="lrswipkxtecd";
my $acl_admin="lrswipkxtecda";
my $EnableVirtualDomainsInMailBoxes=0;


# -----------------------------------------------------------------------------------------------------------------
sub createMailbox {

        my ($user) = @_;
	$mailbox = "user/$user";

	print "Maintenance on Mailbox: \"$mailbox\" \n";
	
        $cyrus->create($mailbox);
        if ($cyrus->error) {
		if($cyrus->error=~/already\s+exists/){
		  print "Not necessary to create Mailbox: $mailbox Already exists\n";
		  CreateSubbox($user,'Sent');
		  CreateSubbox($user,'Trash');
		  CreateSubbox($user,'Drafts');
		  CreateSubbox($user,'Junk');
		  CreateAllacls($user);
		  return;
		}
                print "Error Creating Mailbox: \"$mailbox\" ". $cyrus->error."\n";
		return;
        } else {
                print "Created Mailbox: $mailbox \n";
        }

	

	$cyrus->setacl($mailbox, $cyrus_user, "lrswipkxtecda");
	if ($cyrus->error) {
                print "Error: set acl infos", $mailbox," ", $cyrus->error, "\n";
		return;
       		}
	CreateSubbox($user,'Sent');
        CreateSubbox($user,'Trash');
	CreateSubbox($user,'Drafts');
	CreateSubbox($user,'Junk');
	CreateAllacls($user);
	

}
# -----------------------------------------------------------------------------------------------------------------
sub CreateSubbox {

        my ($user,$subfolder) = @_;


		$mailbox = "user/$user/$subfolder";


	
	print "Maintenance on Mailbox: $mailbox \n";
	
        $cyrus->create($mailbox);
        if ($cyrus->error) {
		if($cyrus->error=~/already\s+exists/){
		  return;
		}
                print "Error Creating Mailbox: $subfolder ". $cyrus->error."\n";
        } else {
		SubscribeMbx($mailbox);
                print "Created Mailbox: $subfolder \n";
        }
}

# -----------------------------------------------------------------------------------------------------------------

sub SubscribeMbx{
#       my ($mailbox) = @_;
#       $cyrus->subscribe($mailbox);
#if ($cyrus->error) {
#            print "Error: ", $mailbox," ", $cyrus->error, "\n";
#	}else{
#	 print "Subscribe Mailbox: $mailbox success\n";
#	}
}
# -----------------------------------------------------------------------------------------------------------------

sub CreateAllacls{
        my ($user) = @_;
	
        $user=trim($user);
	if(length($user)<3){
	  die("user \"$user\" lenght is under 3, corrupted userid");
	}

		$mailbox = "user/$user";
		$sub_mailboxe="user/$user/";

	
      print "listing sub mailboxes $sub_mailboxe", "\n";
      my @l=$cyrus->list('*',$sub_mailboxe);
        if ($cyrus->error) {
                print "Error: unable to list", $mailbox," ", $cyrus->error, "\n";
		exit;
        }

	for ($i=0;$i<=$#l;$i++) {
		my $submailbox=$l[$i][0];
		$cyrus->setacl($submailbox,$user ,$acl);
		print "Mailbox:define acl $acl for $submailbox by $user\n";
 		
        	if ($cyrus->error) {
                	print "Error: ", $submailbox," ", $cyrus->error, "\n";
	        } else {
		SubscribeMbx($submailbox);
                print "success acl: $submailbox \n";
        	}	

	} 
     $cyrus->setacl($mailbox,$user,$acl);
     print "Mailbox:define acl $acl for master branch $mailbox by $user\n";
      	if ($cyrus->error) {
             print "Error: ", $mailbox," ", $cyrus->error, "\n";
	} else {
             print "success acl Mailbox: $mailbox \n";
        }	

}

# -----------------------------------------------------------------------------------------------------------------
sub CreateAcl{
    my ($user,$acl_data) = @_;
    $mailbox = "user/$user";
    $cyrus->setacl($mailbox, $user, $acl_data);
    

if ($cyrus->error) {
	print "Error: set acl ($acl_data) infos", $mailbox," ", $cyrus->error, "\n";
   }else{
	print "Success set acl on $mailbox ($acl_data) for $user\n";
}

$cyrus->setacl($mailbox, $cyrus_user, $acl_admin);
if ($cyrus->error) {
	print "Error: set acl ($acl_data) infos", $mailbox," ", $cyrus->error, "\n";
   }else{
	print "Success set acl on $mailbox ($acl_admin) for $cyrus_user\n";
}
}

# -----------------------------------------------------------------------------------------------------------------
sub userdelete {
	
        my ($user) = @_;
        $user=trim($user);
	if(length($user)<3){
	  die("user \"$user\" lenght is under 3, corrupted userid");
	}

	print "Mailbox:DELETE $user\n";
	$mailbox = "user/$user";
	my $sub_mailboxe="user/$user/";
      print "listing sub mailboxes $sub_mailboxe", "\n";
      my @l=$cyrus->list('*',$sub_mailboxe);
        if ($cyrus->error) {
                print "Error: unable to list", $mailbox," ", $cyrus->error, "\n";
		exit;
        }

	for ($i=0;$i<=$#l;$i++) {
		my $submailbox=$l[$i][0];
		$cyrus->setacl($submailbox,$cyrus_user ,$acl_admin);
		print "Mailbox:define acl $acl_admin for $submailbox by $cyrus_user\n";
 		$cyrus->delete($submailbox);
        	if ($cyrus->error) {
                	print "Error: ", $submailbox," ", $cyrus->error, "\n";
	        } else {
                print "Deleted Mailbox: $submailbox \n";
        	}	

	} 
     $cyrus->setacl($mailbox,$cyrus_user ,$acl_admin);
     print "Mailbox:define acl $acl_admin for master branch $mailbox by $cyrus_user\n";
     $cyrus->delete($mailbox);
      	if ($cyrus->error) {
             print "Error: ", $mailbox," ", $cyrus->error, "\n";
	} else {
             print "Deleted Mailbox: $mailbox \n";
        }	

}
# -----------------------------------------------------------------------------------------------------------------
sub setQuota {

        my ($user,$quota_size) = @_;
        $mailbox = "user/$user";

	if($quota_size == 0){
		DeleteQuota($user);
		return;
	}
	$quota_size=$quota_size*1024;
	$cyrus = Cyrus::IMAP::Admin->new($cyrus_server);
	$cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
        $cyrus->setquota($mailbox,'STORAGE', $quota_size);
        if ($cyrus->error) {
                print "Error Setting Quota: $mailbox for $quota_size kb, Server return:\"". $cyrus->error. "\"\n";
        } else {
                print "Setting Quota: $mailbox at $quota_size Kb\n";
        }
}
# -----------------------------------------------------------------------------------------------------------------
sub DeleteQuota{
	my ($user) = @_;
	$mailbox = "user/$user";
	$cyrus->setquota($mailbox);
	if ($cyrus->error) {
          print "Error remove Quota: $mailbox Server return:\"". $cyrus->error. "\"\n";
        } else {
          print "remove Quota: $mailbox\n";
        }
}
# -----------------------------------------------------------------------------------------------------------------
sub listm {
my @k;
      my @l=$cyrus->list('%','user/');
        if ($cyrus->error) {
                print "Error: ", $mailbox," ", $cyrus->error, "\n";
		exit;
        }

	for ($i=0;$i<=$#l;$i++) {
		print $l[$i][0]."\n";
	} 

}
# -----------------------------------------------------------------------------------------------------------------
sub lmexists {
my @k;
my $mailbox;
      my @l=$cyrus->list('%',"user/$user");
        if ($cyrus->error) {
                print STDERR "Error: ", $mailbox," ", $cyrus->error, "\n";
		exit;
        }

	for ($i=0;$i<=$#l;$i++) {
		$mailbox=$l[$i][0];
		if($mailbox=="user/$user"){
			print("TRUE ($user)\n");
			exit;
		}
	} 

}
# -----------------------------------------------------------------------------------------------------------------
sub getquotam{

my @currentquota = $cyrus->listquota("user/$user");

if (not @currentquota) {die "ERROR:No quota set\n";}
print "USED: @currentquota[1]->[0]\n";
print "MAX: @currentquota[1]->[1]\n";
}
# -----------------------------------------------------------------------------------------------------------------

sub transformMailboxDomain($$){
my $user=shift;
my $folder=shift;
my $domain='';
if(length($folder)>0){$folder='/'.$folder;}
if($user=~/(.+?)\@(.+)/){
	$user=$1;
	$domain=$2;	 
}

$domain=~ s/\./\//;
return "$domain$folder!user/$user";
}

sub main{
	my $s,$a,$q,$acl,$res;
	$s = join(' ', @ARGV);
	my $cydomain=`hostname -d`;
	$cydomain=trim($cydomain);
	if($s=~/--help/){print "\n-----------\nDomain:$cydomain\n help:\n";usage();exit;}
	if($s=~/-u\s+([a-zA-Z0-9_\-\@\.]+)/){$cyrus_user=$1;}
	if($s=~/-p\s+([a-zA-Z0-9_\-\.\@]+)/){$cyrus_pass=$1;}
	if($s=~/-m\s+([a-zA-Z0-9_\-\.\@]+)/){$user=$1;}
	if($s=~/-q\s+([0-9]+)/){$q=$1;}
	if($s=~/-a\s+([a-z]+)/){$acl=$1;}

	$EnableVirtualDomainsInMailBoxes=GET_INFOS('EnableVirtualDomainsInMailBoxes');
#cheramy/name!user/m.cheramy 
	
	if($EnableVirtualDomainsInMailBoxes==1){
		if(length($cydomain)>0){
			#$cyrus_user="$cyrus_user\@$cydomain";
		}
	}
	print "using $cyrus_user on server $cyrus_server ($mechanism)\n"; 
	$cyrus = Cyrus::IMAP::Admin->new($cyrus_server);

	$res=$cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass);
	print "Authenticate has $cyrus_user -> $res\n";
	if($res!=1){
       	 	if ($cyrus->error) {
                	print STDERR "Error: connect ($mechanism) $cyrus_user:*****@127.0.0.1:imap ", $cyrus->error, "\n";
        		exit;
		}
	print STDERR "Failed to connect...\n";
	exit;
	}

	my $ll=length($user);
	if($s=~/--list/){listm();exit;}

	if($s=~/--exists/){lmexists();exit;}

	if($s=~/--quotag/){getquotam();exit;}

	if($s=~/--forcemulti/){$EnableVirtualDomainsInMailBoxes=1;}

	if($s=~/--multi/){
		print transformMailboxDomain($user,"")."\n";
		print transformMailboxDomain($user,"Sent")."\n";
		print transformMailboxDomain($user,"Trash")."\n";
		exit;
	}

	if($s=~/--delete/){
		if( $ll !=0){
			userdelete($user);
			exit;
		}
	}


	if( $ll !=0){
		createMailbox($user);
		}else{
		print "$ll\n";
		usage();exit;
		}

	if(length($q)!=0){
		setQuota($user,$q);
	}

	if(length($acl)!=0){
		CreateAcl($user,$acl);
	}

}
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
sub GET_INFOS($){
my($key) = @_;
my $data_file="/etc/artica-postfix/settings/Daemons/$key";
if(!-e $data_file){
	return "";
	}
if(!open(DAT, $data_file)){
	return "";
	}
my @raw_data=<DAT>;
close(DAT);
foreach my $line_value (@raw_data){
	chomp($line_value);
	if(length($line_value)>0){
		return trim($line_value);
	}
} 
	
}
# -----------------------------------------------------------------------------------------------------------------

main();
