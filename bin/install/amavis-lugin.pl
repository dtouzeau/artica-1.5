package Amavis::Custom;
use strict;
use Net::LDAP;
use Config::IniFiles;
use Geo::IP;
use File::Copy;
use File::stat;
use vars qw($country_name $region_name $city $ldap_server $ldap_server_port $ldap_admin $suffix $ldap_password $message_id $search_dn);

BEGIN {
  import Amavis::Conf qw(:platform :confvars c cr ca $myhostname);
  import Amavis::Util qw(do_log untaint safe_encode safe_decode);
  import Amavis::rfc2821_2822_Tools;
  import Amavis::Notify qw(build_mime_entity); 


}


sub new {
  my($class,$conn,$msginfo) = @_;
  bless {}, $class;


}

sub before_send {
  my($self,$conn,$msginfo) = @_;
  &MysqlInjection($msginfo);
  $self;
}

sub checks {
   #$r->recip_addr_modified($new_addr);  # replaces delivery address!  
  my($self,$conn,$msginfo) = @_;
  my($client_ip) = $msginfo->client_addr;
  my $Mustbanned=0;
  my $MustSpam=0;
  my $bann_reason='';
  my $sender_domain='';
  my($log_id)=$msginfo->log_id;
  my($tempdir) = $msginfo->mail_tempdir;
  my $recipient_domain;
  my($received)=$msginfo->get_header_field_body('received'); 
  my($kasrate)= trim($msginfo->get_header_field_body('X-SpamTest-Rate'));
  my($mail_file_name) = $msginfo->mail_text_fn; 
  if(!$kasrate){$kasrate=0;}
  my($sender_address)=$msginfo->sender; 
  $message_id=Mydecode($msginfo->get_header_field_body('message-id'));
  $message_id=~ s/<//g;
  $message_id=~ s/>//g;
  my $ldap=init_ldap_settings();
  my $userid;

  my $AutoCompressFeature=GET_INFOS("AutoCompressEnabled");

$msginfo->header_edits->add_header('X-Artica-scanner','artica version '.&GET_VERSION_ARTICA());

my $last_recived=tests_from_received($received);

($country_name,$region_name,$city)=ScanGeoIP($last_recived);

$sender_address=~ m/(.+)?@(.+)/;
$sender_domain=$2;
 
  do_log(0, "%s artica-plugin: client's IP [%s], last [%s] sender: <%s>, X-SpamTest-Rate: %s",$message_id,$client_ip,$last_recived,$sender_address,$kasrate);
  do_log(0, "%s artica-plugin: Country [%s], Region [%s] City [%s] Auto-compress:[%s]",$message_id,$country_name,$region_name,$city,$AutoCompressFeature);


if(&AllowInternet($msginfo,$ldap)==1){
 $bann_reason="NO OVER INTERNET";
 $Mustbanned=1;
 $MustSpam=0;
}


if($kasrate>90){$MustSpam=1;}

if($MustSpam==1){
 for my $r (@{$msginfo->per_recip_data}) {
     if(!$r->recip_whitelisted_sender){
     	$r->add_contents_category(CC_SPAM,0) if !$r->bypass_spam_checks;
      }
   }
   $msginfo->add_contents_category(CC_SPAM,0);
 }

if($Mustbanned==1){
     my $new_addr="spam-quarantine";
     Amavis::do_quarantine($conn, $msginfo, undef,[$new_addr], 'local:all-%m.gz');

  for my $r (@{$msginfo->per_recip_data}) {

     $r->add_contents_category(CC_BANNED,0) if !$r->bypass_banned_checks;
     $r->banning_reason_short($bann_reason);
     $r->banned_parts(['MAIL']);
   }
   $msginfo->add_contents_category(CC_BANNED,0);
 }

htmlSize($msginfo,$ldap);


if($AutoCompressFeature==1){
	do_log(1, "%s artica-plugin: detect parts...",$message_id);
	if(AutoCompressDetect($msginfo)==1){
	  mkdir "/tmp/compress";
	  copy($mail_file_name, "/tmp/compress/$message_id.eml") or die "File cannot be copied.";
	  do_log(0, "%s artica-plugin: %s is saved into compress queue [tmp/compress] for compressing operation",$message_id);
 	  Amavis::load_policy_bank('killAll');
 	  return 0;
	 }
 }

$ldap->unbind();
$self;
};
# -----------------------------------------------------------------------------------------------------------------
sub AllowInternet($$){
  my($msginfo,$ldap)=@_;
  my($sender_address)=$msginfo->sender; 
  my $recipient_domain=""; 
  my $AllowedSMTPTroughtInternet=UserDatas("AllowedSMTPTroughtInternet",$sender_address,$ldap); 
  for my $r (@{$msginfo->per_recip_data}) {
	my($recip) = $r->recip_addr;
	$recip=~ m/(.+?)@(.+)/;
	$recipient_domain=$2;
	if($AllowedSMTPTroughtInternet==0){
		if(IsLocalDomain($recipient_domain,$ldap)==0){
		   do_log(0, "%s artica-plugin: domain: %s is External, and %s is not allowed sending trought internet, BANN the message",$message_id,$recipient_domain,$sender_address);
		   #Amavis::load_policy_bank('killAll');
		   return 1;
		 }else{do_log(0, "%s artica-plugin: AllowInternet: %s recipient domain is a local domain",$message_id,$recipient_domain);}
	}

   }
return 0;}
# -----------------------------------------------------------------------------------------------------------------
sub htmlSize($$){
  my($msginfo,$ldap)=@_;
  my $uid;
  my $ou;
  my @res;
  my @rules;
  my %attachments;
  my($sender_address)=$msginfo->sender;
  
  %attachments=ParseAttachParts($msginfo);

 if(keys(%attachments)==0){
	return 0;
	}

 for my $r (@{$msginfo->per_recip_data}) {
	my($recip) = $r->recip_addr;
	$uid=GetUidFromMail($recip,$ldap);
	if(length($uid)>0){
		$search_dn=~ m/cn=$uid,ou=(.+?),/;
		my $ou=$1;
		@res=GetLdapEntries("cn=html_blocker,ou=klf,$suffix","BigMailHTMLEnabled",$ldap);
		@rules=GetLdapEntries("cn=html_blocker,ou=klf,$suffix","BigMailHtmlRules",$ldap);
		if(scalar(@res)>0){
			if($res[0] eq "yes"){
			  	if(scalar(@rules)>0){
					do_log(1, "%s artica-plugin: htmlSize (parse rules)...",$message_id);
			  	}
			}
		}
	 }
    }

}
sub ParseHtmlRules($$){


}


# -----------------------------------------------------------------------------------------------------------------
sub ParseAttachParts($$){
  my($msginfo)=@_;
  my($tempdir) = $msginfo->mail_tempdir;  # working directory for this process
  my $part_path;
  my $size;
  my $ext;
  my($parts_root)=$msginfo->parts_root;
  my($top) = $parts_root->children;
  my($tempdir)=$msginfo->mail_tempdir;
  my $attachments;
    for my $e (!defined $top ? () : @$top) {
      my($name) = $e->name_declared;
      my($tshort)=$e->type_short;
      my($base_name)=$e->base_name;
      my($type_long)=$e->type_long;
      my($top2) = $e->children;
      for my $b (!defined $top2 ? () : @$top2) {
	  my($name) = $b->name_declared;
      	  my($tshort)=$b->type_short;
      	  my($base_name)=$b->base_name;
      	  my($type_long)=$b->type_long;
	 if(length($name)>0){
	        $name=~ m/\.(.+?)$/;
	        $ext=$1;
		$part_path="$tempdir/parts/$base_name";
	        $size=stat($part_path)->size;
		if(-e $part_path){
			$attachments->{$base_name}->{'ext'}=$ext;
			$attachments->{$base_name}->{'size'}=$size;
		}
	    }
         }
     }

return $attachments;
}


sub AutoCompressDetect($$){
  my($msginfo)=@_;
  my($tempdir) = $msginfo->mail_tempdir;  # working directory for this process
  my($mail_file_name) = $msginfo->mail_text_fn;
  my @exts=GET_INFOS_ARRAY("AutoCompressExtensions");
  my $count_ext=scalar(@exts);
  my $size;
if ($msginfo->mime_entity->parts > 0){
   for (my $i=0;$i<$msginfo->mime_entity->parts;$i++){
	my $subEntity = $msginfo->mime_entity->parts($i);
	my $head = $msginfo->mime_entity->parts($i)->head;
	my $filename = $head->recommended_filename;

	do_log(1, "%s artica-plugin: auto-compress: part %s : name:%s (%s extensions to check)",$message_id,$i,$filename,$count_ext);
	foreach my $extension (@exts){
		chomp($extension);
		$extension=trim($extension);
		if(length($extension)>0){
			do_log(1, "%s artica-plugin: auto-compress: ext:[%s]",$message_id,$extension);
	   		if ($filename=~m/\.$extension$/){
				do_log(0, "%s artica-plugin: auto-compress: part %s : %s effective: %s name:%s",$message_id,$i,$subEntity->mime_type,$subEntity->effective_type,$filename);
				return 1;
			   }
		}
	}
   }
 }else{
   do_log(1, "%s artica-plugin: auto-compress: 0 detected part",$message_id);
}

return 0;
}
# -----------------------------------------------------------------------------------------------------------------
sub UserDatas($$$){
my ($tofind,$email,$ldap)=@_;
my  $mesg = $ldap->search(
    base   => 'dc=klf,dc=fr',
    scope  => 'sub',
    filter => "(&(objectClass=userAccount)(mail=$email))"
  );
$mesg->code && die $mesg->error;
  foreach my $entry ($mesg->all_entries) {
    $search_dn=$entry->dn;
    foreach my $attr ($entry->attributes) {
        foreach my $value ($entry->get_value($attr)) {
		if($attr eq $tofind){
			 #print "$attr: $value\n";
			 return $value;
		}
	}
    }	
  } 
}
# -----------------------------------------------------------------------------------------------------------------

sub ScanGeoIP($){
  my ($last_recived)=@_;
  my $country_name="undefined";  
  my $region_name="undefined";
  my $city="undefined";
  
  if(-e "/usr/local/share/GeoIP/GeoLiteCity.dat"){
   	my $gi = Geo::IP->open("/usr/local/share/GeoIP/GeoLiteCity.dat", GEOIP_STANDARD);
	my $record = $gi->record_by_addr($last_recived);
  	$country_name=$record->country_name if $record;
  	$region_name=$record->region_name if $record;
  	$city=$record->city if $record;
  }else{
	do_log(0, "%s artica-plugin: GeoIP, unable to stat /usr/local/share/GeoIP/GeoLiteCity.dat",$message_id);
  }
 
 return ($country_name,$region_name,$city);

}
# -----------------------------------------------------------------------------------------------------------------
sub MysqlInjection($){
    my($msginfo) = @_;
    my $EnableMysqlFeatures=GET_INFOS('EnableMysqlFeatures');
    my $recipient_domain; 
    my $sender_domain;
    my $sql_query;
    if($EnableMysqlFeatures==0){return;}
    my $sql=init_mysql_settings();
    if(!$sql){return;}

    my($client_ip) = $msginfo->client_addr;
    my($sender_address)=$msginfo->sender;
    my($subject)=Mydecode($msginfo->get_header_field_body('subject'));
    my($from) =Mydecode($msginfo->get_header_field_body('from'));
   
   #retreive sender domain:
    $sender_address=~ m/(.+)?@(.+)/;
    $sender_domain=$2;

    my($received)=$msginfo->get_header_field_body('received');
    my $last_recived=tests_from_received($received);

  my($infected) = $msginfo->is_in_contents_category(CC_VIRUS);
  my($banned)   = $msginfo->is_in_contents_category(CC_BANNED);
  my($at_tag2)  = $msginfo->is_in_contents_category(CC_SPAMMY); # >= tag2_level
  my($at_kill)  = $msginfo->is_in_contents_category(CC_SPAM);   # >= kill_level
  do_log(1, "artica-plugin:Subject=(%s)",$subject);
 	$subject=MysqlQuote($subject);
     for my $r (@{$msginfo->per_recip_data}) {
	my($recip) = $r->recip_addr;
	$recip=~ m/(.+)?@(.+)/;
	$recipient_domain=$2;
        #Build sql query.
	$sql_query="INSERT INTO mails_events (`mailfrom`,`mailfrom_domain`,`rcpt_to`,`rcpt_to_domain`,`relayhost`,`message_id`,`Country`,`Region`,`City`,`infected` ,`spammy`,";
	$sql_query=$sql_query."`spam`,`banned`,`zDate`,`subject`) VALUES('$sender_address','$sender_domain','$recip','$recipient_domain','$client_ip',";
	$sql_query=$sql_query."'$message_id','$country_name','$region_name','$city','$infected', '$banned', '$at_tag2', '$at_kill',NOW(),'$subject')";
	my $sth = $sql->prepare($sql_query);
	if(!$sth->execute()){
	do_log(0, "%s artica-plugin: Error mysql %s %s",$message_id,$sql->err,$sql->errstr);
	$sql->disconnect();
	}

      }

 
     do_log(0, "%s artica-plugin: Mysqlinjection: infected:%s, banned:%s, spammy:%s, spam:%s",$message_id,$infected, $banned, $at_tag2, $at_kill);

    $sql->disconnect();



}
# -----------------------------------------------------------------------------------------------------------------
sub MysqlQuote($){
my $value=shift;
$value=~ s/\'/\'\'/g;
return $value;
}
# -----------------------------------------------------------------------------------------------------------------
sub Mydecode {
  my($str) = shift;
  return unless defined $str or $str ne '';
  chomp $str;
  $str =~ s/\n([ \t])/$1/sg; $str =~ s/^[ \t]+//s; $str =~ s/[ \t]+\z//s;
  my($str2);
  eval { $str2 = safe_decode('MIME-Header',$str) };
  $@ eq '' ? return $str2 : return $str;
}



sub IsLocalDomain($$){
my ($tofind,$ldap)=@_;	
$tofind=lc ($tofind);

my  $mesg = $ldap->search(
    base   => $suffix,
    scope  => 'sub',
    filter => "(&(objectclass=domainRelatedObject)(associatedDomain=$tofind))",
     attrs  =>['associatedDomain']
  );
 $mesg->code && die $mesg->error;
 
  foreach my $entry ($mesg->all_entries) {
	foreach my $attr ($entry->attributes) {
		foreach my $value ($entry->get_value($attr)) {
			if (lc ($value)  eq $tofind){
			        do_log(1, "artica-plugin: IsLocalDomain: (&(objectclass=domainRelatedObject)(associatedDomain=%s))=%s",$value,$tofind);
				return 1;
			}
		}
}	}
return IsTargetedDomain($tofind,$ldap);
}
# -----------------------------------------------------------------------------------------------------------------
sub IsTargetedDomain($$){
my ($tofind,$ldap)=@_;	
$tofind=lc ($tofind);

my  $mesg = $ldap->search(
    base   => $suffix,
    scope  => 'sub',
    filter => "(&(objectclass=transportTable)(cn=$tofind))",
     attrs  =>['cn']
  );
 $mesg->code && die $mesg->error;
 
  foreach my $entry ($mesg->all_entries) {
	foreach my $attr ($entry->attributes) {
		foreach my $value ($entry->get_value($attr)) {
			if (lc ($value)  eq $tofind){
     			        do_log(1, "artica-plugin: IsTargetedDomain: (&(objectclass=transportTable)(cn=%s))=%s",$value,$tofind); 	
				return 1;
			}
		}
}	}
return 0;}
# -----------------------------------------------------------------------------------------------------------------
sub init_ldap_settings(){
	my $fileset;
	my $admin;
	$fileset="/etc/artica-postfix/artica-postfix-ldap.conf";
	if(!$ldap_server){$ldap_server =readini("LDAP","server",$fileset,"127.0.0.1");}
	if(!$ldap_server_port){ $ldap_server_port=readini("LDAP","port",$fileset,"389");}
	if(!$suffix){$suffix=readini("LDAP","suffix",$fileset,"");}
	if(!$admin){$admin=readini("LDAP","admin",$fileset,"");}
	if(!$ldap_password){$ldap_password=readini("LDAP","password",$fileset,"");}		
	if(!$ldap_admin){$ldap_admin="cn=$admin,$suffix";}
	my $ldap = Net::LDAP->new($ldap_server , $ldap_server_port => 389, version => 3)  or die "unable to connect to $ldap_server";
	my $results=$ldap->bind ( $ldap_admin, password => $ldap_password);
	$results->code && die $results->error;		
	return $ldap;
	}
# -----------------------------------------------------------------------------------------------------------------
sub init_mysql_settings(){
	my $fileset;
	my $suffix;
	my $admin;
	$fileset="/etc/artica-postfix/artica-mysql.conf";
	my $sql_serv=GET_INFOS_MYSQL("mysql_server");
	my $admin=GET_INFOS_MYSQL("database_admin");
	my $password=GET_INFOS_MYSQL("database_password");	
	my $dsn = "DBI:mysql:database=artica_events;host=$sql_serv";
	my $dbh = DBI->connect($dsn, $admin, $password) || warn "Could not connect to database: $DBI::errstr";
	return $dbh;
	}

# -----------------------------------------------------------------------------------------------------------------
sub readini{
	my $section=$_[0];
	my $key=$_[1];
	my $Path=$_[2];
	my $def=$_[3];
	my $value="";
	my $len=0;
	if(!$Path){return $def;die();}
	if(!-e $Path){$def;die();}
	if(!$section){return $def;die();}
	if(!$key){return $def;die();}
	if(!$Path){return $def;die();}
	
	my $cfg=Config::IniFiles->new( -file => $Path );
	  if($cfg->SectionExists($section)){
		$value=$cfg->val($section,$key,$def);
		if(!$value){return $def;die();}
	}
return $value;
}

# -----------------------------------------------------------------------------------------------------------------
sub tests_from_received($) {
  my($received) = @_;
  my($fields_ref) = parse_received($received);
  my($ip); local($1);
  for (grep {defined} (@$fields_ref{qw(from-tcp from from-com)})) {
    if (/ \[ (\d{1,3} (?: \. \d{1,3}){3}) (?: \. \d{4,5} )? \] /x) {
      $ip = $1;  last;
    } elsif (/\[ [^\]]* : [^\]]* \]/x &&  # triage, must contain a colon
             /\[ ( (?: IPv6: )?  [0-9a-f]{0,4}
                   (?: : [0-9a-f]{0,4} | \. [0-9]{1,3} ){2,9} ) \]/xi) {
      $ip = $1;  last;
    } elsif (/ (?: ^ | \D ) ( \d{1,3} (?: \. \d{1,3}){3}) (?! [0-9.] ) /x) {
      $ip = $1;  last;
    }
  }
  !defined($ip) ? undef : $ip;  # undef need not be tainted
  return $ip;
}
# -----------------------------------------------------------------------------------------------------------------
sub GET_INFOS($){
my($key) = @_;
my $data_file="/etc/artica-postfix/settings/Daemons/$key";
open(DAT, $data_file) || die("Could not open file!");
my @raw_data=<DAT>;
close(DAT);
foreach my $line_value (@raw_data){
	chomp($line_value);
	if(length($line_value)>0){
		return $line_value;
	}
} 
	do_log(0, "artica-plugin: key [%s] has no datas",$key);
}
# -----------------------------------------------------------------------------------------------------------------
sub GET_INFOS_ARRAY($){
my($key) = @_;
my @Array;
my $data_file="/etc/artica-postfix/settings/Daemons/$key";
if(!-e $data_file){return @Array;}
open(DAT, $data_file) || die("Could not open file!");
my @raw_data=<DAT>;
close(DAT);
return @raw_data;
}
# -----------------------------------------------------------------------------------------------------------------
sub GET_INFOS_MYSQL($){
my($key) = @_;
my $data_file="/etc/artica-postfix/settings/Mysql/$key";
open(DAT, $data_file) || die("Could not open file!");
my @raw_data=<DAT>;
close(DAT);
foreach my $line_value (@raw_data){
chomp($line_value);
if(length($line_value)>0){
	return $line_value;}
}
if($key eq "mysql_server"){return "127.0.0.1";}
}
# -----------------------------------------------------------------------------------------------------------------
sub GET_VERSION_ARTICA(){
my $data_file="/usr/share/artica-postfix/VERSION";
open(DAT, $data_file) || die("Could not open file!");
my @raw_data=<DAT>;
close(DAT);
foreach my $line_value (@raw_data){
chomp($line_value);
if(length($line_value)>0){
	return $line_value;}
}
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

sub ReadFile($){
my $path = shift;
my $string;
open FILE, $path or die "Couldn't open file: $!"; 
while (<FILE>){
 $string .= $_;
}
close FILE;
return $string;

}
# -----------------------------------------------------------------------------------------------------------------
sub GetUidFromMail($$){
	my ($email,$ldap)=@_;
	my $uid;
	my $tmpuid=0;
	$uid=UserDatas("uid",$email,$ldap);
	if(length($uid)>0){return $uid;}
	my  $mesg = $ldap->search(
    		base   => $suffix,
    		scope  => 'sub',
    		filter => "(&(objectClass=userAccount)(mailAlias=$email))"
  		);	
 $mesg->code && die $mesg->error;
  foreach my $entry ($mesg->all_entries) {
    $search_dn=$entry->dn;
    foreach my $attr ($entry->attributes) {
        foreach my $value ($entry->get_value($attr)) {
	    if($attr eq "mailAlias"){if($value eq $email){$tmpuid=1;}}
	}
    }	
  }

if($tmpuid==1){
	foreach my $entry ($mesg->all_entries) {
    		foreach my $attr ($entry->attributes) {
        		foreach my $value ($entry->get_value($attr)) {
			   if($attr eq "uid"){return $value;}
			}
		}
	}
 }


}
# -----------------------------------------------------------------------------------------------------------------
sub GetLdapEntries(){
my ($dn,$field,$ldap)=@_;
my @res;
my  $mesg = $ldap->search(
    base   => $dn,
    scope  => 'sub',
    filter => "(objectClass=*)"
  );

$mesg->code && die $mesg->error;
  foreach my $entry ($mesg->all_entries) {
    foreach my $attr ($entry->attributes) {
        foreach my $value ($entry->get_value($attr)) {
	    if($attr eq $field){
		push(@res, $value);
	    }
	}
    }	
  }

return @res;
}
# -----------------------------------------------------------------------------------------------------------------


1;  # insure a defined return

