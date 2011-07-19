<?php
include_once ("ressources/class.templates.inc");
include_once ("ressources/charts.php");
include_once('ressources/class.mysql.inc');

if(isset($_GET["corrupt"])){corrupt();exit;}
if(isset($_GET["Corrupt_count"])){corrupt_count();exit;}
if(isset($_GET["corrupted_move"])){corrupted_move();exit;}
if(isset($_GET["corrupted_reload"])){corrupted_reload();exit;}
if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}


js();

function js(){
	

if(privs()){
	$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	echo "alert('$error);";
	die();
}	
	
$users=new usersMenus();
$corrupt=$users->POSTFIX_QUEUE["corrupt"];

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_POSTFIX}");
	$warning=$tpl->_ENGINE_parse_body("$corrupt {corrupt} mails\\n {corrupt_queue_confirm}","postfix.index.php");
	$warning=str_replace(".","\\n",$warning);
	$page=CurrentPageName();
	$text=
	
	$html="
	
	function LoadCorrupt(){
		var ask=confirm('$warning');
		if(ask){
			YahooWin(500,'$page?corrupt=yes','$title');
			CorruptWait();
		}
		
	}
	
	function CorruptWait(){
		if(!document.getElementById('progression_postfix')){
			setTimeout('CorruptWait',200);
		}
		setTimeout('Corrupt_count()',1000);
	
	}
	
	var x_ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_postfix').innerHTML=tempvalue;
	}		
	
	function ChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		XHR.sendAndLoad('$page', 'GET',x_ChangeStatus);	
	}

	
	var x_Corrupt_count= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		corrupted_move();
	}	
	
	var x_corrupted_move= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		corrupted_reload();
	}	
	
	var x_corrupted_reload= function (obj) {
		var tempvalue=obj.responseText;
		FilLogs(tempvalue);
		finish();
	}	
	
	
	function finish(){
	document.getElementById('wait').innerHTML='';
	ChangeStatus(100);
	}	
	
	
	function Corrupt_count(){
		ChangeStatus(10);
		var XHR = new XHRConnection();
		XHR.appendData('Corrupt_count','yes');
		XHR.sendAndLoad('$page', 'GET',x_Corrupt_count);	
		}	
	function corrupted_move(){
		ChangeStatus(45);
		var XHR = new XHRConnection();
		XHR.appendData('corrupted_move','yes');
		XHR.sendAndLoad('$page', 'GET',x_corrupted_move);	
		}
	function corrupted_reload(){
		ChangeStatus(80);
		var XHR = new XHRConnection();
		XHR.appendData('corrupted_reload','yes');
		XHR.sendAndLoad('$page', 'GET',x_corrupted_reload);	
		}					
		

	function FilLogs(logs){
		logs=escapeVal(logs,'<br>');
		var textlogs=document.getElementById('textlogs').innerHTML;
		textlogs='<div style=\"margin:3px;padding:3px;border-bottom:1px solid #CCCCCC\"><code>'+logs+'</code></div>'+textlogs;
		document.getElementById('textlogs').innerHTML=textlogs;
	}
	
	function escapeVal(content,replaceWith){
		content = escape(content) 
	
			for(i=0; i<content.length; i++){
				if(content.indexOf(\"%0D%0A\") > -1){
					content=content.replace(\"%0D%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0A\") > -1){
					content=content.replace(\"%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0D\") > -1){
					content=content.replace(\"%0D\",replaceWith)
				}
	
			}	
		return unescape(content);
	}			
	
	
	LoadCorrupt();";
	
	echo $html;
	
}

function privs(){
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){$niprov=true;}
return $niprov;
}

function corrupt(){
	$users=new usersMenus();
	$tpl=new templates();
	if(privs()){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
$users=new usersMenus();
$corrupt=$users->POSTFIX_QUEUE["corrupt"];

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_POSTFIX}");
	$warning=$tpl->_ENGINE_parse_body("$corrupt {corrupt} mails\\n {corrupt_queue_confirm}","postfix.index.php");	
	
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";
	$html="<H1>{APP_POSTFIX}: $corrupt {corrupt} eMail(s)</H1>
	
	<table style='width:100%'>
	<tr>
		<td width=1%><span id='wait'><img src='img/wait.gif'></span>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_postfix'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>
	<br>
	" . RoundedLightWhite("<div id='textlogs' style='width:99%;height:120px;overflow:auto'></div>")."";
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}
function Status($pourc){
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
";	


return $html;
	
}

function corrupt_count(){
$users=new usersMenus();
$corrupt=$users->POSTFIX_QUEUE["corrupt"];
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("$corrupt corrupted mails");	
}

function corrupted_move(){
$sock=new sockets();
echo $sock->getfile("PostfixCorruptedMove")."\n";
echo "move corrupted queue done...";
}

function corrupted_reload(){
$sock=new sockets();
$sock->getfile("postqueue_f");
echo "Flush the queue done...";
}


