<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{error_no_privileges}');
		exit;
		}
		
		
	if(isset($_FILES["form"]["name"]["file"])){MEMBERS_UPLOAD_FILE();exit;}
	if(isset($_GET["ImportMembersFile"])){MEMBERS_IMPORT_FILE();exit;}		
	if(isset($_GET["start-page"])){START_PAGE($_GET["start-page"]);exit;}
		
		
js();
		
		
function js(){
	$gid=$_GET["gid"];
	$tpl=new templates();
	
	$page=CurrentPageName();
	$group=new groups($gid);
	$import_title=$tpl->_ENGINE_parse_body('{import}',"domains.edit.group.php");
	$js=file_get_contents("js/webtoolkit.aim.js");
	
	$html="
	   $js
	   
		function startCallback() {
			// make something useful before submit (onStart)
			return true;
		}
 
		function completeCallback(response) {
			// make something useful after (onComplete)
			//document.getElementById('nr').innerHTML = parseInt(document.getElementById('nr').innerHTML) + 1;
			document.getElementById('form_response').innerHTML = response;
		}
		
	
		function LoadImportPage(){
			YahooWin2(550,'$page?start-page=$gid','$group->ou::$group->groupName $import_title');
		}
		
	function x_PerformImport(obj) {
		var tempvalue=obj.responseText;
		document.getElementById('form_response').innerHTML = tempvalue;
		LoadMembers($gid)
	}		
		
		function PerformImport(){
			 var ImportMembersFile=document.getElementById('content_path').value;
			var XHR = new XHRConnection();
			XHR.appendData('ImportMembersFile',ImportMembersFile);
			XHR.appendData('groupid',$gid);
			document.getElementById('form_response').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_PerformImport);			 
		
		}
		
		
		LoadImportPage();
	
	
	";
	
	echo $html;
	
	
}
function MEMBERS_UPLOAD_FILE(){
	$tpl=new templates();
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	
	$tmp_file = $_FILES["form"]['tmp_name']["file"];
	$type_file = $_FILES["form"]['type']["file"];
	$name_file = $_FILES["form"]["name"]["file"];
	$moved_file=$content_dir . "/" .$name_file;
	
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){echo $tpl->_ENGINE_parse_body('{error_unable_to_upload_file}:' . $tmp_file);exit();}
	if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
	if(!move_uploaded_file($tmp_file,$moved_file) ){echo "{error_unable_to_move_file} : ". $moved_file;exit();}
     
   
	
    $data=file_get_contents($moved_file);
    $tr=explode("\n",$data);
    $tb="<table style='width:100%'>
    <tr>
    	<th>{uid}</th>
    	<th>{displayname}</th>
    	<th>{mail}</th>
    	<th>{password}</th>
    	<th>{postalcode}</th>
    	<th>{mobile}</th>
    	<th>{telephonenumber}</th>
    </tr>
    	";
	$count=0;
	
		while (list ($num, $ligne) = each ($tr) ){
			if(trim($ligne)==null){continue;}
			$ligne=str_replace('"','',$ligne);
			$ligne=str_replace("'","`",$ligne);
			$table=explode(";",$ligne);
			$uid=$table[2];
			$DisplayName=$table[0];
			$mail=$table[1];
			$password=$table[3];
			$PostalCode=$table[4];
			$postalAddress=$table[5];
			$mobile=$table[6];
			$telephoneNumber=$table[7];
			$count=$count+1;
			if($count>20){break;}
				$tb=$tb."<tr>
					<td><strong>$uid</strong></td>
					<td><strong>$DisplayName</strong></td>
					<td><strong>$mail</strong></td>
					<td><strong>****</strong></td>
					<td><strong>$PostalCode</strong></td>
					<td><strong>$mobile</strong></td>
					<td><strong>$telephoneNumber</strong></td>
					</tR>";
			
			
			}
	   
    $tb="<div style='width:100%;height:200px;overflow:auto'>$tb</div>";
    $tb=RoundedLightWhite($tb);
    $html="
    <input type='hidden' id='content_path' value='$moved_file'>
    <table style='width:100%' class=table_form>
    <tr>
    	<td class=legend>$name_file (" . count($tr) . " {lines})</td>
    	<td align='right'><input type='button' OnClick=\"javascript:PerformImport();\" value='{import}&nbsp;&raquo;'></td>
    </tr>
    </table>
    <br>
    $tb
	";
    echo $tpl->_ENGINE_parse_body($html);
    
}



function MEMBERS_IMPORT_FILE(){
	
	$file=$_GET["ImportMembersFile"];
	$groupid=$_GET["groupid"];
	$gg=new groups($groupid);
	$ou=$gg->ou;
	
	
	writelogs("importing $file....",__FUNCTION__,__FILE__);
	$datas=file_get_contents($file);
	$datas=explode("\n",$datas);
	$good=0;
	$bad=0;

$count_user=0;
if(is_array($datas)){
		while (list ($num, $ligne) = each ($datas) ){
			if(trim($ligne)==null){continue;}
			$ligne=str_replace('"','',$ligne);
			$ligne=str_replace("'","`",$ligne);
			$table=explode(";",$ligne);
			if($table[2]==null){continue;}
			$count_user=$count_user+1;
			$user=new user();
			$user->uid=$table[2];
			$user->ou=$ou;
			$user->DisplayName=$table[0];
			$user->group_id=$groupid;
			$user->mail=$table[1];
			$user->password=$table[3];
			$user->PostalCode=$table[4];
			$user->postalAddress=$table[5];
			$user->mobile=$table[6];
			$user->telephoneNumber=$table[7];
			if($user->add_user()){
				if($table[8]<>null){
					$aliases=explode(',',$table[8]);
					if(is_array($aliases)){
						while (list ($num1, $mail_ali) = each ($aliases) ){
							if(trim($mail_ali)==null){continue;}
							$user->add_alias($mail_ali);
						}
					}
				}
				$good=$good+1;}else{$bad=$bad+1;}
			}
	}
	
	$html="
	<strong>$group_error</strong>
	<table style='width:100%;padding:1px;border:1px solid #CCCCCC'>
	<tr>
	<th>{success}</th>
	<th>{failed}</th>
	<th>{members}</th>
	</tr>
	<tr>
	<td align='center'><strong>$good</td>
	<td align='center'><strong>$bad</td>
	<td align='center'><strong>$count_user</td>
	</tr>
	<tr>
	<td colspan=3><strong>{group} N.$groupid</strong></td></tr>
	</table>
	$logs
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightWhite($html));
	
	
}


function START_PAGE($gpid){
	$page=CurrentPageName();
	
	
	
	$html="<H1>{importing_form_text_file}</H1>
	<p class=caption>{importuser_text}</p>
	<p class=caption>{importuser_aliases_text}</p>
		<input type='hidden' name='groupid' value='$gpid'>
    	<form action=\"$page\" method=\"post\" onsubmit=\"return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})\">
			<div><label>File:</label> <input type=\"file\" name=\"form[file]\" />&nbsp;&nbsp;<input type=\"submit\" value=\"{upload}&nbsp;&raquo;\" /></div>
		</form>
 
	<div id='form_response'></div>

 
    	
    
    ";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"domains.edit.group.php");
}

		
?>