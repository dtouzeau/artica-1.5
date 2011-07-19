<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.mailmanCTL.inc");
include_once(dirname(__FILE__)."/ressources/class.apache.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.pdns.inc");


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["add-list"])){add_list();exit;}
if(isset($_GET["listsave"])){list_save();exit;}
if(isset($_GET["mailman_lists_div"])){echo GetList();exit;}
if(isset($_GET["list-delete"])){del_list();exit;}
if(isset($_GET["pdns"])){pdns_popup();exit;}
if(isset($_GET["pdns_ip"])){pdns_save();exit;}
if(isset($_GET["list-info"])){list_info();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$prefix=str_replace('.',"_",$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_MAILMAN}");
	$title2=$tpl->_ENGINE_parse_body("{add_mailman}");
	$confirm_delete_mailman=$tpl->_ENGINE_parse_body("{confirm_delete_mailman}");
	$add_mailman_pdns=$tpl->_ENGINE_parse_body('{add_mailman_pdns}');
	$infos=$tpl->_ENGINE_parse_body('{infos}');
	
	$html="
	function {$prefix}Load(){
		YahooWin(500,'$page?popup=yes&ou={$_GET["ou"]}','$title');
	
	}
	
	function MailManPDNS(listname){
		YahooWin2(500,'$page?ou={$_GET["ou"]}&pdns='+listname,'$add_mailman_pdns');
	}
	
	var x_SaveMailManList= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);
				document.getElementById('mailmandiv').innerHTML='';
				return;

				}
				$('#dialog2').dialog('close');
				{$prefix}Load();
			}	
	
	function SaveMailManList(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('listsave','yes');
		XHR.appendData('listname',document.getElementById('listname').value);
		XHR.appendData('admin_email',document.getElementById('admin_email').value);
		XHR.appendData('admin_password',document.getElementById('admin_password').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('webservername',document.getElementById('webservername').value);
		XHR.appendData('webservername_domain',document.getElementById('wwwdomain').value);
		document.getElementById('mailmandiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveMailManList);	
	}
	
	function DeleteMailManList(list){
		if(confirm('$confirm_delete_mailman: '+list)){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('list-delete',list);
			XHR.sendAndLoad('$page', 'GET',x_SaveMailManList);	
		}
	}
	
	function InfoMailManList(list){
		YahooWin2(450,'$page?ou={$_GET["ou"]}&list-info='+list,'$infos:: '+list);
	}
	
	var x_SavePDNSMailman= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		$('#dialog2').dialog('close');
	}					
	
	function SavePDNSMailman(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('pdns_ip',document.getElementById('pdns_ip').value);
		XHR.appendData('www',document.getElementById('www').value);
		XHR.sendAndLoad('$page', 'GET',x_SavePDNSMailman);	
		}
	
	function AddMailManList(list){
		title=list;
		if(!list){list='';title='$title2'}
		YahooWin2(600,'$page?ou={$_GET["ou"]}&add-list='+list,title);
	
	}
	
	{$prefix}Load();
	
	";
	echo $html;
}

function popup(){
	$button=button("{add_mailman}","AddMailManList()");
	
	$list=GetList();
	$html="
	<div style='width:100%;text-align:right;padding:5px'>$button</div>
	<div style='width:99%;height:120px;overflow:auto' id='mailman_lists_div'>
	$list
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function add_list(){
	if($_SESSION["uid"]==-100){if(isset($_GET["ou"])){$ou=base64_decode($_GET["ou"]);}
	}else{$user=new user($_SESSION["uid"]);$ou=$user->ou;}	
	
	$users=new usersMenus();
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");	
	
	$pp="<p style='font-size:14px;color:black'>{add_mailman_text}</p>";
	$mailman=new mailman_control($ou,$_GET["add-list"]);
	
	
	
	if(preg_match("#(.+?)@(.+)#",$_GET["add-list"],$re)){
		$domain=$re[2];
		$listename=$re[1];
		$button_name="{edit}";
		$pp=null;
		$filed_list="<strong style='font-size:14px'>$listename</strong><input type='hidden' id='listname' value='$listename'>";
		$urlLink="<strong style='font-size:14px'>
		<li><a href='http://$mailman->webservername:$ApacheGroupWarePort' target=_new>{view_list_mailman}</a></li>
		<li><a href='http://$mailman->webservername:$ApacheGroupWarePort/mailman/admin/$listename' target=_new>{adminweb_mailman}</a></li>";
		if($users->POWER_DNS_INSTALLED){
			$add_pnds="<hr>".button("{add_mailman_pdns}","MailManPDNS('{$_GET["add-list"]}')");
		}
		
	}else{
		$button_name="{add}";
		$filed_list=Field_text('listname',null,"width:180px;font-size:14px;padding:3px");
	}
	
	while (list ($num, $val) = each ($domains) ){
		$domainsZ[$num]=$num;
	}
	
	$dom2=Field_array_Hash($domainsZ,'wwwdomain',$domain,null,null,0,"font-size:14px;padding:3px");
	
	if($domain==null){
		$dom=Field_array_Hash($domainsZ,'domain',$domain,null,null,0,"font-size:14px;padding:3px");
		
	}else{
		$dom="<input type='hidden' id='domain' value='$domain'><strong style='font-size:14px'>$domain</span>";
		
	}
	
	if($mailman->admin_email==null){
		$mailman->admin_email=$user->mail;
	}

	if($mailman->admin_password==null){
		$mailman->admin_password=$user->password;
	}	
	
	if(preg_match("#^(.+?)\.#",$mailman->webservername,$re)){
		$mailman->webservername=$re[1];
	}
	
	
	$html="
	$pp
	<div id='mailmandiv'></div>
	<table>
	<tr>
		<td class=legend nowrap width=1%>{listname}:</td>
		<td width=1% align='right'>$filed_list</td>
		<td width=1% style='font-size:14px'><strong>@</strong></td>
		<td width=99%>$dom</td>
	</tr>
	<tr>
		<td class=legend nowrap width=1%>{www_server_name}:</td>
		<td align='left' width=1%>". Field_text('webservername',$mailman->webservername,"width:180px;font-size:14px;padding:3px")."</td>
		<td width=1% style='font-size:14px'><strong>.</strong></td>
		<td>$dom2</td>
	</tr>	
	
	
	<tr>
		<td class=legend nowrap width=1%>{admin_mail}:</td>
		<td align='left' width=1% colspan=3>". Field_text('admin_email',$mailman->admin_email,"width:180px;font-size:14px;padding:3px")."</td>
		
		
	</tr>	
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td width=1% colspan=3>". Field_password('admin_password',$mailman->admin_password,"width:180px;font-size:14px;padding:3px")."</td>
		
	</tr>	
	<tr>
		<td colspan=4 align='right'><hr>
		". button("$button_name&nbsp;&raquo;","SaveMailManList()")."
		</td>
	</tr>
	<tr>
		<td colspan=4 align='right'>$urlLink</td>
	</tr>
	<tr>
		<td colspan=4 align='right'>$add_pnds</td>
	</tr>	
	
	
	</table>
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"mailman.lists.php");
	
	
	
	
}

function GetList(){
	if($_SESSION["uid"]==-100){
		if(isset($_GET["ou"])){
			$ou=base64_decode($_GET["ou"]);
		}
	}else{
		$ct=new user($_SESSION["uid"]);
		$ou=$ct->ou;
	}

	$ldap=new clladp();
	$user=new usersMenus();
	$filter="(&(Objectclass=ArticaMailManRobots)(cn=*))";
	$sr = @ldap_search($ldap->ldap_connection,"cn=mailman,ou=$ou,dc=organizations,$ldap->suffix",$filter,array("cn","mailManOwner"));
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	
	$html="<table class=table_form>";
	
	for($i=0;$i<$hash["count"];$i++){
		if($hash[$i][strtolower("mailManOwner")][0]==null){continue;}
		$js="AddMailManList('{$hash[$i]["cn"][0]}');";
		$html=$html."
		<tr>
			<td valign='middle' width=1% ". CellRollOver($js)."><img src='img/fw_bold.gif'></td>
			<td valign='top' ". CellRollOver($js)."><span style='font-size:16px'>{$hash[$i]["cn"][0]}</span></td>
			<td valign='top'>". imgtootltip("info-18.png","{infos}","InfoMailManList('{$hash[$i]["cn"][0]}')")."</td>
			<td valign='top'>". imgtootltip("ed_delete.gif","{delete}","DeleteMailManList('{$hash[$i]["cn"][0]}')")."</td>
		</tr>
		";
		
	}
	
	$html=$html."</table>";
	return $html;
	
}


function list_save(){
	$listname=$_GET["listname"];
	$admin_email=$_GET["admin_email"];
	$tpl=new templates();
	if($_SESSION["uid"]==-100){
		echo "ok\n";
		$ou_q=base64_decode($_GET["ou"]);
	}else{$ct=new user($_SESSION["uid"]);$ou_q=$ct->ou;}	
	
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($admin_email);
	if($uid==null){
		echo $tpl->_ENGINE_parse_body("$admin_email:{mailman_admin_not_exists}");
		exit;
	}
	
	$ct=new user($uid);
	$listuid=$ldap->uid_from_email("$listname@$domain");
	if($listuid<>null){
		echo $tpl->_ENGINE_parse_body("{account_already_exists}:$listname@$domain");
		exit;
	}
	
	if($_GET["webservername"]==null){
		echo $tpl->_ENGINE_parse_body("{www_server_name}:NULL !");
		exit;		
	}
	
	$apache=new vhosts();
	$array=$apache->SearchHosts($_GET["webservername"].'.'.$_GET["webservername_domain"]);
	if($array["apacheservername"]<>null){
		echo $tpl->_ENGINE_parse_body($_GET["webservername"].'.'.$_GET["webservername_domain"]." {error_domain_exists}");
		exit;	
	}
	
	
	$admin_password=$_GET["admin_password"];
	$domain=$_GET["domain"];
	$mailman=new mailman_control($ou_q);
	$mailman->list_name=$listname;
	$mailman->list_domain=$domain;
	$mailman->admin_email=$admin_email;
	$mailman->admin_password=$_GET["admin_password"];
	$mailman->webservername=$_GET["webservername"].'.'.$_GET["webservername_domain"];
	if($mailman->EditList()){
		
	}
	
$sock=new sockets();
		$sock->getFrameWork("cmd.php?syncro-mailman=yes");	
	}
	
	
function del_list(){
	if($_SESSION["uid"]==-100){
		if(isset($_GET["ou"])){
			$ou=base64_decode($_GET["ou"]);
		}
	}else{
		$ct=new user($_SESSION["uid"]);
		$ou=$ct->ou;
	}	
	
	

	if(!preg_match("#(.+?)@(.+)#",$_GET["list-delete"],$re)){echo "unable to preg_match {$_GET["list-delete"]}\n";return;}
	$mailman=new mailman_control($ou,$_GET["list-delete"]);
	$mailman->delete();
	
	
	
}

function pdns_popup(){
	if($_SESSION["uid"]==-100){
		if(isset($_GET["ou"])){
			$ou=base64_decode($_GET["ou"]);
		}
	}else{
		$ct=new user($_SESSION["uid"]);
		$ou=$ct->ou;
	}		
	$mailman=new mailman_control($ou,$_GET["pdns"]);
	$net=new networking();
	$array=$net->array_TCP;
	
	$pd=new pdns();
	$localip=$pd->GetIp($mailman->webservername);
	while (list ($eth, $ip) = each($array) ){
		if($ip==null){continue;}
		$ips[$ip]="$ip ($eth)";
	}
	
	$ipf=Field_array_Hash($ips,"pdns_ip",$localip);
	
	$html="
	<H2>$mailman->webservername&nbsp;&raquo;&raquo;{APP_PDNS}</H2>
	<div style='text-align:right;font-size:10px;font-weight:bold;color:black;border-top:1px solid #999999'><i>$localip</i></div>
	<p style='color:black'>{pdns_mailman_explain}</p>
	<input type='hidden' id='www' value='$mailman->webservername'>
	<table class=table_form>
	<tr>
		<td class=legend>{select_ip_address}:</td>
		<td>$ipf</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{edit}","SavePDNSMailman()")."</td>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function pdns_save(){
	if(!preg_match("#(.+?)\.(.+)$#",$_GET["www"],$re)){
		echo "Unable to find the domain name";
		exit;
	}
	$pdns=new pdns($re[2]);
	$pdns->EditIPName($re[1],$_GET["pdns_ip"],"A");
	echo "{$re[2]} [{$re[1]}] => {$_GET["pdns_ip"]}";
	
	
	
}

function list_info(){
		$array["admin"]="admin";
		$array["bounces"]="bounces";
		$array["confirm"]="confirm";
		$array["join"]="join";
		$array["leave"]="leave";
		$array["owner"]="owner";
		$array["request"]="request";
		$array["subscribe"]="subscribe";
		$array["unsubscribe"]="unsubscribe";

		if(preg_match("#(.+?)@(.+)#",$_GET["list-info"],$re)){
			$list_name=$re[1];
			$domain=$re[2];
		}
	
		$html="<table class=table_form>";
		
		while (list ($list, $list) = each($array) ){
			$html=$html.
			
			"<tr>
				<td valign='top'>
					<strong style='color:#CC0000'>$list_name-$list@$domain</strong>
				</td>
			</tr>
			<tr>
				<td valign='top'><p style='font-size:11px;font-weight:normal;margin:0px;padding:2px'>{mailman_$list}</P></td>
			</tr>
			
			
			
			";
		}
		
	$html="<div style='width:100%;height:300px;overflow:auto'>$html</div>";	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);			
		
		
}


?>