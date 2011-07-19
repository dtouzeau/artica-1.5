<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	
	if(isset($_GET["whitelist"])){whitelist();exit;}
	if(isset($_GET["whitelist-list"])){whitelist_list();exit;}
	if(isset($_GET["whitelist-add"])){whitelist_add();exit;}
	if(isset($_GET["whitelist-del"])){whitelist_del();exit;}
	if(isset($_GET["SPF_PASS_1"])){SAVE_SCORES();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body("{APP_SPF}");
	
	
	$html="
	
	function spamass_spf_load(){
			YahooWin2('650','$page?popup=yes','$title');
		}
	
	spamass_spf_load();";
	
	echo $html;
	
	
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["settings"]='{global_settings}';
	$array["whitelist"]='{whitelist_text}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&section=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_spamass_spf style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_spamass_spf').tabs({
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

function settings(){
	
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableSPF=$sock->GET_INFO("EnableSPF");
	if($EnableSPF==null){$EnableSPF=1;}
	$Config=unserialize(base64_decode($sock->GET_INFO("SpamAssassinSPFConfig")));
	$global_enable=Paragraphe_switch_img("{ACTIVATE_SPF}","{ACTIVATE_SPF_TEXT}<br>{APP_SPF_TEXT}","EnableSPF",$EnableSPF,null,550);
	
	if(!is_array($Config)){$Config=array();}
	
	if($Config["SPF_PASS_1"]==null){$Config["SPF_PASS_1"]="-0.001";}
	if($Config["SPF_PASS_2"]==null){$Config["SPF_PASS_2"]="-";}
	if($Config["SPF_PASS_3"]==null){$Config["SPF_PASS_3"]="-";}
	if($Config["SPF_PASS_4"]==null){$Config["SPF_PASS_4"]="-";}
	
	if($Config["SPF_HELO_PASS_1"]==null){$Config["SPF_HELO_PASS_1"]="-0.001";}
	if($Config["SPF_HELO_PASS_2"]==null){$Config["SPF_HELO_PASS_2"]="-";}
	if($Config["SPF_HELO_PASS_3"]==null){$Config["SPF_HELO_PASS_3"]="-";}
	if($Config["SPF_HELO_PASS_4"]==null){$Config["SPF_HELO_PASS_4"]="-";}	
	
	
	if($Config["SPF_FAIL_1"]==null){$Config["SPF_FAIL_1"]="0";}
	if($Config["SPF_FAIL_2"]==null){$Config["SPF_FAIL_2"]="1.333";}
	if($Config["SPF_FAIL_3"]==null){$Config["SPF_FAIL_3"]="0";}
	if($Config["SPF_FAIL_4"]==null){$Config["SPF_FAIL_4"]="1.142";}	
	
	
	if($Config["SPF_HELO_FAIL_1"]==null){$Config["SPF_HELO_FAIL_1"]="0";}
	if($Config["SPF_HELO_FAIL_2"]==null){$Config["SPF_HELO_FAIL_2"]="-";}
	if($Config["SPF_HELO_FAIL_3"]==null){$Config["SPF_HELO_FAIL_3"]="-";}
	if($Config["SPF_HELO_FAIL_4"]==null){$Config["SPF_HELO_FAIL_4"]="-";}		
	
	if($Config["SPF_HELO_NEUTRAL_1"]==null){$Config["SPF_HELO_NEUTRAL_1"]="0";}
	if($Config["SPF_HELO_NEUTRAL_2"]==null){$Config["SPF_HELO_NEUTRAL_2"]="-";}
	if($Config["SPF_HELO_NEUTRAL_3"]==null){$Config["SPF_HELO_NEUTRAL_3"]="-";}
	if($Config["SPF_HELO_NEUTRAL_4"]==null){$Config["SPF_HELO_NEUTRAL_4"]="-";}		
	
	
	if($Config["SPF_NEUTRAL_1"]==null){$Config["SPF_NEUTRAL_1"]="0";}
	if($Config["SPF_NEUTRAL_2"]==null){$Config["SPF_NEUTRAL_2"]="1.379";}
	if($Config["SPF_NEUTRAL_3"]==null){$Config["SPF_NEUTRAL_3"]="0";}
	if($Config["SPF_NEUTRAL_4"]==null){$Config["SPF_NEUTRAL_4"]="1.069";}		
	
	
	if($Config["SPF_SOFTFAIL_1"]==null){$Config["SPF_SOFTFAIL_1"]="0";}
	if($Config["SPF_SOFTFAIL_2"]==null){$Config["SPF_SOFTFAIL_2"]="1.470";}
	if($Config["SPF_SOFTFAIL_3"]==null){$Config["SPF_SOFTFAIL_3"]="0";}
	if($Config["SPF_SOFTFAIL_4"]==null){$Config["SPF_SOFTFAIL_4"]="1.384";}	
	
	if($Config["SPF_HELO_SOFTFAIL_1"]==null){$Config["SPF_HELO_SOFTFAIL_1"]="0";}
	if($Config["SPF_HELO_SOFTFAIL_2"]==null){$Config["SPF_HELO_SOFTFAIL_2"]="2.078";}
	if($Config["SPF_HELO_SOFTFAIL_3"]==null){$Config["SPF_HELO_SOFTFAIL_3"]="0";}
	if($Config["SPF_HELO_SOFTFAIL_4"]==null){$Config["SPF_HELO_SOFTFAIL_4"]="2.432";}	
	
	

	
	
	$html="
	<div id='spamass_spf_config'>
	
	
	<div>$global_enable</div>
	<HR>
	<div class=explain>{SPAMASS_SCORES_EXPLAIN}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{SPF_PASS}:</td>
		<td>". Field_text("SPF_PASS_1",$Config["SPF_PASS_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_PASS_2",$Config["SPF_PASS_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_PASS_3",$Config["SPF_PASS_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_PASS_4",$Config["SPF_PASS_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("SPF_PASS_HELP")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{SPF_HELO_PASS}:</td>
		<td>". Field_text("SPF_HELO_PASS_1",$Config["SPF_HELO_PASS_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_PASS_2",$Config["SPF_HELO_PASS_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_PASS_3",$Config["SPF_HELO_PASS_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_PASS_4",$Config["SPF_HELO_PASS_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("SPF_PASS_HELP")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{SPF_FAIL}:</td>
		<td>". Field_text("SPF_FAIL_1",$Config["SPF_FAIL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_FAIL_2",$Config["SPF_FAIL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_FAIL_3",$Config["SPF_FAIL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_FAIL_4",$Config["SPF_FAIL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_FAIL_HELP}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{SPF_HELO_FAIL}:</td>
		<td>". Field_text("SPF_HELO_FAIL_1",$Config["SPF_HELO_FAIL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_FAIL_2",$Config["SPF_HELO_FAIL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_FAIL_3",$Config["SPF_HELO_FAIL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_FAIL_4",$Config["SPF_HELO_FAIL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_FAIL_HELP}")."</td>
	</tr>
	
	<tr>
		<td class=legend style='font-size:13px'>{SPF_NEUTRAL}:</td>
		<td>". Field_text("SPF_NEUTRAL_1",$Config["SPF_NEUTRAL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_NEUTRAL_2",$Config["SPF_NEUTRAL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_NEUTRAL_3",$Config["SPF_NEUTRAL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_NEUTRAL_4",$Config["SPF_NEUTRAL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_NEUTRAL_HELP}")."</td>
	</tr>		

	<tr>
		<td class=legend style='font-size:13px'>{SPF_HELO_NEUTRAL}:</td>
		<td>". Field_text("SPF_HELO_NEUTRAL_1",$Config["SPF_HELO_NEUTRAL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_NEUTRAL_2",$Config["SPF_HELO_NEUTRAL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_NEUTRAL_3",$Config["SPF_HELO_NEUTRAL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_NEUTRAL_4",$Config["SPF_HELO_NEUTRAL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_NEUTRAL_HELP}")."</td>
	</tr>	
	
	
	<tr>
		<td class=legend style='font-size:13px'>{SPF_SOFTFAIL}:</td>
		<td>". Field_text("SPF_SOFTFAIL_1",$Config["SPF_SOFTFAIL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_SOFTFAIL_2",$Config["SPF_SOFTFAIL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_SOFTFAIL_3",$Config["SPF_SOFTFAIL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_SOFTFAIL_4",$Config["SPF_SOFTFAIL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_SOFTFAIL_HELP}")."</td>
	</tr>	
	
	<tr>
		<td class=legend style='font-size:13px'>{SPF_HELO_SOFTFAIL}:</td>
		<td>". Field_text("SPF_HELO_SOFTFAIL_1",$Config["SPF_HELO_SOFTFAIL_1"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_SOFTFAIL_2",$Config["SPF_HELO_SOFTFAIL_2"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_SOFTFAIL_3",$Config["SPF_HELO_SOFTFAIL_3"],'width:40px;font-size:13px')."</td>
		<td>". Field_text("SPF_HELO_SOFTFAIL_4",$Config["SPF_HELO_SOFTFAIL_4"],'width:40px;font-size:13px')."</td>
		<td>". help_icon("{SPF_SOFTFAIL_HELP}")."</td>
	</tr>	
	
	<tr>
		<td colspan=6 align='right'><hr>". button("{apply}","saveSpamAssassinSPF()")."</td>
	</tr>
	
	</table>
	
	</div>
	
	<script>
	
	var x_saveSpamAssassinSPF= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_spamass_spf');
		}		
	
	function saveSpamAssassinSPF(){

	var XHR = new XHRConnection();
		XHR.appendData('SPF_PASS_1',document.getElementById('SPF_PASS_1').value);
		XHR.appendData('SPF_PASS_2',document.getElementById('SPF_PASS_2').value);
		XHR.appendData('SPF_PASS_3',document.getElementById('SPF_PASS_3').value);
		XHR.appendData('SPF_PASS_4',document.getElementById('SPF_PASS_4').value);

		XHR.appendData('SPF_HELO_PASS_1',document.getElementById('SPF_HELO_PASS_1').value);
		XHR.appendData('SPF_HELO_PASS_2',document.getElementById('SPF_HELO_PASS_2').value);
		XHR.appendData('SPF_HELO_PASS_3',document.getElementById('SPF_HELO_PASS_3').value);
		XHR.appendData('SPF_HELO_PASS_4',document.getElementById('SPF_HELO_PASS_4').value);	

		XHR.appendData('SPF_FAIL_1',document.getElementById('SPF_FAIL_1').value);
		XHR.appendData('SPF_FAIL_2',document.getElementById('SPF_FAIL_2').value);
		XHR.appendData('SPF_FAIL_3',document.getElementById('SPF_FAIL_3').value);
		XHR.appendData('SPF_FAIL_4',document.getElementById('SPF_FAIL_4').value);

		XHR.appendData('SPF_HELO_FAIL_1',document.getElementById('SPF_HELO_FAIL_1').value);
		XHR.appendData('SPF_HELO_FAIL_2',document.getElementById('SPF_HELO_FAIL_2').value);
		XHR.appendData('SPF_HELO_FAIL_3',document.getElementById('SPF_HELO_FAIL_3').value);
		XHR.appendData('SPF_HELO_FAIL_4',document.getElementById('SPF_HELO_FAIL_4').value);	

		XHR.appendData('SPF_HELO_NEUTRAL_1',document.getElementById('SPF_HELO_NEUTRAL_1').value);
		XHR.appendData('SPF_HELO_NEUTRAL_2',document.getElementById('SPF_HELO_NEUTRAL_2').value);
		XHR.appendData('SPF_HELO_NEUTRAL_3',document.getElementById('SPF_HELO_NEUTRAL_3').value);
		XHR.appendData('SPF_HELO_NEUTRAL_4',document.getElementById('SPF_HELO_NEUTRAL_4').value);			
		
		XHR.appendData('SPF_NEUTRAL_1',document.getElementById('SPF_NEUTRAL_1').value);
		XHR.appendData('SPF_NEUTRAL_2',document.getElementById('SPF_NEUTRAL_2').value);
		XHR.appendData('SPF_NEUTRAL_3',document.getElementById('SPF_NEUTRAL_3').value);
		XHR.appendData('SPF_NEUTRAL_4',document.getElementById('SPF_NEUTRAL_4').value);			
		
		XHR.appendData('SPF_SOFTFAIL_1',document.getElementById('SPF_SOFTFAIL_1').value);
		XHR.appendData('SPF_SOFTFAIL_2',document.getElementById('SPF_SOFTFAIL_2').value);
		XHR.appendData('SPF_SOFTFAIL_3',document.getElementById('SPF_SOFTFAIL_3').value);
		XHR.appendData('SPF_SOFTFAIL_4',document.getElementById('SPF_SOFTFAIL_4').value);		
		
		XHR.appendData('SPF_HELO_SOFTFAIL_1',document.getElementById('SPF_HELO_SOFTFAIL_1').value);
		XHR.appendData('SPF_HELO_SOFTFAIL_2',document.getElementById('SPF_HELO_SOFTFAIL_2').value);
		XHR.appendData('SPF_HELO_SOFTFAIL_3',document.getElementById('SPF_HELO_SOFTFAIL_3').value);
		XHR.appendData('SPF_HELO_SOFTFAIL_4',document.getElementById('SPF_HELO_SOFTFAIL_4').value);	
		document.getElementById('spamass_spf_config').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_saveSpamAssassinSPF);	
		
	}
	</script>
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function SAVE_SCORES(){
	$sock=new sockets();
	
	$sock->SET_INFO("EnableSPF",$_GET["EnableSPF"]);
	$sock->SET_INFO("SpamAssassinSPFConfig",base64_encode(serialize($_GET)));
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
	
}

function whitelist(){
	$tpl=new templates();
	$page=CurrentPageName();
	$add_text=$tpl->javascript_parse_text('{SPF_SPAMMASS_ADD_WL_TEXT}');
	
	$html="
	<div style='text-align:right;padding:5px;margin:5px'>". button("{add}","SPF_SPAMMASS_ADD_WL()")."</div>
	<div id='whitelistspfspamass' style='height:450px;overflow:auto'></div>
	
	<script>
	
	var x_SPF_SPAMMASS_ADD_WL= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		SPF_SPAMMASS_LIST();
		}

	function SPF_SPAMMASS_LIST(){
		LoadAjax('whitelistspfspamass','$page?whitelist-list=yes');
	}
	
	
	function SPF_SPAMMASS_ADD_WL(){
		var email=prompt('$add_text');
		if(email){
			var XHR = new XHRConnection();
			document.getElementById('whitelistspfspamass').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('whitelist-add',email);
			XHR.sendAndLoad('$page', 'GET',x_SPF_SPAMMASS_ADD_WL);
			}
		}
		
	function SPF_SPAMMASS_DELETE_WL(ID){
			var XHR = new XHRConnection();
			document.getElementById('whitelistspfspamass').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('whitelist-del',ID);
			XHR.sendAndLoad('$page', 'GET',x_SPF_SPAMMASS_ADD_WL);
	}
	
	SPF_SPAMMASS_LIST();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

function whitelist_list(){
	$q=new mysql();
	$tpl=new templates();
	$sql="SELECT * FROM spamassassin_spf_wl ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");

	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	
	<th colspan=3>&nbsp;</th>
	</tr>
</thead>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>";
		$html=$html."<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:16px'>{$ligne["domain"]}</td>
		<td width=1%>". imgtootltip("delete-32.png","{delete}","SPF_SPAMMASS_DELETE_WL({$ligne["ID"]})")."</td>
		</tr>
		";
		
		
		
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function whitelist_del(){
	$sql="DELETE FROM spamassassin_spf_wl WHERE ID='{$_GET["whitelist-del"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
}

function whitelist_add(){
	
	$sql="INSERT INTO spamassassin_spf_wl(domain) VALUES('{$_GET["whitelist-add"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
	
	
}


?>