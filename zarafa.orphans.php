<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.cron.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["zarafa-orhpans-list"])){slist();exit;}
	if(isset($_POST["ZarafaStoreDelete"])){ZarafaStoreDelete();exit;}
	if(isset($_POST["ZarafaStoreLink"])){ZarafaStoreLink();exit;}
	if(isset($_POST["ZarafaStoreScan"])){ZarafaStoreScan();exit;}
	if(isset($_GET["js"])){js();exit;}
popup();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->javascript_parse_text("{orphans}");
	$html="YahooWin5('650','$page','$title');";
	echo $html;
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<div class=explain>{zarafa_orphans_explain}</div>
	<center>
	<table style='width:80%' class=form>
	<tr>
		<td class=legend>{member}:</td>
		<td>". Field_text("orphan-search",null,"font-size:14px;padding:3px",null,null,null,false,"ZarafaOrphansShowCheck(event)")."</td>
		<td width=1%>". button("{search}","ZarafaOrphansShow()")."</td>
	</tr>
	</table>
	</center>
	<div id='zarafa-orhpans-list' style='width:100%;height:550px;overflow:auto'></div>
	
	
	<script>
	function ZarafaOrphansShowCheck(e){
			if(checkEnter(e)){ZarafaOrphansShow();}
		}
	
		function ZarafaOrphansShow(){
			var se=escape(document.getElementById('orphan-search').value);
			LoadAjax('zarafa-orhpans-list','$page?zarafa-orhpans-list=yes&search='+se);
		}
		ZarafaOrphansShow();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function slist(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();
	$deleteTXT=$tpl->javascript_parse_text("{delete}");
	$sql="SELECT * FROM zarafa_orphaned ORDER BY size DESC LIMIT 0,100";
	
	if($_GET["search"]<>null){
		$_GET["search"]=str_replace("*", "%", $_GET["search"]);
		$sql="SELECT * FROM zarafa_orphaned WHERE uid LIKE '{$_GET["search"]}' OR storeid LIKE '{$_GET["search"]}' ORDER BY size DESC LIMIT 0,100";
	}
	
$results=$q->QUERY_SQL($sql,"artica_backup");	
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% align='center'>". imgtootltip("32-redo.png","{analyze}","OrphanReScan()")."</th>
		<th width=1%>{date}</th>
		<th width=99%>{member}/{store}</th>
		<th>{link}</th>
		<th>{size}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne["uid"]=trim($ligne["uid"]);
		$color="black";
		$THIS_USER_DOES_NOT_EXISTS=null;
		$delete=imgtootltip("delete-32.png","{delete}:{$ligne["storeid"]}","ZarafaStoreDelete('{$ligne["storeid"]}')");
		
		$date=strtotime($ligne["zDate"]);
		$distanceOfTimeInWords=distanceOfTimeInWords($date,time());	
		$users=new user($ligne["uid"]);
		$relink=imgtootltip("32-backup.png","{link}","ZarafaStoreLink('{$ligne["storeid"]}','{$ligne["uid"]}')");
		if($users->mail==null){
			$THIS_USER_DOES_NOT_EXISTS="<div style='color:#891C1C;font-size:11px;font-weight:bold'><i>{THIS_USER_DOES_NOT_EXISTS}</i></div>";
			$color="#CCCCCC";
			$relink="&nbsp;";
		}
		$ligne["size"]=FormatBytes($ligne["size"]/1024);
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:14px;color:$color' nowrap colspan=2>{$ligne["zDate"]}</a></td>
		<td style='font-size:14px;color:$color'><strong>{$ligne["uid"]}</strong></a>$THIS_USER_DOES_NOT_EXISTS<div style='font-size:11px'><i>{$ligne["storeid"]}<br>$distanceOfTimeInWords</i></td>
		<td style='font-size:14px;color:$color'>$relink</td>
		<td style='font-size:14px;color:$color'>{$ligne["size"]}</a></td>
		<td width=1%>$delete</td>
		</tr>
		
		";
		
		
	}

	$html=$html."</tbody></table>
		<script>
	var x_ZarafaStoreDelete= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
	    	ZarafaOrphansShow();
		}	

		function ZarafaStoreDelete(gpname){
			if(confirm('$deleteTXT '+gpname+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('ZarafaStoreDelete',gpname);
			AnimateDiv('zarafa-orhpans-list');
			XHR.sendAndLoad('$page', 'POST',x_ZarafaStoreDelete);
			}
		}
		
		function ZarafaStoreLink(storeid,uid){
			if(confirm(storeid+' --> '+uid+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('ZarafaStoreLink',storeid);
			XHR.appendData('uid',uid);
			AnimateDiv('zarafa-orhpans-list');
			XHR.sendAndLoad('$page', 'POST',x_ZarafaStoreDelete);
			}
		}

		function OrphanReScan(){
			var XHR = new XHRConnection();
			XHR.appendData('ZarafaStoreScan','yes');
			AnimateDiv('zarafa-orhpans-list');
			XHR.sendAndLoad('$page', 'POST',x_ZarafaStoreDelete);		
		}
		
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function ZarafaStoreDelete(){
	$sql="DELETE FROM zarafa_orphaned WHERE storeid='{$_POST["ZarafaStoreDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("zarafa.php?zarafa-orphan-kill={$_POST["ZarafaStoreDelete"]}");
	
	
}

function ZarafaStoreLink(){
	$sql="DELETE FROM zarafa_orphaned WHERE storeid='{$_POST["ZarafaStoreLink"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("zarafa.php?zarafa-orphan-link={$_POST["ZarafaStoreLink"]}&uid={$_POST["uid"]}");	
}

function ZarafaStoreScan(){
$sock=new sockets();
	$sock->getFrameWork("zarafa.php?zarafa-orphan-scan=yes");		
}

