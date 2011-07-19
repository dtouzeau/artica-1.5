<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.status.inc');
include_once('ressources/class.rtmm.tools.inc');
session_start();
if(isset($_GET["loadhelp"])){loadhelp();exit;}




if(!isset($_SESSION["uid"])){
	writelogs("uid=" . $_SESSION["uid"] . " come back to logon",__FUNCTION__,__FILE__);
	header('location:logon.php');
	exit;
	}




    if(isset($_GET["GeoipCity"])){GeoipCity();exit;}
	if(isset($_GET["PostfixStatus"])){echo Artica_infos();exit;}
	if(isset($_GET["today"])){echo GetToday();exit;}
	if(isset($_GET["lastinfos"])){echo GetLastInfos();;exit;}
	if(isset($_GET["allstatus"])){echo PAGE_REFRESH_All_Status();exit;}
	if(isset($_GET["StartPostfix"])){echo StartPostfix();exit;}
    if(isset($_GET["PostfixHistoryMsgID"])){echo PostfixHistoryMsgID();exit;}
    if(isset($_GET["postfix-status"])){echo postfix_status();exit;}

$users=new usersMenus();
writelogs("uid=" . $_SESSION["uid"],__FUNCTION__,__FILE__);
writelogs("AsOrgAdmin=$users->AsOrgAdmin",__FUNCTION__,__FILE__);
writelogs("AsArticaAdministrator=$users->AsArticaAdministrator",__FUNCTION__,__FILE__,__LINE__);
writelogs("AsOrgPostfixAdministrator=$users->AsOrgPostfixAdministrator",__FUNCTION__,__FILE__,__LINE__);
writelogs("AsMessagingOrg=$users->AsMessagingOrg",__FUNCTION__,__FILE__,__LINE__);


if($_SESSION["uid"]==-100){header('location:admin.index.php');exit;}
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){header('location:admin.index.php');exit;}


if(isset($_GET["admin-tabs"])){main_tabs();exit;}
if(isset($_GET["org"])){section_organization();exit;}
if(isset($_GET["messaging"])){section_messaging();exit;}


OVERVIEWUSER();

function loadhelp(){
	$tpl=new templates();
	$html="
	<div style='height:300px;padding:5px;border:1px dotted #CCCCCC;overflow:auto;font-size:13px'>{{$_GET["loadhelp"]}}</div>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function OVERVIEWUSER(){
$ou=$_SESSION["ou"];
$page=CurrentPageName();
$html="
<div id='user-front-end'>$frontend</div>
<script>LoadAjax('user-front-end','$page?admin-tabs=yes');</script>";
$tpl=new template_users("{your_organization}: $ou",$html,$_SESSION);
echo $tpl->web_page;		
}

function main_tabs(){
	
	if(!isset($_GET["main"])){$_GET["main"]="network";};
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	
	
	
	$ou=$_SESSION["ou"];
	
	if($users->AsMessagingOrg){
		$array["messaging"]='{emails_received}';
	}
	
	
	$array["org"]=$ou;

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_user_panel style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_user_panel').tabs({
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

function section_organization(){
	$ou=$_SESSION["ou"];
	
	echo "
	<div id='main-org-panel'></div>
	<script>
		Loadjs('domains.manage.org.index.php?js=yes&ou=$ou&panel=yes');
	</script>
	
	";
	
}
function section_messaging(){
	$ou=$_SESSION["ou"];
	$ldap=new clladp();
	$tpl=new templates();
	$domains=$ldap->Hash_domains_table($ou);
	while (list ($num, $ligne) = each ($domains) ){
		$doms[]="(LENGTH(delivery_user)>0 AND delivery_domain='$num')";
	}
	
	$sql="SELECT * FROM smtp_logs WHERE 1 AND ". implode(" OR ",$doms)." ORDER BY time_stamp DESC LIMIT 0,200";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
	echo "<H2> Failed: $sql</H2><H2>$q->mysql_error</H2>";
	return ;
	}
	
	$count=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$tr=$tr.format_line($ligne);
		}	
	
	$html="
	<table style='width:100%'>
	$tr
	</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
		
		
}






?>