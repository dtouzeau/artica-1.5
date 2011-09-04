<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');

	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}

	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	if(!CheckSambaRights()){echo "alert('$ERROR_NO_PRIVS')";die();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["search"])){search();exit;}
	if(isset($_POST["TrashRestore"])){TrashRestore();exit;}
	if(isset($_POST["TrashDelete"])){TrashDelete();exit;}
	if(isset($_POST["TrashScan"])){TrashScan();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->javascript_parse_text("{recycle}");
	$urlencoded=urlencode($_GET["sharename"]);
	$html="RTMMail('950','$page?popup=yes&sharename=$urlencoded','$title::{$_GET["sharename"]}');";
	echo $html;
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$smb=new samba();
	$dirs=$smb->LOAD_RECYCLES_BIN();
	while (list ($DirUid, $none) = each ($dirs) ){$trashes[$DirUid]=$DirUid;}
	$trashes[null]="{all}";
	$html="
	<center style='margin-bottom:10px'>
	<table style='width:70%' class=form>
		<tbody>
			<tr>
				<td class=legend>{files}:</td>
				<td colspan=3>". Field_text("search-file-trash","*","font-size:14px;padding:3px;",null,null,null,false,"SearchTrashFileCheck(event)")."</td>
				<td width=1%>". button("{search}","SearchTrashFile()")."</td>
			</tr>
			<tr>
				<td class=legend>{share}:</td>
				<td>". Field_array_Hash($trashes, "sharename",$_GET["sharename"],"style:font-size:14px")."</td>
				<td class=legend>{date}:</td>
				<td>". Field_text("search-date-trash","0000-00-00","font-size:14px;padding:3px;width:90px",null,null,null,false,"SearchTrashFileCheck(event)")."</td>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
	</center>
	<div id='trash-samba-list' style='width:100%;height:550px;overflow:auto'></div>
	<script>
	function SearchTrashFileCheck(e){
		if(checkEnter(e)){SearchTrashFile();}
	}
	
	function SearchTrashFile(){
		var date=escape(document.getElementById('search-date-trash').value);
		var sharename=escape(document.getElementById('sharename').value);
		var searchfile=escape(document.getElementById('search-file-trash').value);
		LoadAjax('trash-samba-list','$page?search='+searchfile+'&date='+date+'&sharename='+sharename);
	
	}
	
	SearchTrashFile();
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function search(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$_GET["sharename"]=trim($_GET["sharename"]);
	$_GET["date"]=trim($_GET["date"]);
	$_GET["search"]=trim($_GET["search"]);
	$_GET["search"]="*{$_GET["search"]}*";
	$_GET["search"]=str_replace("**", "*", $_GET["search"]);
	$_GET["search"]=str_replace("**", "*", $_GET["search"]);
	$_GET["search"]=str_replace("*", "%", $_GET["search"]);
	if($_GET["search"]<>null){$path=" AND path LIKE '{$_GET["search"]}'";}
	if(trim($_GET["sharename"])<>null){$sharename=" AND sharename='{$_GET["sharename"]}'";}
	if(trim($_GET["date"])=="0000-00-00"){$_GET["date"]=null;}
	if($_GET["date"]<>null){$date=" AND DATE_FORMAT(zDate,'%Y-%m-%d')>='{$_GET["date"]}'";}
	
	
	$sql="SELECT * FROM samba_recycle_bin_list WHERE 1$sharename$path$date ORDER BY filesize DESC LIMIT 0,50 ";
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{date}</th>
		<th width=1% align=center>". imgtootltip("eclaire-24.png","{scan_trashs}","ScanTrashs()")."</th>
		<th width=1%>{members}</th>
		<th width=99%>{files}</th>
		<th width=1% colspan=3>{size}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if(preg_match("#Table.+?doesn.+?exist#", $q->mysql_error)){
			$q->BuildTables();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				echo "<H2>$q->mysql_error (after building tables)</H2>";
			}
		}else{
			echo "<H2>$q->mysql_error</H2>";}
	}
	
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$size=$ligne["filesize"]/1024;
		$size=FormatBytes($size);
		$orgfile=$ligne["path"];
		$uidregx=$ligne["uid"];
		$uidregx=str_replace(".", "\.", $uidregx);
		$newfile=$orgfile;
		if(preg_match("#$uidregx\/(.+)#",$orgfile,$re)){$newfile=$re[1];}
		$newfileLenght=strlen($newfile);
		if($newfileLenght>80){$newfile="...".substr($newfile,$newfileLenght-77,77);}
		$enc=base64_encode($orgfile);
		$md5=md5($orgfile);
		$color="black";
		if($ligne["restore"]==1){$color="#CCCCCC";}
		if($ligne["delete"]==1){$color="#CCCCCC";}
		$html=$html."
		<tr class=$classtr>
		<td width=1% nowrap style='font-size:13px' colspan=2><span id='date-$md5' style='color:$color'>{$ligne["zDate"]}</span></td>
		<td width=1% nowrap style='font-size:13px'><span id='uid-$md5' style='color:$color'>{$ligne["uid"]}</td>
		<td width=99% nowrap style='font-size:13px'><span id='file-$md5' style='color:$color'>$newfile</td>
		<td width=1% nowrap style='font-size:13px'><span id='size-$md5' style='color:$color'>$size</td>
		<td width=1% nowrap style='font-size:13px'>". imgtootltip("down-24.png","{restore}","TrashRestore('$md5','$enc')")."</td>
		<td width=1% nowrap style='font-size:13px'>". imgtootltip("delete-24.png","{delete}","TrashDelete('$md5','$enc')")."</td>
		
		
		</tr>
		";
		
	}
	
	$html=$html."</table>
	<script>
	
var x_TrashRestore=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
    }	
    
var x_ScanTrashs=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	SearchTrashFile();
    }	    
	
	function TrashRestore(id,enc){
		document.getElementById('date-'+id).style.color = '#CCCCCC';
		document.getElementById('uid-'+id).style.color = '#CCCCCC';
		document.getElementById('file-'+id).style.color = '#CCCCCC';
		document.getElementById('size-'+id).style.color = '#CCCCCC';
		var XHR = new XHRConnection();
		XHR.appendData('TrashRestore',enc);
		XHR.sendAndLoad('$page', 'POST',x_TrashRestore);
	}
	
	function TrashDelete(id,enc){
		document.getElementById('date-'+id).style.color = '#CCCCCC';
		document.getElementById('uid-'+id).style.color = '#CCCCCC';
		document.getElementById('file-'+id).style.color = '#CCCCCC';
		document.getElementById('size-'+id).style.color = '#CCCCCC';
		var XHR = new XHRConnection();
		XHR.appendData('TrashDelete',enc);
		XHR.sendAndLoad('$page', 'POST',x_TrashRestore);
	}	
	
	function ScanTrashs(){
		var XHR = new XHRConnection();
		XHR.appendData('TrashScan','yes');
		AnimateDiv('trash-samba-list');
		XHR.sendAndLoad('$page', 'POST',x_ScanTrashs);	
	}
	
</script>	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function TrashRestore(){
	$path=base64_decode($_POST["TrashRestore"]);
	$q=new mysql();
	$sql="SELECT path FROM samba_recycle_bin_list WHERE path='$path'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["path"]==null){echo $path." \nNo such file !";return;}
	$sql="UPDATE samba_recycle_bin_list SET restore=1 WHERE path='$path'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("samba.php?trash-restore=yes");
	
	
}

function TrashDelete(){
	$path=base64_decode($_POST["TrashDelete"]);
	$q=new mysql();
	$sql="SELECT path FROM samba_recycle_bin_list WHERE path='$path'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["path"]==null){echo $path." \nNo such file !";return;}
	$sql="UPDATE samba_recycle_bin_list SET delete=1 WHERE path='$path'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("samba.php?trash-delete=yes");	
}

function TrashScan(){
$sock=new sockets();
	$sock->getFrameWork("samba.php?trash-scan=yes");		
}
