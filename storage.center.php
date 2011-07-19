<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	if($_POST["action"]=="upload"){upload_file();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-add-software"])){popup_add_software();exit;}
	if(isset($_GET["upload-iframe"])){upload_iframe();exit;}
	if(isset($_GET["sflist"])){echo software_list();exit;}
	if(isset($_GET["DelRemoteSoftware"])){echo software_delete();exit;}
	if(isset($_GET["SavePackegInfos"])){echo software_edit();exit;}
	if(isset($_GET["popup-edit-software"])){echo popup_edit_software();exit;}
	

js();	

function software_edit(){
	$strDescription = addslashes(nl2br($_GET["description"]));
	$commandline = addslashes($_GET["commandline"]);
	$ExecuteAfter =  addslashes($_GET["ExecuteAfter"]);
	$MinutesToWait=$_GET["MinutesToWait"];
	
	$sql="UPDATE files_storage SET 
		description='$strDescription', 
		ExecuteAfter='$ExecuteAfter',
		commandline='$commandline',
		MinutesToWait='$MinutesToWait'
		WHERE id_files={$_GET["SavePackegInfos"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		echo "$q->mysql_error";
	}
}
		
function software_delete(){
	$sql="DELETE FROM files_storage WHERE id_files={$_GET["DelRemoteSoftware"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
}

		
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_STORAGE_CENTER}');
	
	$html="
	
	function LoadMainLabel(){
		LoadWinORG('650','$page?popup=yes','$title');
		}	
		
	function AddRemoteSoftware(){
		YahooWin2('500','$page?popup-add-software=yes');
	}
	
	function EditRemoteSoftware(id){
		YahooWin2('500','$page?popup-edit-software='+id);
	}
	
	function RefreshSoftwaresList(){
		LoadAjax('software_list','$page?sflist=yes');
	}
	
	
var x_DelRemoteSoftware=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
}

function DelRemoteSoftware(id){
		var XHR = new XHRConnection();
		XHR.appendData('DelRemoteSoftware',id);
		document.getElementById('software_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DelRemoteSoftware);
}

var x_SavePackegInfos=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
	YahooWin2Hide();
}

function SavePackegInfos(id){
		var XHR = new XHRConnection();
		XHR.appendData('SavePackegInfos',id);
		XHR.appendData('description',document.getElementById('txtDescription').value);
		XHR.appendData('commandline',document.getElementById('commandline').value);
		XHR.appendData('ExecuteAfter',document.getElementById('ExecuteAfter').value);
		XHR.appendData('MinutesToWait',document.getElementById('MinutesToWait').value);
		document.getElementById('packageinfo').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePackegInfos);
}
	

LoadMainLabel();
	
";

echo $html;
}

function popup(){
	$list=software_list();
	$add=Paragraphe("software-back-64-add.png","{ADD_NEW_SOFTWARE}","{ADD_NEW_SOFTWARE_TEXT}","javascript:AddRemoteSoftware()");
	$refresh=Paragraphe("64-refresh.png","{refresh}","{resfresh_software_list}","javascript:RefreshSoftwaresList()");

	$users=new usersMenus();
	if(!$users->winexe_installed){
		$warn=Paragraphe("64-infos.png","{APP_WINEXE_NOT_INSTALLED}",
		"{APP_WINEXE_NOT_INSTALLED_TEXT}","javascript:Loadjs('setup.index.progress.php?product=APP_WINEXE&start-install=yes');")."<br>";
	}	
	
	
	$html="<H1>{APP_STORAGE_CENTER}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>$warn$refresh<br>$add</td>
	<td valign='top'>
		<p class=caption>{APP_STORAGE_CENTER_TEXT}</p>
		". RoundedLightWhite("<div id='software_list' style='width:100%;height:450px;overflow:auto'>$list</div>")."
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function software_list(){
	
	$q=new mysql();
	$sql="SELECT description,filetype,filesize,filename,id_files,OCS_PACKAGE FROM files_storage ORDER BY filesize DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$error=$q->mysql_error;}
	
	$html="<span style='color:red'>$error</span>
	<table style='width:99%'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=99%>{filename}</th>
		<th width=1% nowrap>{filesize}</th>
		<th width=1%>&nbsp;</th>
	</tr>";
	
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	   $filesize=ParseBytes($ligne["filesize"]);
	   $filename=texttooltip($ligne["filename"],$ligne["description"],"EditRemoteSoftware({$ligne["id_files"]})",null,0);
	   $delete=imgtootltip("ed_delete.gif","{delete}","DelRemoteSoftware({$ligne["id_files"]})");
	   if($ligne["OCS_PACKAGE"]==1){$delete="&nbsp;";}
		$html=$html . "
		<tr " . CellRollOver().">
		<td valign='top'><img src='img/fw_bold.gif'></td>
		<td><strong>$filename</strong></td>
		<td><strong>$filesize</strong></td>
		<td width=1%>$delete</td>
		
		</TR>";
		
		}

		$html=$html . "</table>";
		
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body($html);
	
}

function popup_edit_software(){
	$page=CurrentPageName();
	$sql="SELECT description,filetype,filesize,filename,commandline,ExecuteAfter,MinutesToWait,OCS_PACKAGE FROM files_storage WHERE id_files={$_GET["popup-edit-software"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	 $filesize=ParseBytes($ligne["filesize"]);
	$ligne["description"]=html_entity_decode($ligne["description"]);
	$ligne["description"]=br2nl($ligne["description"]);
	$commandline=$ligne["commandline"];
	$ExecuteAfter=$ligne["ExecuteAfter"];
	$MinutesToWait=$ligne["MinutesToWait"];
	
	
	
	
	for($i=2;$i<60;$i++){
		$mins[$i*60]=$i;
	}
	
	$MinutesToWait=Field_array_Hash($mins,"MinutesToWait",$MinutesToWait);	
	
	
	$form="<TABLE style='width:98%' class=table_form>
		  <TR>
		  	 <TD class=legend valign='top'>{description}: </TD>
		   	 <TD><TEXTAREA NAME=\"txtDescription\" id=\"txtDescription\" ROWS=\"5\" COLS=\"30\">{$ligne["description"]}</TEXTAREA></TD>
		  </TR>
		  <TR>
		   		<TD class=legend nowrap>{commandline}: </TD>
		  		<TD>". Field_text("commandline",$commandline)."</TD>
		  </TR>		  
			<TR>	
		   		<TD class=legend nowrap>{ExecuteAfter}: </TD>
		  		<TD>". Field_text("ExecuteAfter",$ExecuteAfter)."</TD>
		  </TR>	
			<TR>	
		   		<TD class=legend nowrap>{MinutesToWait}: </TD>
		  		<TD>$MinutesToWait</TD>
		  </TR>			  		  
		<tr>
			<td valing='top' align='right' colspan=2><input type='button' OnClick=\"javascript:SavePackegInfos({$_GET["popup-edit-software"]});\" value=\"{edit}&nbsp;&raquo;\">
		</tr>
		 </table>";
		 
if($ligne["OCS_PACKAGE"]==1){
	$form=
	"<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/64-ocs.png'></td>
	<td valign='top'>".
	RoundedLightWhite("<p><code style='font-size:13px;font-weight:bold'>{$ligne["description"]}</code></p>
	<br><code style='font-size:13px;font-weight:bold'>{commandline}: $commandline</code>
	<br><code style='font-size:10px;font-weight:bold'>{ExecuteAfter}: $ExecuteAfter</code>
	<br><code style='font-size:13px;font-weight:bold'>{MinutesToWait}:{$ligne["MinutesToWait"]}mn</code>
	")."</td>
	</tr>
	</table>";
}
	
	
	$html="<H1>{$ligne["filename"]}</H1>
	
	<p class=caption style='font-size:14px'>$filesize ({$ligne["filetype"]})</p>
	<div id='packageinfo'>
		$form
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);

}


function popup_add_software(){
	//iframe()
	$page=CurrentPageName();
	$html="<H1>{ADD_NEW_SOFTWARE}</H1>
	<p class=caption>{ADD_NEW_SOFTWARE_TEXT}</p>
	<iframe src='$page?upload-iframe=yes' style='width:100%;height:300px;border:0px'></iframe>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function upload_iframe(){
	$page=CurrentPageName();
	
	for($i=2;$i<60;$i++){
		$mins[$i*60]=$i;
	}
	
	$MinutesToWait=Field_array_Hash($mins,"MinutesToWait");
	
	$html="
	<FORM METHOD=\"post\" ACTION=\"$page\" ENCTYPE=\"multipart/form-data\">
 	<INPUT TYPE=\"hidden\" NAME=\"MAX_FILE_SIZE\" VALUE=\"100000000\">
 	<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"upload\">
 	<TABLE style='width:98%' class=table_form>
		  <TR>
		   <TD class=legend valign='top'>{description}: </TD>
		   <TD><TEXTAREA NAME=\"txtDescription\" ROWS=\"5\" COLS=\"30\"></TEXTAREA></TD>
		  </TR>
		  <TR>
		   	<TD class=legend>{file}: </TD>
		  	<TD><INPUT TYPE=\"file\" NAME=\"binFile\"></TD>
		  </TR>
		  <TR>
		   		<TD class=legend nowrap>{commandline}: </TD>
		  		<TD>". Field_text("commandline")."</TD>
		  </TR>		  
			<TR>	
		   		<TD class=legend nowrap>{ExecuteAfter}: </TD>
		  		<TD>". Field_text("ExecuteAfter")."</TD>
		  </TR>	
		  	<TR>	
		   		<TD class=legend nowrap>{MinutesToWait}: </TD>
		  		<TD>$MinutesToWait</TD>
		  </TR>		  
		  
		  <TR>
		   <TD COLSPAN=\"2\" align='right'><INPUT TYPE=\"submit\" VALUE=\"{upload}&nbsp;&raquo;\"></TD>
		  </TR>
		 </TABLE>
</FORM>
	
	
	";
	$html=iframe($html,0);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function upload_file(){
	$tpl=new templates();
	if(!is_array($_FILES["binFile"])){
		upload_iframe();
		exit;
	}
	

	
	$filename=$_FILES["binFile"]["name"];
	$size=$_FILES["binFile"]["size"];
	$filepath=$_FILES["binFile"]["tmp_name"];
	$txtDescription=$_POST["txtDescription"];
	$type=$_FILES["binFile"]["type"];
	$error=$_FILES["binFile"]["error"];
	
	$ExecuteAfter=addslashes($_POST["ExecuteAfter"]);
	$commandline=addslashes($_POST["commandline"]);
	$MinutesToWait=addslashes($_POST["MinutesToWait"]);
	$filename=str_replace(" ","-",$filename);
	
	//$filename=filesize()
	
if($error==0){
	
	$q=new mysql();

    $data = addslashes(fread(fopen($filepath, "r"), filesize($filepath)));
    $strDescription = addslashes(nl2br($txtDescription));
    $sql = "INSERT INTO files_storage ";
    $sql .= "(description, bin_data, filename, filesize, filetype,commandline,ExecuteAfter,MinutesToWait) ";
    $sql .= "VALUES ('$strDescription', '$data', ";
    $sql .= "'$filename', '$size', '$type','$commandline','$ExecuteAfter','$MinutesToWait')";
    $q->QUERY_SQL($sql,"artica_backup");
    if($q->ok){
    	echo $tpl->_ENGINE_parse_body("<strong style='font-size:16px;color:red'>{success} $filename</strong>");
    	exit;
    }
    
   echo $tpl->_ENGINE_parse_body("<strong style='font-size:16px;color:red'>$q->mysql_error</strong>"); 
	
}else{
	 echo $tpl->_ENGINE_parse_body("<strong style='font-size:16px;color:red'>Error number $error</strong>"); 
	 exit;	
}
}

?>