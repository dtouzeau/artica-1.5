<?php
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.apache.inc");



	
	if(isset($_GET["tabs"])){tabs_admin();exit;}
	
	if(isset($_GET["WebFilterRepository"])){WebFilterRepository();exit;}
	if(isset($_GET["WebFilterRepository-tabs"])){WebFilterRepository_tabs();exit;}
	if(isset($_POST["CategoriesRepositoryEnable"])){WebFilterRepository_save();exit;}
	
	
	js();
	
function js(){
	$page=CurrentPageName();
	echo "LoadAjax('BodyContent','$page?tabs=yes');";
	
}


function tabs_admin(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	

	if($users->AsWebFilterRepository){
		$arr["WebFilterRepository-tabs"]="{APP_PROXY_CATS}";
	}
	//$arr["popup"]="{myWebServices}";
	
	
	while(list( $num, $ligne ) = each ($arr)){
		
		$toolbox[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>");
	}
	
	
	
	
	$html = "<div id='container-webfilter-tabs' style='width:99%;margin:0px;background-color:white'>
			<ul>
				" . implode ( "\n\t", $toolbox ) . "
			</ul>
		</div>
		<script>
		 $(document).ready(function() {
			$(\"#container-webfilter-tabs\").tabs();});
		</script>";
	echo $html;
	
}

function WebFilterRepository_tabs(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	

	if($users->AsWebFilterRepository){
		$arr["WebFilterRepository"]="{parameters}";
		$arr["WebFilterRepository-events"]="{events}";
	}
	//$arr["popup"]="{myWebServices}";
	
	
	while(list( $num, $ligne ) = each ($arr)){
		
		$toolbox[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>");
	}
	
	
	
	
	$html = "<div id='container-WebFilterRepository-tabs' style='width:99%;margin:0px;background-color:white'>
			<ul>
				" . implode ( "\n\t", $toolbox ) . "
			</ul>
		</div>
		<script>
		 $(document).ready(function() {
			$(\"#container-WebFilterRepository-tabs\").tabs();});
			
			LoadAjax('tool-map','miniadm.toolbox.php?script=". urlencode($page)."');
		</script>";
	echo $html;	
	
	
}

function WebFilterRepository(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	if(!$users->AsWebFilterRepository){return false;}
	$CategoriesRepositoryEnable=$sock->GET_INFO("CategoriesRepositoryEnable");
	if(!is_numeric($CategoriesRepositoryEnable)){$CategoriesRepositoryEnable=0;}
	$UpdateCategoriesRepositoryUrl=$sock->GET_INFO("UpdateCategoriesRepositoryUrl");
	if($UpdateCategoriesRepositoryUrl==null){$UpdateCategoriesRepositoryUrl="http://www.artica.fr/blacklist";}
	
	$html="<div class=explain>{APP_PROXY_CATS_TEXT}</div>
	<div id='WebFilterRepository-parms-div'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td valign='top' class=legend>{enable}:</td>
		<td>". Field_checkbox("CategoriesRepositoryEnable",1,$CategoriesRepositoryEnable,"CategoriesRepositoryEnableCheck()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{update_url}:</td>
		<td>". Field_text("UpdateCategoriesRepositoryUrl",$UpdateCategoriesRepositoryUrl,"font-size:14px;width:100%")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{apply}","APP_PROXY_CATS_SAVE()")."</td>
	</tr>
	</tbody>
	
	</table>
	
	<script>
		function CategoriesRepositoryEnableCheck(){
			document.getElementById('UpdateCategoriesRepositoryUrl').disabled=true;
			if(document.getElementById('CategoriesRepositoryEnable').checked){
				document.getElementById('UpdateCategoriesRepositoryUrl').disabled=false;
			}
		
		}
	var x_APP_PROXY_CATS_SAVE= function (obj) {
		var response=obj.responseText;
		RefreshTabs('container-WebFilterRepository-tabs');
	}	
	
	function APP_PROXY_CATS_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('UpdateCategoriesRepositoryUrl',document.getElementById('UpdateCategoriesRepositoryUrl').value);
		if(document.getElementById('CategoriesRepositoryEnable').checked){XHR.appendData('CategoriesRepositoryEnable',1);}else{XHR.appendData('CategoriesRepositoryEnable',0);}
		AnimateDiv('WebFilterRepository-parms-div');
		XHR.sendAndLoad('$page', 'POST',x_APP_PROXY_CATS_SAVE);		
	}	
	
		CategoriesRepositoryEnableCheck();
	</script>	
	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);
}


function WebFilterRepository_save(){
	$sock=new sockets();
	$sock->SET_INFO("CategoriesRepositoryEnable",$_POST["CategoriesRepositoryEnable"]);
	$sock->SET_INFO("UpdateCategoriesRepositoryUrl",$_POST["UpdateCategoriesRepositoryUrl"]);
	
}