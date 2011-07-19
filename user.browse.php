<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');

	
	
	
	if(isset($_GET["popup"])){echo popup();exit;}
	if(isset($_GET["browser-users"])){local_users();exit;}
	
js();

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(isset($_GET["YahooWin"])){$YahooWin="YahooWin{$_GET["YahooWin"]}";}	
	$html="
	<center><strong style='font-size:13px;'>Search:&nbsp;</strong>". Field_text("local_user_search",null,"font-size:13px;padding:3px;width:220px",null,null,null,false,"SearchLocalUserEnter(event)")."</center>
	<div style='height:300px;width:100%;overflow:auto;margin:8px' id='browser-users'></div>
	
	
	
	<script>
		function RefreshLocalMember(){
			var search=escape(document.getElementById('local_user_search').value);
			LoadAjax('browser-users','$page?browser-users='+search+'&field={$_GET["field"]}&YahooWin={$_GET["YahooWin"]}');
		}
		
		function SearchLocalUserEnter(e){
			if(checkEnter(e)){RefreshLocalMember();}
		}
		
		RefreshLocalMember();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();		
	$YahooWin="YahooWin5";
	if(isset($_GET["YahooWin"])){$YahooWin="YahooWin{$_GET["YahooWin"]}";}
	$title=$tpl->_ENGINE_parse_body("{browse}"."...");
	$html="$YahooWin(500,'$page?popup=yes&field={$_GET["field"]}&YahooWin={$_GET["YahooWin"]}','$title')";
	echo $html;
	
}




function local_users(){
	$stringtofind=$_GET["browser-users"];
	$ldap=new clladp();
	$page=CurrentPageName();
	$tpl=new templates();		
	if($_SESSION["ou"]<>null){$ou=$_SESSION["ou"];}
	$hash=$ldap->UserSearch($ou,$stringtofind);
	$YahooWin="YahooWin5";
	if(isset($_GET["YahooWin"])){$YahooWin="YahooWin{$_GET["YahooWin"]}";}	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	for($i=0;$i<$hash[0]["count"];$i++){
		$ligne=$hash[0][$i];
		$dn=$ligne["dn"];
		if(strpos($ligne["dn"],"dc=pureftpd,dc=organizations")>0){continue;}
		$uid=$ligne["uid"][0];
		if($uid==null){continue;}
		if($uid=="squidinternalauth"){continue;}
		if($array[$uid]<>null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ct=new user($uid);
			$js=MEMBER_JS($uid,1,1);
			$img=imgtootltip("contact-48.png","{view}",$js);
			$add=imgtootltip("plus-24.png","{add}","SelectMemberBrowser('$uid')");
			$html=$html."
			<tr class=$classtr>
			<td width=1%>$img</td>
			<td><strong style='font-size:14px'>$ct->DisplayName</td>
			<td width=1%>$add</td>
			</tr>
			";		
		
	}
	$html=$html."</tbody></table>
	<script>	
		function SelectMemberBrowser(uid){
			document.getElementById('{$_GET["field"]}').value=uid;
			{$YahooWin}Hide();
		}
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}