<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.fetchmail.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["Showlist"])){echo section_rules_list();exit;}

if(isset($_GET["ajax"])){ajax_index();exit;}
if(isset($_GET["fetchmail-daemon-rules"])){echo section_rules_list();exit;}

section_Fetchmail_Daemon();



function section_Fetchmail_Daemon(){
		
		$yum=new usersMenus();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();		
		$title="{fetchmail_rules}";
		
		$add_fetchmail=Paragraphe('add-fetchmail-64.png','{add_new_fetchmail_rule}','{fetchmail_explain}',"javascript:add_fetchmail_rules()",null,340);

	$ini->loadString($sock->getfile('fetchmailstatus'));
	$status=DAEMON_STATUS_ROUND("FETCHMAIL",$ini,null);
	$status=$tpl->_ENGINE_parse_body($status);	
		$html="<table style='width:600px'>
		<tr>
		<td valign='top' width=1%><img src='img/bg_fetchmail2.jpg'>
		<td valign='top' align='right'><div style='width:350px'>$status <br> $add_fetchmail</div></td>
		</tr>
		<td colspan=2>
		<div id='fetchmail_daemon_rules'></div>
			</td>
			</tr>			
					</table>
					
	<script>LoadAjax('fetchmail_daemon_rules','fetchmail.daemon.rules.php?Showlist=yes');</script>";
				

$tpl=new template_users($title,$html,0,0,0,0);
echo $tpl->web_page;		
	
	}
	
	
function ajax_index(){
	$page=CurrentPageName();
	$html="
	<div id='fetchmail_daemon_rules' style='width:99.5%;height:350px;overflow:auto'></div>
	<script>
		LoadAjax('fetchmail_daemon_rules','$page?fetchmail-daemon-rules=yes');
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
function section_rules_list(){
	
	if($_GET["tab"]==1){section_config();exit;}
	
	$fetch=new Fetchmail_settings();
	$rules=$fetch->LoadAllRules();
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	
	$html="";
$rd="	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:98%'>
	<thead class='thead'>
		<tr>
		<th width=5%>{date}</th>
		$th_add
		<th width=99% colsan=2>{website}</th>
		<th width=1% colspan=2>{size}</th>
		<th width=1% colspan=2>{client}</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	$classtr="";
while (list ($num, $hash) = each ($rules) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$uid=$hash["uid"];
		$user=new user($uid);
		if($hash["enabled"]==0){$img='status_ok-grey.gif';}else{$img="status_ok.gif";}
		$js="UserFetchMailRule('$num','$uid')";
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:13px;font-weight:bold;text-decoration:underline' >";
		$rd=$rd . "<tr class=$classtr>
		<td width=1%><img src='img/$img'></td>
		<td >$href{$user->mail}</a></td>
		<td >$href{$hash["poll"]}</a></td>
		<td >$href{$hash["proto"]}</a></td>
		<td >$href{$hash["user"]}</a></td>
		</tr>";
		
		
		
		
		
		
	}
	$rd=$rd . "</table>";
	
	
  $tpl=new templates();
  if(isset($_GET["ajax"])){
  	return $tpl->_ENGINE_parse_body($html . $rd);
  }
	echo $tpl->_ENGINE_parse_body($html . $rd);
	
	
}


function section_config(){
	
	$fetch=new fetchmail();
	if(isset($_GET["build"])){
		
		$fetch->Save();
		$fetch=new fetchmail();
	}
	
	$fetchmailrc=$fetch->fetchmailrc;
	$FetchGetLive=$fetch->FetchGetLive;
	$save=Paragraphe('disk-save-64.png','{generate_config}','{generate_config_text}',"javascript:LoadAjax(\"fetchmail_daemon_rules\",\"fetchmail.daemon.rules.php?Showlist=yes&tab=1&build=yes\")");
	
	$fetchmailrc=htmlentities($fetchmailrc);
	$fetchmailrc=nl2br($fetchmailrc);
	
	$FetchGetLive=htmlentities($FetchGetLive);
	$FetchGetLive=nl2br($FetchGetLive);	
	
	$tpl=new templates();
	$html=section_tabs() ."<br><H5>{see_config}</H5><br>
	<table style='width:100%'>
	<tr>
	<td width=75% valign='top'>" . RoundedLightGreen("<code>$fetchmailrc</code>")  ."<br>" . RoundedLightGreen("<code>$FetchGetLive</code>")  . "</td>
	<td valign='top'>$save<br>" . applysettings("fetch") . "</td>
	</tr>
	</table>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



function section_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{fetchmail_rules}';
	$array[]='{see_config}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('fetchmail_daemon_rules','$page?Showlist=yes&section=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}  
	