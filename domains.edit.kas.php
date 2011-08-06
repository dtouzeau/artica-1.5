<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	include_once('ressources/class.kas-filter.inc');
	
	
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["popup"])){main_tabs();exit;}
	if(isset($_GET["ACTION_SPAM_MODE"])){SAVE();exit;}
	if(isset($_GET["OPT_FILTRATION_ON"])){SAVE();exit;}
	if(isset($_GET["OPT_SPAM_RATE_LIMIT"])){SAVE();exit;}
	if(isset($_GET["apply-config"])){APPLY();exit;}
	
	
js();

function js(){
		
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAS3}:: => '.$ou,"user.kas.php");
	
	
	$html="
		function OU_KASMILTER(){
			YahooWin4('650','$page?popup=yes&ou=$ou','$title');
		
		}
		
var x_ACTION_SPAM_MODE_SAVE= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	RefreshTab('main_rule_kas3');
}
	
	function ACTION_SPAM_MODE_SAVE(){
			var XHR = new XHRConnection();
			XHR.appendData('ACTION_SPAM_MODE',document.getElementById('ACTION_SPAM_MODE').value);
			XHR.appendData('ACTION_SPAM_EMAIL',document.getElementById('ACTION_SPAM_EMAIL').value);
			XHR.appendData('ACTION_SPAM_SUBJECT_PREFIX',document.getElementById('ACTION_SPAM_SUBJECT_PREFIX').value);
			XHR.appendData('ACTION_SPAM_USERINFO',document.getElementById('ACTION_SPAM_USERINFO').value);
			
			XHR.appendData('ACTION_PROBABLE_MODE',document.getElementById('ACTION_PROBABLE_MODE').value);
			XHR.appendData('ACTION_PROBABLE_EMAIL',document.getElementById('ACTION_PROBABLE_EMAIL').value);
			XHR.appendData('ACTION_PROBABLE_SUBJECT_PREFIX',document.getElementById('ACTION_PROBABLE_SUBJECT_PREFIX').value);
			XHR.appendData('ACTION_PROBABLE_USERINFO',document.getElementById('ACTION_PROBABLE_USERINFO').value);
			
			XHR.appendData('ACTION_BLACKLISTED_MODE',document.getElementById('ACTION_BLACKLISTED_MODE').value);
			XHR.appendData('ACTION_BLACKLISTED_EMAIL',document.getElementById('ACTION_BLACKLISTED_EMAIL').value);
			XHR.appendData('ACTION_BLACKLISTED_SUBJECT_PREFIX',document.getElementById('ACTION_BLACKLISTED_SUBJECT_PREFIX').value);
			XHR.appendData('ACTION_BLACKLISTED_USERINFO',document.getElementById('ACTION_BLACKLISTED_USERINFO').value);			
			
			XHR.appendData('ACTION_FORMAL_EMAIL',document.getElementById('ACTION_FORMAL_EMAIL').value);
			XHR.appendData('ACTION_FORMAL_SUBJECT_PREFIX',document.getElementById('ACTION_FORMAL_SUBJECT_PREFIX').value);
			XHR.appendData('ACTION_FORMAL_USERINFO',document.getElementById('ACTION_FORMAL_USERINFO').value);
			XHR.appendData('ACTION_FORMAL_MODE',document.getElementById('ACTION_FORMAL_MODE').value);				  
			
			   
			
			XHR.appendData('ou','$ou');
			document.getElementById('ACTION_SPAM_MODE_ID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ACTION_SPAM_MODE_SAVE);
		
	}
	
	   
	


	
	
	function OURBLDEL(ID){
		var XHR = new XHRConnection();
		XHR.appendData('OURBLDEL',ID);
		XHR.appendData('ou','$ou');
		XHR.sendAndLoad('$page', 'GET',x_OURBLADD);
	
	}		
	
	OU_KASMILTER();";
	
	echo $html;
	
}

function main_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["actions"]='{actions}';
	$array["rules"]='{rules}';
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num&tab=$num&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	

	echo "
	<div id=main_rule_kas3 style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_rule_kas3').tabs({
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

function main_switch(){
switch ($_GET["main"]) {
		case "index":main_index();exit;
		case "actions":main_actions();exit;
		case "rules":main_rule();break;
	    
	  
	}
	
}


function main_actions(){
	$tpl=new templates();
	$kas=new kas_mysql($_GET["ou"]);
	$page=CurrentPageName();
	
	$action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{quarantine}",-1=>"{kasreject}",-3=>"{kasdelete}");
	$ACTION_SPAM_MODE=Field_array_Hash($action_message,'ACTION_SPAM_MODE',$kas->GET_KEY("ACTION_SPAM_MODE"),"");	
	$ACTION_PROBABLE_MODE=Field_array_Hash($action_message,'ACTION_PROBABLE_MODE',$kas->GET_KEY("ACTION_PROBABLE_MODE"));
	$ACTION_BLACKLISTED_MODE=Field_array_Hash($action_message,'ACTION_BLACKLISTED_MODE',$kas->GET_KEY("ACTION_BLACKLISTED_MODE"));	
	$ACTION_FORMAL_MODE=Field_array_Hash($action_message,'ACTION_FORMAL_MODE',$kas->GET_KEY("ACTION_FORMAL_MODE"));
	
	
	
	$html="
		<div id='ACTION_SPAM_MODE_ID'>
		". Field_hidden("ACTION_SPAM_EMAIL",$kas->GET_KEY("ACTION_SPAM_EMAIL"))."		
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%'><span style='font-size:12px;font-weight:bold;color:#005447'>{spam option 1}</span></td>
				<td>$ACTION_SPAM_MODE</td>
			</tr>
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_SPAM_SUBJECT_PREFIX',$kas->GET_KEY("ACTION_SPAM_SUBJECT_PREFIX")) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_SPAM_USERINFO',$kas->GET_KEY("ACTION_SPAM_USERINFO")) . "</td>
			</tr>	
			<tr>
			<td colspan=2 align='right' style='padding-left:15px'>
			<hr>
				". button("{edit}","ACTION_SPAM_MODE_SAVE()")."</td>
			</tr>
		</table>
		";
				
				

	
	
	
$html=$html."<br>
". Field_hidden("ACTION_PROBABLE_EMAIL",$kas->GET_KEY("ACTION_PROBABLE_EMAIL"))."		
		<table style='width:90%'>
		<tr >
				<td align='right' nowrap style='width:1%'><span style='font-size:12px;font-weight:bold;color:#005447'>{spam option 2}</span></td>
				<td>$ACTION_PROBABLE_MODE</td>
			</tr>
					
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_PROBABLE_SUBJECT_PREFIX',$kas->GET_KEY("ACTION_PROBABLE_SUBJECT_PREFIX")) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_PROBABLE_USERINFO',$kas->GET_KEY("ACTION_PROBABLE_USERINFO")) . "</td>
			</tr>	
			<tr>
						<td colspan=2 align='right' style='padding-left:15px'>
			<hr>
				". button("{edit}","ACTION_SPAM_MODE_SAVE()")."</td>
			</tr>
		</table>
		";	
			

$html=$html."<br>
". Field_hidden("ACTION_BLACKLISTED_EMAIL",$kas->GET_KEY("ACTION_BLACKLISTED_EMAIL"))."	
<table style='width:90%'>
		<tr >
				<td align='right' nowrap style='width:1%'><span style='font-size:12px;font-weight:bold;color:#005447'>{spam option 3}</span></td>
				<td>$ACTION_BLACKLISTED_MODE</td>
			</tr>
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_BLACKLISTED_SUBJECT_PREFIX',$kas->GET_KEY("ACTION_BLACKLISTED_SUBJECT_PREFIX")) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_BLACKLISTED_USERINFO',$kas->GET_KEY("ACTION_BLACKLISTED_USERINFO")) . "</td>
			</tr>	
			<tr>	
						<td colspan=2 align='right' style='padding-left:15px'>
			<hr>
				". button("{edit}","ACTION_SPAM_MODE_SAVE()")."</td>
			</tr>
		</table>
		";	

$html=$html."<br>
". Field_hidden("ACTION_FORMAL_EMAIL",$kas->GET_KEY("ACTION_FORMAL_EMAIL"))."	
		<table style='width:90%'>
		<tr > 
				<td align='right' nowrap style='width:1%'><span style='font-size:12px;font-weight:bold;color:#005447'>{spam option 4}</span></td>
				<td>$ACTION_FORMAL_MODE</td>
			</tr>
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_FORMAL_SUBJECT_PREFIX',$kas->GET_KEY("ACTION_FORMAL_SUBJECT_PREFIX")) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_FORMAL_USERINFO',$kas->GET_KEY("ACTION_FORMAL_USERINFO")) . "</td>
			</tr>	
			<tr>	
						<td colspan=2 align='right' style='padding-left:15px'>
			<hr>
				". button("{edit}","ACTION_SPAM_MODE_SAVE()")."</td>
			</tr>
		</table>
		</div>	";				
	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SAVE(){
	$ou=$_GET["ou"];
	unset($_GET["ou"]);
	$kas=new kas_mysql($ou);
		
	while (list ($num, $ligne) = each ($_GET) ){
		$kas->SET_VALUE($num,$ligne);
	}
	
	
}

function main_rule(){
	$page=CurrentPageName();
	$kas=new kas_mysql($_GET["ou"]);
$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->GET_KEY("OPT_SPAM_RATE_LIMIT"),null,null,0,"font-size:14px");
$page=CurrentPageName();	


$html="	$tab
<table style='width:100%'>
<tr>
	<td align='right' nowrap class=legend>{OPT_SPAM_RATE_LIMIT}:</strong></td>
	<td>$OPT_SPAM_RATE_LIMIT</td>
	<td>". help_icon("{OPT_SPAM_RATE_LIMIT_TEXT}")."</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_USE_DNS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_USE_DNS',$kas->GET_KEY("OPT_USE_DNS"),'{enable_disable}') . "</td>
	<td>". help_icon("{OPT_USE_DNS_TEXT}")."</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_DNS_HOST_IN_DNS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_DNS_HOST_IN_DNS',$kas->GET_KEY("OPT_DNS_HOST_IN_DNS"),'{enable_disable}') . "</td>
	<td>{OPT_DNS_HOST_IN_DNS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_SPF}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_SPF',$kas->GET_KEY("OPT_SPF"),'{enable_disable}') . "</td>
	<td>{OPT_SPF_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_TO_UNDISCLOSED}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_TO_UNDISCLOSED',$kas->GET_KEY("OPT_HEADERS_TO_UNDISCLOSED"),'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_TO_UNDISCLOSED_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{HEADERS_FROM_OR_TO_DIGITS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_FROM_OR_TO_DIGITS',$kas->GET_KEY("OPT_HEADERS_FROM_OR_TO_DIGITS"),'{enable_disable}') . "</td>
	<td>{HEADERS_FROM_OR_TO_DIGITS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{HEADERS_FROM_OR_TO_NO_DOMAIN}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_FROM_OR_TO_NO_DOMAIN',$kas->GET_KEY("OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"),'{enable_disable}') . "</td>
	<td>{HEADERS_FROM_OR_TO_NO_DOMAIN_TEXT}</td>
</tr>


<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_TOO_LONG}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_TOO_LONG',$kas->GET_KEY("OPT_HEADERS_SUBJECT_TOO_LONG"),'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_TOO_LONG_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_WS_OR_DOTS}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_WS_OR_DOTS',$kas->GET_KEY("OPT_HEADERS_SUBJECT_WS_OR_DOTS"),'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_WS_OR_DOTS_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID',$kas->GET_KEY("OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"),'{enable_disable}') . "</td>
	<td>{OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID_TEXT}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_KOREAN}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_KOREAN',$kas->GET_KEY("OPT_LANG_KOREAN"),'{enable_disable_kas}') . "</td>
	<td>{eastern_encodings}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_CHINESE}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_CHINESE',$kas->GET_KEY("OPT_LANG_CHINESE"),'{enable_disable_kas}') . "</td>
	<td>{eastern_encodings}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_JAPANESE}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_JAPANESE',$kas->GET_KEY("OPT_LANG_JAPANESE"),'{enable_disable_kas}') . "</td>
	<td>{eastern_encodings}</td>
</tr>
<tr>
	<td align='right' nowrap class=legend>{OPT_LANG_THAI}:</strong></td>
	<td>" . Field_numeric_checkbox_img('OPT_LANG_THAI',$kas->GET_KEY("OPT_LANG_THAI"),'{enable_disable_kas}') . "</td>
	<td>{eastern_encodings}</td>
</tr>



<tr><td colspan=3 align=right>
			<hr>
				". button("{edit}","OPT_SPAM_RATE_LIMIT()"). "
		</td></tr>
</table>


	<script>
		var x_OPT_SPAM_RATE_LIMIT= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshTab('main_rule_kas3');
		}	
			function OPT_SPAM_RATE_LIMIT(){
					var XHR = new XHRConnection();
					XHR.appendData('ou','{$_GET["ou"]}');
					XHR.appendData('OPT_SPAM_RATE_LIMIT',document.getElementById('OPT_SPAM_RATE_LIMIT').value);
					XHR.appendData('OPT_USE_DNS',document.getElementById('OPT_USE_DNS').value);
					XHR.appendData('OPT_HEADERS_TO_UNDISCLOSED',document.getElementById('OPT_HEADERS_TO_UNDISCLOSED').value);

					XHR.appendData('OPT_HEADERS_FROM_OR_TO_DIGITS',document.getElementById('OPT_HEADERS_FROM_OR_TO_DIGITS').value);
					XHR.appendData('OPT_HEADERS_FROM_OR_TO_NO_DOMAIN',document.getElementById('OPT_HEADERS_FROM_OR_TO_NO_DOMAIN').value);					
					XHR.appendData('OPT_HEADERS_SUBJECT_TOO_LONG',document.getElementById('OPT_HEADERS_SUBJECT_TOO_LONG').value);
					XHR.appendData('OPT_HEADERS_SUBJECT_WS_OR_DOTS',document.getElementById('OPT_HEADERS_SUBJECT_WS_OR_DOTS').value);
					XHR.appendData('OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID',document.getElementById('OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID').value);
			
					XHR.appendData('OPT_DNS_HOST_IN_DNS',document.getElementById('OPT_DNS_HOST_IN_DNS').value);
					XHR.appendData('OPT_SPF',document.getElementById('OPT_SPF').value);
					
					XHR.appendData('OPT_LANG_KOREAN',document.getElementById('OPT_LANG_KOREAN').value);
					XHR.appendData('OPT_LANG_CHINESE',document.getElementById('OPT_LANG_CHINESE').value);
					XHR.appendData('OPT_LANG_JAPANESE',document.getElementById('OPT_LANG_JAPANESE').value);
					XHR.appendData('OPT_LANG_THAI',document.getElementById('OPT_LANG_THAI').value);
					
					
					
					document.getElementById('img_OPT_HEADERS_TO_UNDISCLOSED').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_HEADERS_FROM_OR_TO_NO_DOMAIN').src='ajax-menus-loader.gif';					
					document.getElementById('img_OPT_HEADERS_FROM_OR_TO_DIGITS').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_HEADERS_SUBJECT_TOO_LONG').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_HEADERS_SUBJECT_WS_OR_DOTS').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_SPF').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_DNS_HOST_IN_DNS').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_LANG_KOREAN').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_LANG_CHINESE').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_LANG_THAI').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_LANG_JAPANESE').src='ajax-menus-loader.gif';
					document.getElementById('img_OPT_USE_DNS').src='ajax-menus-loader.gif';
					
					
					XHR.sendAndLoad('$page', 'GET',x_OPT_SPAM_RATE_LIMIT);
			}	
	</script>


"; 	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function main_index(){
	
	$sock=new sockets();
	$kasversion=unserialize(base64_decode($sock->getFrameWork("cmd.php?kasversion=yes")));
	$page=CurrentPageName();
	$kas=new kas_mysql($_GET["ou"]);
	$enable=Paragraphe_switch_img('{enable} {OPT_FILTRATION_ON}','{OPT_FILTRATION_ON_TEXT}','OPT_FILTRATION_ON',$kas->GET_KEY("OPT_FILTRATION_ON"));
	$OPT_PROBABLE_SPAM_ON=Paragraphe_switch_img('{enable} {OPT_PROBABLE_SPAM_ON}','{OPT_PROBABLE_SPAM_ON_TEXT}','OPT_PROBABLE_SPAM_ON',$kas->GET_KEY("OPT_PROBABLE_SPAM_ON"));	
	$apply=Paragraphe("system-64.png","{apply config}","{apply config text}","javascript:KasBuilder()",null,210);
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/caterpillarkas.png' id='caterpillarkas'>
			<p>&nbsp;</p>
			
			$apply
		</td>
		<td valign='top'>
			<div style='font-size:22px;font-weight:bold;color:#005447'>{APP_KAS3}</div>
			<div style='font-size:12px;text-align:right;padding-top:5px;border-top:1px solid #CCCCCC'><i>
			{version}:{$kasversion["version"]}&nbsp;{antivirus_database}: {$kasversion["pattern"]}</i></div>
			$enable
			$OPT_PROBABLE_SPAM_ON
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{edit}","OPT_FILTRATION_ON()"). "
		</td>
	</tr>
	</table>
	
	<script>
		var x_OPT_FILTRATION_ON= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshTab('main_rule_kas3');
		}	
			function OPT_FILTRATION_ON(){
					var XHR = new XHRConnection();
					XHR.appendData('OPT_FILTRATION_ON',document.getElementById('OPT_FILTRATION_ON').value);
					XHR.appendData('OPT_PROBABLE_SPAM_ON',document.getElementById('OPT_PROBABLE_SPAM_ON').value);
					XHR.appendData('ou','{$_GET["ou"]}');
					document.getElementById('img_OPT_FILTRATION_ON').src='img/wait_verybig.gif';
					document.getElementById('img_OPT_PROBABLE_SPAM_ON').src='img/wait_verybig.gif';
					XHR.sendAndLoad('$page', 'GET',x_OPT_FILTRATION_ON);
			}

			function KasBuilder(){
				document.getElementById('caterpillarkas').src='img/wait_verybig.gif';
				var XHR = new XHRConnection();
				XHR.appendData('apply-config','yes');
				XHR.sendAndLoad('$page', 'GET',x_OPT_FILTRATION_ON);
			
			}
			
	</script>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function APPLY(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kas-reconfigure=yes");
}




?>