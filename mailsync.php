<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.cron.inc');

	
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	if(!permissions()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["script"])){popup_script();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["remote_imap_server"])){add_account();exit;}
	if(isset($_GET["imapsynclist"])){imapsynclist();exit;}
	if(isset($_GET["imapSyncDelete"])){imapSyncDelete();exit;}
	if(isset($_GET["AddForm"])){add_popup();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["schedule"])){schedule();exit;}
	if(isset($_GET["imapsync_save_schedule"])){schedule_save();exit;}
	if(isset($_GET["imapRun"])){imapRun();exit;}
	if(isset($_GET["imapStop"])){imapStop();exit;}
	if(isset($_GET["toolbox"])){toolbox();exit;}
	if(isset($_GET["imapsync-folders"])){folders_sync_form();exit;}
	if(isset($_GET["folder-to-sync"])){folders_sync_add();exit;}
	if(isset($_GET["folder-to-sync-del"])){folders_sync_del();exit;}
	if(isset($_GET["imapsync-adv"])){adv_options_form();exit;}
	if(isset($_GET["syncinternaldate"])){adv_options_save();exit;}
	
js();

function popup_script(){
	$uid=$_GET["uid"];
	$page=CurrentPageName();
	$id=$_GET["id"];
	
	if(!is_numeric($id)){echo "<H2>!!??</H2>
	<script>
		LoadAjax('imapsync-toolbox','$page?toolbox=yes&ID=$id&uid=$uid&width-folder-params=yes');
	</script>";
	exit;
	}
	
	$sock=new sockets();
	$cmdline=base64_decode($sock->getFrameWork("cmd.php?imapsync-show=$id"));
	$r=explode("\n",$cmdline);
	if(count($r)>0){
		$cmdline=null;
		while (list($key,$val)=each($r)){
			$cmdline=$cmdline."<div><code>$val&nbsp;</code></div>";
		}
		
	}
	
	
	echo "
	<div style='margin-top:10px;height:350px;overflow:auto'>
	<code style='font-size:13px'>$cmdline</code>
	</div>
	<script>
		LoadAjax('imapsync-toolbox','$page?toolbox=yes&ID=$id&uid=$uid&width-folder-params=yes');
	</script>
	";
	
}

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{import_mailbox}","domains.edit.users.php");
	$add=$tpl->_ENGINE_parse_body("{add}");
	$edit=$tpl->_ENGINE_parse_body("{edit}");
	$events=$tpl->_ENGINE_parse_body("{events}");
	$schedule=$tpl->_ENGINE_parse_body("{schedule}");
	$apply_upgrade_help=$tpl->javascript_parse_text("{apply_upgrade_help}");
	$command_lines_view=$tpl->_ENGINE_parse_body("{command_lines_view}");
	$page=CurrentPageName();
	$uid=$_GET["uid"];
	
	$html="
	  var mem_ID='';
	
		function import_mailbox_run(){
			YahooWin5(600,'$page?popup=yes&uid={$_GET["uid"]}','$title');
		
		}
		
	var x_impasync_add=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      YahooWin6Hide();
      imapsynclist();
      if(mem_ID>0){ImapSyncEdit(mem_ID);}
      
     }	

    function AddForm(){
    	YahooWin6(700,'$page?AddForm=yes&uid=$uid','$add');
	} 

    function ImapSyncEdit(ID){
    	YahooWin6(700,'$page?AddForm=yes&uid=$uid&ID='+ID,'$edit::$uid::'+ID);
	} 	
	
	function imapSyncEvents(id){
		YahooWin6(700,'$page?events=yes&uid=$uid&id='+id,'$events');
	}
	
	function imapSyncSchedule(id){
		YahooWin6(245,'$page?schedule=yes&uid=$uid&id='+id,'$schedule');
	}
	
	function imapsync_script(id){
		LoadAjax('imapsyncadddiv','$page?script=yes&uid=$uid&id='+id);
	}
	
	
		
	function impasync_add(){
			var XHR = new XHRConnection();
			mem_ID=document.getElementById('ID').value;
			XHR.appendData('uid','{$_GET["uid"]}');
			
			XHR.appendData('ID',document.getElementById('ID').value);
			XHR.appendData('remote_imap_server',document.getElementById('remote_imap_server').value);
			XHR.appendData('remote_imap_username',document.getElementById('remote_imap_username').value);
			XHR.appendData('remote_imap_password',document.getElementById('remote_imap_password').value);
			if(document.getElementById('use_ssl').checked){XHR.appendData('use_ssl',1);}else{XHR.appendData('use_ssl',0);}
			
			if(document.getElementById('local_mailbox')){if(document.getElementById('local_mailbox').checked){XHR.appendData('local_mailbox',1);}else{XHR.appendData('local_mailbox',0);}}
			XHR.appendData('dest_imap_server',document.getElementById('dest_imap_server').value);
			XHR.appendData('dest_imap_username',document.getElementById('dest_imap_username').value);
			XHR.appendData('dest_imap_password',document.getElementById('dest_imap_password').value);
			if(document.getElementById('dest_use_ssl').checked){XHR.appendData('dest_use_ssl',1);}else{XHR.appendData('dest_use_ssl',0);}
			if(document.getElementById('delete_messages').checked){XHR.appendData('delete_messages',1);}else{XHR.appendData('delete_messages',0);}
			
			if(document.getElementById('local_mailbox_source')){if(document.getElementById('local_mailbox_source').checked){XHR.appendData('local_mailbox_source',1);}else{XHR.appendData('local_mailbox_source',0);}}
			
			
			document.getElementById('imapsyncadddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add);
			 
			}

	function imapsynclist(){
		LoadAjax('imapsynclist','$page?imapsynclist=yes&uid={$_GET["uid"]}');
	}
	
	function imapSyncDelete(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapSyncDelete',id);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add); 
	}
	
	function imapRun(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapRun',id);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_run); 	
	}
	
	var x_impasync_run=function(obj){
     var tempvalue=obj.responseText;
     alert('$apply_upgrade_help');
     imapsynclist();
      
     }	

	function imapStop(pid){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapStop',pid);
			document.getElementById('imapsynclist').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_run); 	
	}     
	
     
    function imapsync_folders(ID){
			LoadAjax('imapsyncadddiv','$page?imapsync-folders=yes&uid={$_GET["uid"]}&ID='+ID);
    
	}
	
    function imapsync_adv(ID){
			LoadAjax('imapsyncadddiv','$page?imapsync-adv=yes&uid={$_GET["uid"]}&ID='+ID);
    
	}	
	
	
	
	
	function imapsync_save_schedule(id){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('imapsync_save_schedule',document.getElementById('schedule').value);
			XHR.appendData('id',id);
			document.getElementById('imapsync_save_schedule_div').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_impasync_add); 
	}
	
	
	import_mailbox_run()";
	
	echo $html;
	
	
	
}



function add_popup(){
	$uid=$_GET["uid"];	
	$ID=$_GET["ID"];
	$title="{add_new_account}";
	$button_title="{add}";
	$page=CurrentPageName();
	
	
	$user=new usersMenus();
	if(!$user->imapsync_installed){
		$content=Paragraphe('add-remove-64.png','{imapsync_not_installed}','{imapsync_not_installed_text}',null,null,290);
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	
	if($ID>0){
		$q=new mysql();
		$sql="SELECT * FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$parameters=unserialize(base64_decode($ligne["parameters"]));
		$remote_imap_server=$ligne["imap_server"];
		$remote_imap_username=$ligne["username"];
		$remote_imap_password=$ligne["password"];
		$use_ssl=$ligne["ssl"];
  		$title="{edit}:$remote_imap_server";
		$button_title="{apply}";
	}

	if($user->cyrus_imapd_installed){$local_mailbox=true;}
	if($user->ZARAFA_INSTALLED){$local_mailbox=true;}
	
	if($local_mailbox){
		if($parameters["local_mailbox"]==null){$parameters["local_mailbox"]=1;}
		$tr_local_mailbox_switch="
		<tr>
			<td class=legend nowrap style='font-size:13px'>{local_mailbox}:</td>
			<td>". Field_checkbox("local_mailbox",1,$parameters["local_mailbox"],"tr_local_mailbox_switch()")."</td>
		</tr>";
		
		if($parameters["local_mailbox_source"]==null){$parameters["local_mailbox_source"]=0;}
		
		$tr_local_mailbox_source_switch="
		<tr>
			<td class=legend nowrap style='font-size:13px'>{local_mailbox}:</td>
			<td>". Field_checkbox("local_mailbox_source",1,$parameters["local_mailbox_source"],"tr_local_mailbox_source_switch()")."</td>
		</tr>";		
		
		
		
	}
	
	
$html=Field_hidden("ID",$ID). "
<H3>$title</H3>
<table style='width:100%'>
<tr>
<td valign='top'><div id='imapsync-toolbox'></div></td>
<td valign='top'>
<div id='imapsyncadddiv'>


		<table style='width:100%'>
		<tr><td colspan=2><h3>{remote_imap_server}</h3></td></tr>
		$tr_local_mailbox_source_switch
		<tr>
			<td class=legend nowrap style='font-size:13px'>{remote_imap_server}:</td>
			<td>" . Field_text('remote_imap_server',$remote_imap_server,"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{remote_imap_username}:</td>
			<td>" . Field_text('remote_imap_username',$remote_imap_username,"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{remote_imap_password}:</td>
			<td>" . Field_password('remote_imap_password',$remote_imap_password,"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px;'>{use_ssl}:</td>
			<td>". Field_checkbox("use_ssl",1,$use_ssl)."</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px;'>{nokeepmess}:</td>
			<td>". Field_checkbox("delete_messages",1,$parameters["delete_messages"])."</td>
		</tr>				
		
		<tr><td colspan=2><hr></td></tr>
		<tr><td colspan=2><h3>{dest_imap_server}</h3></td></tr>
		$tr_local_mailbox_switch
		<tr>
			<td class=legend nowrap style='font-size:13px'>{dest_imap_server}:</td>
			<td>" . Field_text('dest_imap_server',$parameters["dest_imap_server"],"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{dest_imap_username}:</td>
			<td>" . Field_text('dest_imap_username',$parameters["dest_imap_username"],"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{dest_imap_password}:</td>
			<td>" . Field_password('dest_imap_password',$parameters["dest_imap_password"],"font-size:13px;padding:3px",null) . "</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px;'>{use_ssl}:</td>
			<td>". Field_checkbox("dest_use_ssl",1,$parameters["dest_use_ssl"])."</td>
		</tr>	
				
		
		<tr>
			<td colspan=2 align='right'>
			<hr>
			". button("$button_title","impasync_add()")."
			</td>
		</tr>
</table>
</div>
</td>
</tr>
</table>
<script>
	tr_local_mailbox_switch();
	tr_local_mailbox_source_switch();
	imapsync_toolbox();
	function tr_local_mailbox_switch(){
		if(!document.getElementById('local_mailbox')){return;}
		if(document.getElementById('local_mailbox').checked){
			document.getElementById('dest_imap_server').disabled=true;
			document.getElementById('dest_imap_username').disabled=true;
			document.getElementById('dest_imap_password').disabled=true;
			document.getElementById('dest_use_ssl').disabled=true;
		}else{	
			document.getElementById('dest_imap_server').disabled=false;
			document.getElementById('dest_imap_username').disabled=false;
			document.getElementById('dest_imap_password').disabled=false;
			document.getElementById('dest_use_ssl').disabled=false;		
		}
	}
	
	function tr_local_mailbox_source_switch(){
	  if(!document.getElementById('local_mailbox_source')){return;}
		if(document.getElementById('local_mailbox_source').checked){
			document.getElementById('remote_imap_server').disabled=true;
			document.getElementById('remote_imap_username').disabled=true;
			document.getElementById('remote_imap_password').disabled=true;
			
		}else{
			document.getElementById('remote_imap_server').disabled=false;
			document.getElementById('remote_imap_username').disabled=false;
			document.getElementById('remote_imap_password').disabled=false;				
		}	
	}	

	
	function imapsync_toolbox(){
		LoadAjax('imapsync-toolbox','$page?toolbox=yes&ID=$ID&uid=$uid');
	
	}
	
	

</script>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function adv_options_save(){
		$uid=$_GET["uid"];
		$ID=$_GET["ID"];
		$page=CurrentPageName();	
		$q=new mysql();
		$sql="SELECT `parameters` FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["parameters"]));	
	
		while (list($key,$val)=each($_GET)){
			$array[$key]=$val;
		}
		
		$parameters=base64_encode(serialize($array));
		
		$sql="UPDATE imapsync SET `parameters`='$parameters' WHERE `uid`='$uid' AND ID='$ID'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;echo "\n$sql";}
}

function adv_options_form(){
		$uid=$_GET["uid"];
		$ID=$_GET["ID"];
		$page=CurrentPageName();	
		$q=new mysql();
		$sql="SELECT `parameters` FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["parameters"]));
		$users=new usersMenus();
		
		if($array["syncinternaldates"]==null){$array["syncinternaldates"]=1;}
		if($array["noauthmd5"]==null){$array["noauthmd5"]=1;}
		if($array["allowsizemismatch"]==null){$array["allowsizemismatch"]=1;}
		if($array["nosyncacls"]==null){$array["nosyncacls"]=1;}
		if($array["nofoldersizes"]==null){$array["nofoldersizes"]=1;}
		if($array["skipsize"]==null){$array["skipsize"]=0;}
		if($array["maxage"]==null){$array["maxage"]=0;}
		if($array["UseOfflineImap"]==null){$array["UseOfflineImap"]=0;}
		
		for($i=0;$i<365;$i++){
			$maxage_array[$i]=$i;
		}
		
		if(!$users->offlineimap_installed){
			$script_offlineimap="DiableOfflineImap();OfflineImapSwitch();";
			$array["UseOfflineImap"]=0;
		}else{
			$script_offlineimap="OfflineImapSwitch();";
		}
		
		
		$html="
		<table style='width:100%'>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{UseOfflineImap}:</td>
			<td>&nbsp;</td>
			<td>". Field_checkbox("UseOfflineImap",1,$array["UseOfflineImap"],"OfflineImapSwitch()")."</td>
			<td width=1%>". help_icon("{UseOfflineImap_text}")."</td>
		</tr>		
		<tr>
			<td class=legend nowrap style='font-size:13px'>{syncinternaldate}:</td>
			<td>&nbsp;</td>
			<td>". Field_checkbox("syncinternaldate",1,$array["syncinternaldates"])."</td>
			<td width=1%>". help_icon("{syncinternaldate_text}")."</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{noauthmd5}:</td>
			<td>&nbsp;</td>
			<td align='left'>". Field_checkbox("noauthmd5",1,$array["noauthmd5"])."</td>
			<td width=1%>". help_icon("{noauthmd5_text}")."</td>
		</tr>	
		<tr>
			<td class=legend nowrap style='font-size:13px'>{nosyncacls}:</td>
			<td>&nbsp;</td>
			<td align='left'>". Field_checkbox("nosyncacls",1,$array["nosyncacls"])."</td>
			<td width=1%>&nbsp;</td>
		</tr>		
		<tr>
			<td class=legend nowrap style='font-size:13px'>{allowsizemismatch}:</td>
			<td>&nbsp;</td>
			<td align='left'>". Field_checkbox("allowsizemismatch",1,$array["allowsizemismatch"])."</td>
			<td width=1%>". help_icon("{allowsizemismatch_text}")."</td>
		</tr>
		<tr>
			<td class=legend nowrap style='font-size:13px'>{skipsize}:</td>
			<td>&nbsp;</td>
			<td>". Field_checkbox("skipsize",1,$array["skipsize"])."</td>
			<td width=1%>". help_icon("{skipsize_text}")."</td>
		</tr>
		</tr>
					
		<tr>
			<td class=legend nowrap style='font-size:13px'>{nofoldersizes}:</td>
			<td width=1% align='left'>&nbsp;</td>
			<td>". Field_checkbox("nofoldersizes",1,$array["nofoldersizes"])."</td>
			<td width=1%>". help_icon("{nofoldersizes_text}")."</td>
		</tr>
		
		<tr>
			<td class=legend nowrap style='font-size:13px;' nowrap>{imapsync_prefix_source}:</td>
			<td width=1% align='left'>".Field_checkbox("usePrefix1",1,$array["usePrefix1"],"SwitchOnOffSep()")."</td>
			<td >". Field_text("prefix1",$array["prefix1"])."</td>
			<td width=1%>". help_icon("{imapsync_prefix_text}")."</td>
		</tr>			
		
		<tr>
			<td class=legend nowrap style='font-size:13px;' nowrap>{mailbox_separator_source}:</td>
			<td width=1% align='left'>".Field_checkbox("useSep1",1,$array["useSep1"],"SwitchOnOffSep()")."</td>
			<td >". Field_text("sep",$array["sep"])."</td>
			<td width=1%>". help_icon("{mailbox_separator_text}")."</td>
		</tr>
		
		
		<tr>
			<td class=legend nowrap style='font-size:13px;' nowrap>{imapsync_prefix_destination}:</td>
			<td width=1% align='left'>".Field_checkbox("usePrefix2",1,$array["usePrefix2"],"SwitchOnOffSep()")."</td>
			<td >". Field_text("prefix2",$array["prefix2"])."</td>
			<td width=1%>". help_icon("{imapsync_prefix_text}")."</td>
		</tr>		

		
		<tr>
			<td class=legend nowrap style='font-size:13px;' nowrap>{mailbox_separator_destination}:</td>
			<td width=1% align='left'>".Field_checkbox("useSep2",1,$array["useSep2"],"SwitchOnOffSep()")."</td>
			<td>". Field_text("sep2",$array["sep2"])."</td>
			<td width=1%>". help_icon("{mailbox_separator_text}")."</td>
		</tr>	

		<tr>
			<td class=legend nowrap style='font-size:13px'>{maxage}:</td>
			<td style='font-size:13px' colspan=2>". Field_array_Hash($maxage_array,"maxage",$array["maxage"],null,null,0,"font-size:13px;padding:3px;width:60px")."&nbsp;{days}</td>
			<td width=1%>". help_icon("{maxage_text}")."</td>
		</tr>			
		
		<tr>
			<td colspan=3 align='right'><hr>". button("{apply}","ImapSyncSaveAdv()")."</td>
		</tr>
		</table>
		
		<script>
		function imapsync_toolbox_adv(){
			SwitchOnOffSep();
			LoadAjax('imapsync-toolbox','$page?toolbox=yes&ID=$ID&uid=$uid&width-folder-params=yes');
		}
		
	var x_ImapSyncSaveAdv=function(obj){
	     var tempvalue=obj.responseText;
	     if(tempvalue.length>3){alert(tempvalue);}
	     imapsync_adv($ID);
	      
	     }	

	     
	 function SwitchOnOffSep(){
	 	document.getElementById('sep').disabled=true;
	 	document.getElementById('sep2').disabled=true;
	 	
	 	document.getElementById('prefix1').disabled=true;
	 	document.getElementById('prefix2').disabled=true;	 	
	 	
	 	if(document.getElementById('useSep1').checked){document.getElementById('sep').disabled=false;}
		if(document.getElementById('useSep2').checked){document.getElementById('sep2').disabled=false;}
	 	if(document.getElementById('usePrefix1').checked){document.getElementById('prefix1').disabled=false;}
		if(document.getElementById('usePrefix2').checked){document.getElementById('prefix2').disabled=false;}		
	 
	}
	
	function DiableOfflineImap(){
		document.getElementById('UseOfflineImap').disabled=false;
		
	}
	
	function OfflineImapSwitch(){
		if(document.getElementById('UseOfflineImap').checked){
			document.getElementById('syncinternaldate').disabled=true;
			document.getElementById('skipsize').disabled=true;
			document.getElementById('nosyncacls').disabled=true;
			document.getElementById('allowsizemismatch').disabled=true;
			document.getElementById('noauthmd5').disabled=true;
			document.getElementById('nofoldersizes').disabled=true;
			

		}else{
			document.getElementById('syncinternaldate').disabled=false;
			document.getElementById('skipsize').disabled=false;
			document.getElementById('nosyncacls').disabled=false;
			document.getElementById('allowsizemismatch').disabled=false;
			document.getElementById('noauthmd5').disabled=false;
			document.getElementById('nofoldersizes').disabled=false;
		}
	}
	     
		
		function ImapSyncSaveAdv(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','$uid');
			XHR.appendData('ID','$ID');
			if(document.getElementById('syncinternaldate').checked){XHR.appendData('syncinternaldate',1);}else{XHR.appendData('syncinternaldate',0);}
			if(document.getElementById('noauthmd5').checked){XHR.appendData('noauthmd5',1);}else{XHR.appendData('noauthmd5',0);}
			if(document.getElementById('allowsizemismatch').checked){XHR.appendData('allowsizemismatch',1);}else{XHR.appendData('allowsizemismatch',0);}
			if(document.getElementById('skipsize').checked){XHR.appendData('skipsize',1);}else{XHR.appendData('skipsize',0);}
			if(document.getElementById('nosyncacls').checked){XHR.appendData('nosyncacls',1);}else{XHR.appendData('nosyncacls',0);}
			if(document.getElementById('nofoldersizes').checked){XHR.appendData('nofoldersizes',1);}else{XHR.appendData('nofoldersizes',0);}
			XHR.appendData('sep',document.getElementById('sep').value);
			XHR.appendData('sep2',document.getElementById('sep2').value);
			if(document.getElementById('useSep2').checked){XHR.appendData('useSep2',1);}else{XHR.appendData('useSep2',0);}
			if(document.getElementById('useSep1').checked){XHR.appendData('useSep1',1);}else{XHR.appendData('useSep1',0);}
			if(document.getElementById('UseOfflineImap').checked){XHR.appendData('UseOfflineImap',1);}else{XHR.appendData('UseOfflineImap',0);}
			
			if(document.getElementById('usePrefix1').checked){XHR.appendData('usePrefix1',1);}else{XHR.appendData('usePrefix1',0);}
			if(document.getElementById('usePrefix2').checked){XHR.appendData('usePrefix2',1);}else{XHR.appendData('usePrefix2',0);}			
			XHR.appendData('prefix1',document.getElementById('prefix1').value);
			XHR.appendData('prefix2',document.getElementById('prefix2').value);
			XHR.appendData('maxage',document.getElementById('maxage').value);		
			document.getElementById('imapsyncadddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ImapSyncSaveAdv); 	
		}		
		
		imapsync_toolbox_adv();
		$script_offlineimap
		</script>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
		
		
}

function folders_sync_form(){
		$uid=$_GET["uid"];
		$ID=$_GET["ID"];
		$page=CurrentPageName();
		
		$q=new mysql();
		$sql="SELECT `folders` FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["folders"]));
		if(count($array["FOLDERS"])==0){$array["FOLDERS"][]="{all}";}
		$html="
		<table style='width:350px'>
		<tr>
			<td class=legend style='font-size:13px'>{add}:</td>
			<td>". Field_text("folder-sync-add",null,"font-size:13px;padding:3px;",null,null,null,false,"ImapSyncAddFolderPress(event)")."</td>
		</tr>
		</table>
		<hr>
		
		<table style='width:350px'>";
		while (list($num,$folder)=each($array["FOLDERS"])){
			$delete=imgtootltip("delete-24.png","{delete}","ImapSyncDelFolder($num)");
			if($folder=="{all}"){$delete=null;}
			
			$html=$html."
			<tr ". CellRollOver().">
			<td width=1%><img src='img/folder-network2-22.png'></td>
			<td width=99%><strong style='font-size:13px'>$folder</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
		$html=$html."
		</table>
		
		<script>
		function imapsync_toolbox_Folders(){
			LoadAjax('imapsync-toolbox','$page?toolbox=yes&ID=$ID&uid=$uid&width-folder-params=yes');
		}
		
		function ImapSyncAddFolderPress(e){
			if(!checkEnter(e)){return;}
			ImapSyncAddFolder();
		}
		
		
	var x_ImapSyncAddFolder=function(obj){
	     var tempvalue=obj.responseText;
	     if(tempvalue.length>3){alert(tempvalue);}
	     imapsync_folders($ID);
	      
	     }			
		
		
		function ImapSyncAddFolder(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','$uid');
			XHR.appendData('ID','$ID');
			XHR.appendData('folder-to-sync',document.getElementById('folder-sync-add').value);
			document.getElementById('imapsyncadddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ImapSyncAddFolder); 	
		}
		
		function ImapSyncDelFolder(NUM){
			var XHR = new XHRConnection();
			XHR.appendData('uid','$uid');
			XHR.appendData('ID','$ID');
			XHR.appendData('folder-to-sync-del',NUM);
			document.getElementById('imapsyncadddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ImapSyncAddFolder); 		
		}
		
		
		imapsync_toolbox_Folders();
		</script>
		";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	}
	
function folders_sync_add(){
		$uid=$_GET["uid"];
		$ID=$_GET["ID"];
		$page=CurrentPageName();
		
		$q=new mysql();
		$sql="SELECT `folders` FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["folders"]));	
		$array["FOLDERS"][]=$_GET["folder-to-sync"];
		
		$base=base64_encode(serialize($array));
		$sql="UPDATE imapsync SET `folders`='$base' WHERE `uid`='$uid' AND ID='$ID'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;echo "\n$sql";}
}
function folders_sync_del(){
		$uid=$_GET["uid"];
		$ID=$_GET["ID"];
		$page=CurrentPageName();
		
		$q=new mysql();
		$sql="SELECT `folders` FROM imapsync WHERE `uid`='$uid' AND ID='$ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["folders"]));	
		unset($array["FOLDERS"][$_GET["folder-to-sync-del"]]);
		
		$base=base64_encode(serialize($array));
		$sql="UPDATE imapsync SET `folders`='$base' WHERE `uid`='$uid' AND ID='$ID'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;echo "\n$sql";}
}




function toolbox(){
	$ID=$_GET["ID"];
	$users=new usersMenus();
	if($ID<1){return;}
	$folders=Paragraphe("folder-network2-64.png","{mailbox_folders}","{imapsync_folders_text}","javascript:imapsync_folders('$ID')");
	$adv_options=Paragraphe("parameters2-64.png","{advanced_options}","{imapsync_advanced_options}","javascript:imapsync_adv('$ID')");
	
	
	if(isset($_GET["width-folder-params"])){
		$folders=Paragraphe("64-parameters.png","{main_settings}","{imapsync_main_settings}","javascript:ImapSyncEdit('$ID')");
		
	}
	if($users->AsAnAdministratorGeneric){
		$script_view="<br>".Paragraphe("script-view-64.png","{command_lines_view}","{view_generated_command_lines}",
		"javascript:imapsync_script('$ID')");
	}

	$html=$folders."<br>$adv_options$script_view";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function popup(){
$uid=$_GET["uid"];	
$user=new usersMenus();
if(!$user->imapsync_installed){
	$content=Paragraphe('add-remove-64.png','{imapsync_not_installed}','{imapsync_not_installed_text}',null,null,290);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	exit;
}


$html="
<p style='font-size:13px'>
<span style='float:right;padding:3px;'>". button("{add}","AddForm()")."</span>
{import_mailbox_text}</p>
<div style='text-align:right'>". imgtootltip("20-refresh.png","{refresh}","imapsynclist()")."</div>
<div id='imapsynclist' style='width:100%;height:250px;overflow:auto'></div>


<script>
	imapsynclist();
</script>
";


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function add_account(){
	$parameters=base64_encode(serialize($_GET));
	$q=new mysql();
	
	$sql="INSERT INTO imapsync (`uid`,`imap_server`,`username`,`password`,`ssl`,`enabled`,`parameters`) VALUES('{$_GET["uid"]}',
	'{$_GET["remote_imap_server"]}','{$_GET["remote_imap_username"]}','{$_GET["remote_imap_password"]}',
	'{$_GET["use_ssl"]}','1','$parameters')";
	
	if($_GET["ID"]>0){
		
		$sql="SELECT `parameters` FROM imapsync WHERE `uid`='{$_GET["uid"]}' AND ID='{$_GET["ID"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$array=unserialize(base64_decode($ligne["parameters"]));	
		
		while (list($key,$val)=each($_GET)){
			$array[$key]=$val;
		}		
		$parameters_edit=base64_encode(serialize($array));
		
		$sql="UPDATE imapsync SET `imap_server`='{$_GET["remote_imap_server"]}',
		`username`='{$_GET["remote_imap_username"]}',
		`password`='{$_GET["remote_imap_password"]}',
		`ssl`='{$_GET["use_ssl"]}',
		`parameters`='$parameters_edit' WHERE ID={$_GET["ID"]}";
	}
	

	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;echo "\n$sql";}
			
}


function imapsynclist(){
	$sock=new sockets();
	
	$cron=new cron_macros();
	while (list ($index, $line) = each ($cron->cron_defined_macros) ){
		if($index==0){continue;}
		
		$retour[$line]=$index;
	}	
	
	
	$sql="SELECT * FROM imapsync WHERE uid='{$_GET["uid"]}'";
	$html="<table style='width:99%'>
	<tr>
		<th>{status}</th>
		<th>{server}</th>
		<th>{username}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	
	";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
 	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
 		$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?procstat={$ligne["pid"]}")));
 		if($array["since"]==null){$ligne["state"]=0;}
 		switch ($ligne["state"]) {
 			case -1:$img="status_service_removed.png";$text="{error}";$run=imgtootltip("run-24.png","{run}","imapRun({$ligne["ID"]})");break;
 			case 0: $img="status_service_wait.png";$text="{sleeping}";$run=imgtootltip("run-24.png","{run}","imapRun({$ligne["ID"]})");break;
 			case 1: $img="status_service_run.png";$text="{running}: pid {$ligne["pid"]}";$run=imgtootltip("x-delete.gif","{stop}","imapStop({$ligne["ID"]})");break;

 			
 			
 		}
 	 	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?procstat={$ligne["pid"]}")));
 		if($array["since"]<>null){
 			$running="{running}: {since} {$array["since"]}";
 		} 		
 		
 		if($ligne["CronSchedule"]<>null){$sched="<br>{each}:".$retour[$ligne["CronSchedule"]];}else{$sched=null;}
 	
 		$status=imgtootltip($img,$text."<br>$running","imapSyncEvents({$ligne["ID"]})");
 		$schedule=imgtootltip("time-30.png","{schedule}$sched","imapSyncSchedule({$ligne["ID"]})");
 		$settings=imgtootltip("settings-30.png","{edit}","ImapSyncEdit({$ligne["ID"]})");
 		
 		
 		
 		$html=$html."
 		<tr ". CellRollOver().">
 			<td width=1% align='center'>$status</td>
 			<td><code style='font-size:14px'>{$ligne["imap_server"]}</code></td>
 			<td><code style='font-size:14px'>{$ligne["username"]}</code></td>
 			<td width=1% align='center'>$settings</td>
 			<td width=1% align='center'>$schedule</code></td>
 			<td width=1% align='center'>$run</td>
 			<td width=1% align='center'>". imgtootltip("delete-32.png","{delete}","imapSyncDelete({$ligne["ID"]})")."</td>
 		</tr>
 			
 		";
 	}
	
 	$html=$html."</table>";
 	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function imapSyncDelete(){
	$sql="DELETE FROM imapsync WHERE ID={$_GET["imapSyncDelete"]}";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-cron=yes");	
	
}

function events(){
	$tr=array();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?imapsync-events={$_GET["id"]}")));
	
	if(is_array($datas)){
		while (list ($index, $line) = each ($datas) ){
			if($line==null){continue;}
			$tr[]="<div style='font-size:12px;padding:3px'>".htmlspecialchars($line)."</div>";
		}
	}
	
	$html="
	<div style='width:100%;height:450px;overflow:auto;'>".implode("\n",$tr)."</div>";
	echo $html;
}

function schedule(){
	$cron=new cron_macros();
	while (list ($index, $line) = each ($cron->cron_defined_macros) ){
		if($index==0){continue;}
		$array[$index]=$index;
		$retour[$line]=$index;
	}
	
$sql="SELECT CronSchedule FROM imapsync WHERE ID='{$_GET["id"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	
	$array[null]="{disabled}";
	
	$field=Field_array_Hash($array,"schedule",$retour[$ligne["CronSchedule"]],null,null,0,"font-size:15px");

	$html="
	<div id='imapsync_save_schedule_div'>
	<H3>{schedule}</H3>
	<table style='width:100%'>
	<tr>
		<td class=legend>{each}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{apply}","imapsync_save_schedule('{$_GET["id"]}')")."
		</td>
	</tr>
	</table>

	</div>";
	

	 	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function schedule_save(){
	$cron=new cron_macros();
	$data=$cron->cron_defined_macros[$_GET["imapsync_save_schedule"]];
	$sql="UPDATE imapsync SET CronSchedule='$data' WHERE ID='{$_GET["id"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-cron=yes");
	}
	
function imapRun(){
	$id=$_GET["imapRun"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-run=$id");	
}
function imapStop(){
	$id=$_GET["imapStop"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?imapsync-stop=$id");		
}

	
	
function permissions(){

	//sync-64.png
	//imgtootltip("icon_sync.gif",'{export_mailbox_text}',"Loadjs('mailsync.php?uid=$uid');") 
	
$usersprivs=new usersMenus();
if($usersprivs->AsAnAdministratorGeneric){return true;}
if(!$usersprivs->AllowFetchMails){return false;}
if($_SESSION["uid"]<>$_GET["userid"]){return false;}
return true;
	
}




?>