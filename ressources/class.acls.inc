<?php
include_once(dirname(__FILE__).'/class.sockets.inc');
class aclsdirs{
	var $directory;
	var $md5;
	var $chmod_owner_read;
	var $chmod_owner_write;
	var $chmod_owner_execute;
	var $chmod_group_read;
	var $chmod_group_write;
	var $chmod_group_execute;
	var $chmod_public_read;
	var $chmod_public_write;
	var $chmod_public_execute;	
	var $chmod_recursive=0;
	var $chmod_octal=0;
	var $chmod_strings='';
	var $acls_array=array();
	var $chmod_owner;
	var $events;
	
	function aclsdirs($directory=null){
		if($directory<>null){
			$this->directory=$directory;
			$this->loadacls();
		}
		
	}
	
	
	private function loadacls(){
		$md5=md5($this->directory);
		
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT * FROM acl_directories WHERE `md5`='$md5'","artica_backup"));
		if($GLOBALS["VERBOSE"]){echo "chmod -> ". strlen($ligne["chmod"])." bytes in mysql\n";}
		$chmod=unserialize($ligne["chmod"]);
		
		
		if($chmod["chmod_owner_read"]=="r"){$this->chmod_owner_read=1;}
		if($chmod["chmod_owner_write"]=="w"){$this->chmod_owner_write=1;}
		if($chmod["chmod_owner_execute"]=="x"){$this->chmod_owner_execute=1;}
		
		if($chmod["chmod_group_read"]=="r"){$this->chmod_group_read=1;}
		if($chmod["chmod_group_write"]=="w"){$this->chmod_group_write=1;}
		if($chmod["chmod_group_execute"]=="x"){$this->chmod_group_execute=1;}

		if($chmod["chmod_public_read"]=="r"){$this->chmod_public_read=1;}
		if($chmod["chmod_public_write"]=="w"){$this->chmod_public_write=1;}
		if($chmod["chmod_public_execute"]=="x"){$this->chmod_public_execute=1;}		
		
		$this->chmod_recursive=$chmod["chmod_recursive"];
		$this->chmod_strings=$chmod["chmod_strings"];
		$this->chmod_octal=$chmod["chmod_octal"];
		$this->events=$ligne["events"];
		$this->md5=trim($ligne["md5"]);	
		if($GLOBALS["VERBOSE"]){echo "acls -> ". strlen($ligne["acls"])." bytes in mysql\n";}
					
		$this->acls_array=unserialize($ligne["acls"]);
		
		$sock=new sockets();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?chmod-access=".urlencode($this->directory))));
	
		if(!is_array($chmod)){
			
			$octal=$datas[1];
			$rwx=$datas[2];
			if($rwx[1]=='r'){$this->chmod_owner_read=1;}
			if($rwx[2]=='w'){$this->chmod_owner_write=1;}
			if($rwx[3]=='x'){$this->chmod_owner_execute=1;}
			if($rwx[4]=='r'){$this->chmod_group_read=1;}
			if($rwx[5]=='w'){$this->chmod_group_write=1;}
			if($rwx[6]=='x'){$this->chmod_group_execute=1;}
			if($rwx[7]=='r'){$this->chmod_public_read=1;}
			if($rwx[8]=='w'){$this->chmod_public_write=1;}
			if($rwx[9]=='x'){$this->chmod_public_execute=1;}
			$this->chmod_octal=$octal;	
			$this->chmod_strings=$rwx;
			
		}
		
		$this->chmod_owner="{$datas[3]}:{$datas[4]}";
		
	}
	
	public function DeleteAllacls(){
		$sql_edit="UPDATE acl_directories SET `acls`='',`update`='1' WHERE `md5`='$this->md5'";
		$q=new mysql();
		$q->QUERY_SQL($sql_edit,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}	
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?acls-delete=".urlencode($this->directory));					
	}
	
	public function SaveAcls(){
		$data=serialize($this->acls_array);
		$md5=md5($this->directory);
		$sql_add="INSERT INTO acl_directories(acls,directory,md5) VALUES('$data','$this->directory','$md5')";
		$sql_edit="UPDATE acl_directories SET `acls`='$data',`update`='1' WHERE `md5`='$this->md5'";
		if($this->md5<>null){$sql=$sql_edit;}else{$sql=$sql_add;}
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?acls-apply=$md5");		
		
	}
	
	public function SaveAclsChmod($array){
		
		$md5=md5($this->directory);
		if(!isset($array["chmod_octal"])){
				while (list ($num, $ligne) = each ($array) ){
					writelogs("Save chmod $num = $ligne ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					if($ligne==0){$octals[$num]="-";}else{$octals[$num]=$ligne;}
				}
		
				if($octals["chmod_owner_read"]==1){$octals["chmod_owner_read"]="r";}
				if($octals["chmod_owner_write"]==1){$octals["chmod_owner_write"]="w";}
				if($octals["chmod_owner_execute"]==1){$octals["chmod_owner_execute"]="x";}
				if($octals["chmod_group_read"]==1){$octals["chmod_group_read"]="r";}
				if($octals["chmod_group_write"]==1){$octals["chmod_group_write"]="w";}	
				if($octals["chmod_group_execute"]==1){$octals["chmod_group_execute"]="x";}
				if($octals["chmod_public_read"]==1){$octals["chmod_public_read"]="r";}	
				if($octals["chmod_public_write"]==1){$octals["chmod_public_write"]="w";}	
				if($octals["chmod_public_execute"]==1){$octals["chmod_public_execute"]="x";}
				$wxstr="{$octals["chmod_owner_read"]}{$octals["chmod_owner_write"]}{$octals["chmod_owner_execute"]}";
				$wxstr=$wxstr."{$octals["chmod_group_read"]}{$octals["chmod_group_write"]}{$octals["chmod_group_execute"]}";
				$wxstr=$wxstr."{$octals["chmod_public_read"]}{$octals["chmod_public_write"]}{$octals["chmod_public_execute"]}";				
				
				
				$octals["chmod_strings"]=$wxstr;
				$octals["chmod_octal"]=$this->ModeRWX2Octal($wxstr);
				
				writelogs("Save chmod string= $wxstr and octal = {$octals["chmod_octal"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$octals["chmod_recursive"]=$array["chmod_recursive"];
		
		
		$data=serialize($octals);
		$sql_add="INSERT INTO acl_directories(chmod,directory,md5) VALUES('$data','$this->directory','$md5')";
		$sql_edit="UPDATE acl_directories SET `chmod`='$data',`update`='1' WHERE `md5`='$this->md5'";
		if($this->md5<>null){$sql=$sql_edit;}else{$sql=$sql_add;}
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $q->mysql_error;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?acls-apply=$md5");	
		
	}
	
	
function ModeRWX2Octal($Mode_rwx) {    // enter rwx mode, e.g. 'drwxr-sr-x'
    if ( ! preg_match("/[-d]?([-r][-w][-xsS]){2}[-r][-w][-xtT]/", $Mode_rwx) ){writelogs("wrong <TT>-rwx</TT> mode in ModeRWX2Octal('<TT>$Mode_rwx</TT>')",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
    $Mrwx = substr($Mode_rwx, -9);    // 9 chars from the right-hand side
    $ModeDecStr     = (preg_match("/[sS]/",$Mrwx[2]))?4:0;    // pick out sticky
    $ModeDecStr    .= (preg_match("/[sS]/",$Mrwx[5]))?2:0;    // _ bits and change
    $ModeDecStr    .= (preg_match("/[tT]/",$Mrwx[8]))?1:0;    // _ to e.g. '020'
    $Moctal     = $ModeDecStr[0]+$ModeDecStr[1]+$ModeDecStr[2];    // add them
    $Mrwx = str_replace(array('s','t'), "x", $Mrwx);    // change execute bit
    $Mrwx = str_replace(array('S','T'), "-", $Mrwx);    // _ to on or off
    $trans = array('-'=>'0','r'=>'4','w'=>'2','x'=>'1');    // prepare for strtr
    $ModeDecStr    .= strtr($Mrwx,$trans);    // translate to e.g. '020421401401'
    $Moctal    .= $ModeDecStr[3]+$ModeDecStr[4]+$ModeDecStr[5];    // continue
    $Moctal    .= $ModeDecStr[6]+$ModeDecStr[7]+$ModeDecStr[8];    // _ adding
    $Moctal    .= $ModeDecStr[9]+$ModeDecStr[10]+$ModeDecStr[11];  // _ triplets
    return $Moctal;    // returns octal mode, e.g. '2755' from above.
}
function ModeOctal2rwx($ModeOctal) { // enter octal mode, e.g. '644' or '2755'
    if ( ! preg_match("/[0-7]{3,4}/", $ModeOctal) )    // either 3 or 4 digits
        die("wrong octal mode in ModeOctal2rwx('<TT>$ModeOctal</TT>')");
    $Moctal = ((strlen($ModeOctal)==3)?"0":"").$ModeOctal;    // assume default 0
    $Mode3 = substr($Moctal,-3);    // trailing 3 digits, no sticky bits considered
    $RWX = array ('---','--x','-w-','-wx','r--','r-x','rw-','rwx');    // dumb,huh?
    $Mrwx = $RWX[$Mode3[0]].$RWX[$Mode3[1]].$RWX[$Mode3[2]];    // concatenate
    if (preg_match("/[1357]/", $Moctal[0])) $Mrwx[8] = ($Mrwx[8]=="-")?"T":"t";
    if (preg_match("/[2367]/", $Moctal[0])) $Mrwx[5] = ($Mrwx[5]=="-")?"S":"s";
    if (preg_match("/[4567]/", $Moctal[0])) $Mrwx[2] = ($Mrwx[2]=="-")?"S":"s";
    return $Mrwx;    // returns e.g. 'rw-r--r--' or 'rwxr-sr-x'
}	
	
	
	
}