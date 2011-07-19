<?php

function QUERY_SQL($sql,$function=null,$file=null){
	if(!is_file(dirname(__FILE__) . "/settings.inc")){
		die('<code>Unable to locate ' . dirname(__FILE__) . "/settings.inc" . " please make sure artica-postfix daemon running on this computer'");
	}
	
	
	include(dirname(__FILE__) . "/settings.inc");
	$Database="/usr/share/artica-postfix/LocalDatabases/artica_database.db";
	
	
	writelogs("$function() in $file: #$Database#",__FUNCTION__,__FILE__);
	$bd=sqlite_open($Database, 0666, $error);
	
	if(!$bd){
		writelogs("$time QUERY_SQL:Error $error",__FILE__,__FUNCTION__);
		return null;
	}
	
	$ok=@mysql_select_db($_GLOBAL["sql_database"]);
    if (!$ok){
    		$errnum=mysql_error();
    		$des=mysql_error();
    		writelogs("$time QUERY_SQL:Error Number $errnum ($des)");
    		writelogs("$time Database selected=$Database");
    		die("Database connection error<br><code>$errnum $des</code>");
    
    	}	
	$results=mysql_query($sql);
	
		if(mysql_error()){
				$errnum=mysql_error();
				$des=mysql_error();
				writelogs("$time QUERY_SQL:Error Number $errnum ($des)",__FILE__,__FUNCTION__);
		}	
	
	mysql_close($bd);
	return $results;}
	
	
class domain{
		var $description;
		var $aliases;
		var $mailboxes;
		var $maxquota;
		var $transport;
		var $backupmx;
		var $created;
		var $modified;
		var $active;
		var $domain_name;

	function  domain($domain=null){

		if($domain<>null){
			$this->domain_name=$domain;
			$this->Load();
		}
	}

	function Load(){
		$sql="SELECT * FROM domain WHERE domain='$this->domain_name'";		
		$ligne=mysql_fetch_array(QUERY_SQL($sql,__FUNCTION__,basename(__FILE__)));
		$this->description=$ligne['description'];
		$this->aliases=$ligne['aliases'];
		$this->mailboxes=$ligne['mailboxes'];
		$this->maxquota=$ligne['maxquota'];
		$this->transport=$ligne['transport'];
		$this->backupmx=$ligne['backupmx'];
		$this->created=$ligne['created'];
		$this->modified=$ligne['modified'];
		$this->active=$ligne['active'];
		$this->domain_name=$ligne["domain"];

	}
	
	function IsLocalDomain($domain){
		$sql="SELECT domain FROM domain WHERE domain='$domain'";
		$ligne=mysql_fetch_array(QUERY_SQL($sql,__FUNCTION__,basename(__FILE__)));
		if ($ligne["domain"]==$domain){return true;}
		return false;
		
	}
	
	function GetDomainList(){
		$sql="SELECT * FROM domain ORDER BY domain";
		$resultat=QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));	
		while($ligne=mysql_fetch_array($resultat,MYSQL_ASSOC)){	
			$hash[]=$ligne;
		}
		return $hash;
	}
	
	function Edit(){
		$sql="UPDATE `domain` SET
			`description`='$this->description',
			`aliases`='$this->aliases',
			`mailboxes`='$this->mailboxes',
			`maxquota`='$this->maxquota',
			`transport`='$this->transport',
			`backupmx`='$this->backupmx',
			`created`='$this->created',
			`modified`='$this->modified',
			`active`='$this->active'
			WHERE `domain`='$this->domain_name'";
		QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));

		}

	function Add(){
		$this->created=date('Y-m-d H:i:s');
		$this->modified=date('Y-m-d H:i:s');
		$this->active=1;
		$sql="INSERT INTO `domain` (
			`domain`,
			`description`,
			`aliases`,
			`mailboxes`,
			`maxquota`,
			`transport`,
			`backupmx`,
			`created`,
			`modified`,
			`active`)
			VALUES(
			'$this->domain_name',
			'$this->description',
			'$this->aliases',
			'$this->mailboxes',			
			'$this->maxquota',
			'$this->transport',
			'$this->backupmx',
			'$this->created',
			'$this->modified',
			'$this->active')";

		QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));

		}

	function Delete(){
		$sql="DELETE FROM`domain`WHERE domain='$this->domain_name'";
		QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));

		}

	}
	
class alias{
		var $address;
		var $goto;
		var $domain;
		var $created;
		var $modified;
		var $active;

	function  alias($address=null){

		if($address<>null){
			$this->address=$address;
			$this->Load();
		}
	}

	function Load(){
		$sql="SELECT * FROM alias WHERE address='$this->address'";		
		$ligne=mysql_fetch_array(QUERY_SQL($sql,__FUNCTION__,basename(__FILE__)));
		$this->goto=$ligne['goto'];
		$this->domain=$ligne['domain'];
		$this->created=$ligne['created'];
		$this->modified=$ligne['modified'];
		$this->active=$ligne['active'];

	}
	function Edit(){
		$mcl=new alias($this->address);
		if(strlen($mcl->goto)==0){$this->Add();return null;}
		$this->modified=date('Y-m-d H:i:s');
		
		$sql="UPDATE `alias` SET
			`address`='$this->address',
			`goto`='$this->goto',
			`domain`='$this->domain',
			`modified`='$this->modified',
			`active`='$this->active'
			WHERE `address`='$this->address'";
		QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));

		}
		
	function Delete_all($domain){
		$sql="DELETE FROM alias WHERE domain='$domain'";
		QUERY_SQL($sql);
		
	}

	function Add(){
		$this->active=1;
		$this->created=date('Y-m-d H:i:s');
		$this->modified=date('Y-m-d H:i:s');
		$sql="INSERT INTO `alias` (
			`address`,
			`goto`,
			`domain`,
			`created`,
			`modified`,
			`active`)
			VALUES(
			'$this->address',
			'$this->goto',
			'$this->domain',
			'$this->created',
			'$this->modified',
			'$this->active')";

		QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));

		}

	function Delete(){
		$sql="DELETE FROM`alias`WHERE address='$this->address'";
		QUERY_SQL($sql);

		}
		
	function hash_list_from_domain($domain){
		$sql="SELECT * FROM alias WHERE domain='$domain' ";
		$resultat=QUERY_SQL($sql,__FUNCTION__,basename(__FILE__));	
		while($ligne=mysql_fetch_array($resultat,MYSQL_ASSOC)){	
			$hash[]=$ligne;
		}
		return $hash;
		
	}

	}
	
	class transport_maps{
		var $domain;
		var $goto;

	function  transport_maps($domain=null){
		if($domain<>null){
			$this->domain=$domain;
			$this->Load();
		}
	}

	function Load(){
		$sql="SELECT * FROM transport_maps WHERE domain='$this->domain'";		
		$ligne=mysql_fetch_array(QUERY_SQL($sql));
		$this->domain=$ligne['domain'];
		$this->goto=$ligne['goto'];

	}
	function Edit(){
		writelogs("Checking [$this->domain] before ");
		$newsql=new transport_maps($this->domain);
		writelogs("$this->domain=$newsql->goto (" . strlen($newsql->goto) . ")");
		if(strlen($newsql->goto)==0){
			
			$this->Add();return null;}
		
		
		$sql="UPDATE `transport_maps` SET
			`domain`='$this->domain',
			`goto`='$this->goto'
			WHERE `domain`='$this->domain'";
		QUERY_SQL($sql);

		}

	function Add(){
		$sql="INSERT INTO `transport_maps` (`domain`,`goto`) VALUES('$this->domain','$this->goto')";
		QUERY_SQL($sql);

		}

	function Delete(){
		$sql="DELETE FROM`transport_maps`WHERE domain='$this->domain'";
		QUERY_SQL($sql);

		}

	}

	
class MailBox{
		var $username;
		var $password;
		var $name;
		var $maildir;
		var $quota;
		var $domain;
		var $created;
		var $modified;
		var $active;

	function  mailbox($username=null){

		if($username<>null){
			$this->username=$username;
			$this->Load();
		}
	}

	function Load(){
		$sql="SELECT * FROM mailbox WHERE username='$this->username'";		
		$ligne=mysql_fetch_array(QUERY_SQL($sql));
		$this->username=$ligne['username'];
		$this->password=$ligne['password'];
		$this->name=$ligne['name'];
		$this->maildir=$ligne['maildir'];
		$this->quota=$ligne['quota'];
		$this->domain=$ligne['domain'];
		$this->created=$ligne['created'];
		$this->modified=$ligne['modified'];
		$this->active=$ligne['active'];
		}
	function Load2(){
		$sql="SELECT * FROM mailbox WHERE username='$this->username' AND domain='$this->domain";		
		$ligne=mysql_fetch_array(QUERY_SQL($sql));
		$this->username=$ligne['username'];
		$this->password=$ligne['password'];
		$this->name=$ligne['name'];
		$this->maildir=$ligne['maildir'];
		$this->quota=$ligne['quota'];
		$this->domain=$ligne['domain'];
		$this->created=$ligne['created'];
		$this->modified=$ligne['modified'];
		$this->active=$ligne['active'];
		}		
		
		
		
		
	function Edit(){
		$sql="UPDATE `mailbox` SET
			`username`='$this->username',
			`password`='$this->password',
			`name`='$this->name',
			`maildir`='$this->maildir',
			`quota`='$this->quota',
			`domain`='$this->domain',
			`created`='$this->created',
			`modified`='$this->modified',
			`active`='$this->active'
			WHERE `username`='$this->username'";
		QUERY_SQL($sql);

		}

	function Add(){
		$this->created=date('Y-m-d H:i:s');
		$this->maildir=$this->addslash($this->maildir);
		$sql="INSERT INTO `mailbox` (
			`username`,
			`password`,
			`name`,
			`maildir`,
			`quota`,
			`domain`,
			`created`,
			`modified`,
			`active`)
			VALUES(
			'$this->username',
			'$this->password',
			'$this->name',
			'$this->maildir',
			'$this->quota',
			'$this->domain',
			'$this->created',
			'$this->created',
			'1')";

		QUERY_SQL($sql);

		}

	function Delete(){
		$sql="DELETE FROM`mailbox`WHERE username='$this->username'";
		QUERY_SQL($sql);

		}
		
	function addslash($path){
		if(substr($path,strlen($path)-1,1)<>'/'){
			writelogs('INFOS::Adding slash to the end -> ' . $path);
			return $path . '/';
			
		}else{return $path;}
	}		
		
	function getPathFromUser($user){
			$this->username=$user;	
			$this->Load();
			return $this->maildir;
		}

	function IsExists($user){
		$this->username=$user;
		$this->Load();
		if($this->maildir==null){return false;}
		return true;
		}

	}
	
	