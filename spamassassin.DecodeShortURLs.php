<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["list"])){popup_list();exit;}
	if(isset($_GET["list-list"])){popup_list_list();exit;}
	if(isset($_GET["DecodeShortURLsAdd"])){add();exit;}
	if(isset($_GET["DecodeShortURLsDel"])){del();exit;}
	if(isset($_GET["DecodeShortURLsEnable"])){enable();exit;}
	if(isset($_GET["EnableDecodeShortURLsInSpamAssassin"])){EnableDecodeShortURLsInSpamAssassinSave();exit;}
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{DecodeShortURLs}");
	$html="YahooWin4('650','$page?popup=yes','$title')";
	echo $html;
}

function index(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$EnableDecodeShortURLsInSpamAssassin=$sock->GET_INFO("EnableDecodeShortURLs");
	if($EnableDecodeShortURLsInSpamAssassin==null){$EnableDecodeShortURLsInSpamAssassin=1;}	
	$razor_but=Paragraphe_switch_img("{enable_DecodeShortURLs}","{DecodeShortURLs_explain}",
	"EnableDecodeShortURLsInSpamAssassin",$EnableDecodeShortURLsInSpamAssassin,"{enable_disable}",450);
	
	
	$html="
	<div id='EnableDecodeShortURLsInSpamAssassinDIV'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/spider-restrict-128.png'></td>
		<td valign='top'>
			$razor_but
			<hr>
			<div style='text-align:right'>". button("{apply}",'EnableDecodeShortURLsInSpamAssassinSave()')."</div>
		</div>
	</tr>
	</table>
	</div>
	<script>
var x_EnableDecodeShortURLsInSpamAssassinSave= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		RefreshTab('main_DecodeShortURLs_spamass');
	}		
function EnableDecodeShortURLsInSpamAssassinSave(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableDecodeShortURLsInSpamAssassin',document.getElementById('EnableDecodeShortURLsInSpamAssassin').value);
		document.getElementById('EnableDecodeShortURLsInSpamAssassinDIV').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_EnableDecodeShortURLsInSpamAssassinSave);
		}		
	

	</script>
	
	";
	
echo $tpl->_ENGINE_parse_body($html);	
	
}

function add(){
	$sql="INSERT INTO spamassassin_table (`spam_type`,`value`) VALUES ('DecodeShortURLs','{$_GET["DecodeShortURLsAdd"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");
	
}
function del(){
	if(!is_numeric($_GET["DecodeShortURLsDel"])){return;}
	$sql="DELETE FROM spamassassin_table WHERE ID='{$_GET["DecodeShortURLsDel"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");	
	
}

function enable(){
	if(!is_numeric($_GET["DecodeShortURLsEnable"])){return;}
	$sql="UPDATE spamassassin_table SET enabled='{$_GET["value"]}' WHERE ID={$_GET["DecodeShortURLsEnable"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");		
	
}

function popup_list(){
	$page=CurrentPageName();
	$tpl=new templates();	

	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{add}:</td>
		<td>". Field_text("DecodeShortURLsAdd",null,"font-size:13px;padding:3px",null,null,null,false,"DecodeShortURLsAddCheck(event)")."</td>
	</tr>
	</table>
	<hr>
	
	<div id='DecodeShortURLsList' style='height:430px;overflow:auto'></div>
	
	<script>
		function DecodeShortURLsListRefresh(){
			var pattern=escape(document.getElementById('DecodeShortURLsAdd').value);
			LoadAjax('DecodeShortURLsList','$page?list-list=yes&find='+pattern);
		}
		
	var x_DecodeShortURLsAddCheck= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		DecodeShortURLsListRefresh();
	}			
		
	var x_DecodeShortURLsEnable= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		
	}			
		
	function DecodeShortURLsAddCheck(e){
		if(!checkEnter(e)){DecodeShortURLsListRefresh();return;}
		var XHR = new XHRConnection();
		XHR.appendData('DecodeShortURLsAdd',document.getElementById('DecodeShortURLsAdd').value);
		document.getElementById('DecodeShortURLsList').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DecodeShortURLsAddCheck);
		}	

	function DecodeShortURLsDel(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DecodeShortURLsDel',ID);
		document.getElementById('DecodeShortURLsList').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DecodeShortURLsAddCheck);
	}

	function DecodeShortURLsEnable(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DecodeShortURLsEnable',ID);
		if(document.getElementById('enable_'+ID).checked){
			XHR.appendData('value',1);
		}else{
			XHR.appendData('value',0);
		}
		
		XHR.sendAndLoad('$page', 'GET',x_DecodeShortURLsEnable);
	}		
		
	
	
		
	DecodeShortURLsListRefresh();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_list_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	if(trim($_GET["find"])<>null){
		$WHEREF="AND `value` LIKE '%{$_GET["find"]}%' ";
	}
	
	$sql="SELECT * FROM  spamassassin_table WHERE spam_type='DecodeShortURLs' $WHEREF ORDER BY `value` LIMIT 0,50";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>{websites}</th>
	</tr>
</thead>";



if(!$q->ok){echo "<H3>$q->mysql_error</H3>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:14px'>{$ligne["value"]}</td>
		<td width=1%>". Field_checkbox("enable_{$ligne["ID"]}",1,$ligne["enabled"],"DecodeShortURLsEnable({$ligne["ID"]})")."</td>
		<td width=1%>". imgtootltip("delete-24.png","{delete}","DecodeShortURLsDel({$ligne["ID"]})")."</td>
		</tr>
		";
		
		
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}


function popup(){
	
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["list"]='{websites}';
	


	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_DecodeShortURLs_spamass style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_DecodeShortURLs_spamass').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
		
			});
		</script>";			
	
}

function EnableDecodeShortURLsInSpamAssassinSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableDecodeShortURLs",$_GET["EnableDecodeShortURLsInSpamAssassin"]);
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");
}



?>