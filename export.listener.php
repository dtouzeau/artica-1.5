<?php
session_start();
if(!isset($_SESSION["uid"])){echo "<H1>Session Out</H1>";exit;}
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
$user=new usersMenus();


if($user->AllowAddGroup==false){echo 
	$rpl=new template_users("{import users}","{not allowed}",0,1);
	echo $rpl->web_page;
	exit;}


if($_GET["mode"]=="ImportUsers"){echo ImportUsers();}
if( isset($_POST['upload']) ){if($_POST["articamethod"]=="ImportUsers"){ImportUsers_Uploaded();exit();}}
if(isset($_POST["process_temp_file"])){process_temp_file();exit;}



function ImportUsers($error=null){
$page=CurrentPageName();
$html="
<div id='content'>
<h1>{import users} {$_GET["ou"]}</h1>
<p>{import users explain}</p>
<div style='color:red;font-size:12px'><b>$error</b></div><br>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
<p>
<input type=\"hidden\" name=\"articamethod\" value='ImportUsers'>
<input type=\"hidden\" name=\"ou\" value='{$_GET["ou"]}'>
<input type=\"hidden\" name=\"suffix\" value='{$_GET["suffix"]}'>
<input type=\"file\" name=\"fichier\" size=\"30\">
<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:90px'>
</p>
</form>

</div>";
	
	
$tpl=new template_users('{import users}',$html,0,1);
echo $tpl->web_page;

	
	
	
}
function ImportUsers_Uploaded(){
	$_GET["ou"]=$_POST["ou"];
	$_GET["suffix"]=$_POST["suffix"];
	$tmp_file = $_FILES['fichier']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";

	if( !is_uploaded_file($tmp_file) ){ImportUsers('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	  if( !strstr($type_file, 'csv')){	ImportUsers('{error_file_extension_not_match} :csv');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){ImportUsers("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
Parsing_first_users();	 	
}

function Parsing_first_users(){
	$array_content=explode("\n",file_get_contents($_GET["moved_file"]));
	$table="<table style='width:100%'>
	<tr class='rowT'>
	<td>{email}</td>
	<td>{mailbox}</td>
	<td>{group name}</td>
	<td>{mailbox account}</td>
	<td>{password}</td>
	</tr>
	
	";
	for($i=0;$i<5;$i++){
		$table=$table. "<tr>";
		if(preg_match_all('#"([A-Za-z0-9\._\-\séèàùÃ«\'©\(\)\{\}\@]+|)"#',$array_content[$i],$res)){
		$table=$table. "<td>{$res[1][1]}</td><td>{$res[1][2]}</td><td>{$res[1][3]}</td><td>{$res[1][4]}</td><td>{$res[1][5]}</td>";	
		}else{
			$table=$table. "<td colspan=7>Line $i ({$array_content[$i]}) {failed}</td>";
		}
	}
	$table=$table . "</table>";
	
$page=CurrentPageName();
$html="
<div id='content'>
<form name='ffm1' method='post'>
<input type='hidden' name='process_temp_file' value='{$_GET["moved_file"]}'>
<input type='hidden' name='ou' value='{$_GET["ou"]}'>
<input type='hidden' name='suffix' value='{$_GET["suffix"]}'>
<h1>{import users} {$_GET["ou"]}</h1>
<p>{import users explain 2}</p>
$table
<br>
<center><input type='submit' value='{continue_next_process}'></center>
</form>
</div>";	
	
$tpl=new template_users('{import users}',$html,0,1);
echo $tpl->web_page;	
}

function process_temp_file(){
	
	if(!is_file($_POST["process_temp_file"])){ ImportUsers("{error_unable_process_file} : ". $_POST["process_temp_file"]); exit;}
	if($_POST["ou"]==null){	ImportUsers("{error_miss_datas} : ou"); exit;}
	if($_POST["suffix"]==null){ImportUsers("{error_miss_datas} : suffix"); exit;}	
	$ou=$_POST["ou"];
	$suffix=$_POST["suffix"];
	$datas=file_get_contents($_POST["process_temp_file"]);
	$process=new ImportFile($_POST["process_temp_file"],$ou);
	  		
$count=count($array_content)	;
$page=CurrentPageName();
$html="
<div id='content'>
<h1>{import users}$ou</h1>
<strong>$process->countLine lines</strong><br>
<strong>$process->countFailed users {failed}</strong><br>
<a href='$process->logFile'>". basename($process->logFile) ."</a>
<br>
</div>";	
	
$tpl=new template_users('{import users}',$html,0,1);
echo $tpl->web_page;		
}




class ImportFile{
	var $filename;
	var $ou;
	var $datas;
	var $array_content;
	var $logs;
	var $process_result=false;
	var $DisplayName;
	var $eMail;
	var $enable_mailbox;
	var $Group_name;
	var $userid;
	var $password;
	var $quota;
	var $group_id;
	var $ClassLdap;
	var $countFailed;
	var $countLine;
	var $logFile;
	
	function ImportFile($filename,$ou){
		$content_dir=dirname(__FILE__)."/ressources/conf/upload";
		my_mkdir($content_dir);
		$this->filename=$filename;
		$this->ou=$ou;
		$this->datas=file_get_contents($this->filename);
		$this->array_content=explode("\n",$this->datas);
		$this->countLine=count($this->array_content);
		if($this->countLine<5){
			$this->logs("{error_miss_datas} : array(" . count($this->array_content) . "<br>$datas<br>");
			$this->process_result=false;
			return false;
			}
		$this->process_array();
			
	}
	
	
	function process_array(){
		$ldap=new clladp();
		$this->ClassLdap=$ldap;
		while (list ($num, $ligne) = each ($this->array_content) ){
			$this->logs("Process $num\n*******************************\n$ligne");
				if($this->Parse_array($ligne)){
					if($this->ProcessGroup()==false){$this->logs("Process group failed");}
					if($this->Adduser()==true){
						$this->logs("success");
						if($ldap->AddUserToGroup($this->group_id,$this->userid)==false){$this->logs("Failed to add group $this->group_id for $this->userid\n$ldap->ldap_last_error\n");}
						}else{$this->countFailed=$this->countFailed+1;}
				}else{$this->countFailed=$this->countFailed+1;}
		}
	}
	
	function SaveLogs(){
		
		
	}
	
	
	function Parse_array($ligne){
			$this->DisplayName=null;
			$this->eMail=$res[1][1];
			$this->enable_mailbox=null;
			$this->Group_name=null;
			$this->userid=null;
			$this->password=null;
			$this->quota=null;
			$this->group_id=0;
		
		if(preg_match_all('#"([A-Za-z0-9\._\-\séèàùÃ«\'©\(\)\{\}\@]+|)"#',$ligne,$res)){
			$this->DisplayName=$res[1][0];
			$this->eMail=$res[1][1];
			$this->enable_mailbox=$res[1][2];
			$this->Group_name=$res[1][3];
			$this->userid=$res[1][4];
			$this->password=$res[1][5];
			$this->quota=$res[1][6];
			$this->group_id=0;
			return true;
		}else{$this->logs("Unable to process this line -> preg_match failed");return false;}
		
		
		
	}
	
	
	
	function UserExists(){
		$hash=$this->ClassLdap->UserDatas($this->userid);
		if(!is_array($hash)){
			$this->logs("Process user : $this->userid doesn't exist\n");
			return false;
		}
		return true;
	}
	
	
	function ProcessGroup(){
	
		if($this->Group_name==null){$this->logs("Group name is null...");return false;}
		$dnGroup="cn=$this->Group_name,ou=$this->ou," . $this->ClassLdap->suffix;
		$this->logs($dnGroup);
		$group_id=$this->GetGroupid($dnGroup);
		$this->logs("$dnGroup -> NEW GUID=$group_id");
		if($group_id==0){
			$group_id=$this->ClassLdap->AddUserGroup($this->ou,$this->Group_name);
			$logs=$logs . "$this->Group_name -> NEW GUID=$group_id\n";
			}
			
		if($group_id>0){
			$this->group_id=$group_id;
			return true;}else{return false;}
		
		
	}
	function GetGroupid($dnGroup){
		$hahs=$this->ClassLdap->Ldap_read($dnGroup,'(objectClass=*)',array('gidNumber'));	
		if($hahs["count"]>0){return $hahs[0]["gidnumber"][0];}
		else{$this->logs("Failed to get group id " .$this->ClassLdap->ldap_last_error . "\n");			
			return 0;}
		}	
	
	
	
	function logs($text){
		
		$fp = fopen("ressources/logs/$this->ou-results.txt", "a"); #open for writing
	  	@fputs($fp, $text."\n"); #write all of $data to our opened file
	  	@fclose($fp); #close the file
		$this->logFile="ressources/logs/$this->ou-results.txt";
	}
	
function Adduser(){
	
	if($this->DisplayName==null){
		if($this->eMail<>null){
			$tblemail=explode('@',$this->eMail);
			$this->DisplayName=$tblemail[0];
		}
	}
	$this->userid=strtolower($this->userid);
	
	
	if($this->eMail==null){$this->eMail="$this->DisplayName@nodomain";}
	$tblemail=explode('@',$this->eMail);
	if(is_array($tblemail)){
		$domain=$tblemail[1];
		if($this->userid==null){$this->userid=$tblemail[0];}
		$this->logs("Add users -> $this->userid, $domain");
	}
	if($this->UserExists()==true){$this->logs("User already exists");return false;}
	if($this->userid==null){$this->logs("Warning userid is null process failed");return false;}
	if($this->password==null){$this->password=$this->DisplayName;}
	if($enable_mailbox=='yes'){$enable_mailbox='TRUE';}else{$enable_mailbox='FALSE';}
	
		$this->logs('****** PROCESSING '. $this->userid . '*****' );
		$update_array["objectClass"][]="userAccount";
		$update_array["objectClass"][]="top";
		$update_array["objectClass"][]="ArticaSettings";
		
		$update_array["displayName"][]=$this->DisplayName;
		$update_array["homeDirectory"][]='/home/'.$this->DisplayName;
		$update_array["mailDir"][]="cyrus";
		$update_array["givenName"][]=$this->DisplayName;
		$update_array["accountGroup"][]=0;
		$update_array["accountActive"][]='TRUE';
		$update_array["MailboxActive"][]=$enable_mailbox;
		$update_array["cn"][]=$this->DisplayName;
		$update_array["sn"][]=$this->DisplayName;
		$update_array["uid"][]=strtolower($this->userid);
		$update_array["mail"][]=strtolower($this->eMail);
		$update_array["userPassword"][]=$this->password;
		$update_array["domainName"][]=strtolower($domain);
		
		if($this->ClassLdap->ldap_add("cn=$this->DisplayName,ou=$this->ou,{$this->ClassLdap->suffix}",$update_array)){
			$this->logs("Adding $this->userid success !\n******************************\n");
			return true;}else{
			$this->logs("Adding $this->userid failed !");
			$this->logs($this->ClassLdap->ldap_last_error . "\n******************************\n");
			return false;
		}
	}	
	
}









?>