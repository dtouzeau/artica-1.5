<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.spamassassin.inc');
include_once('ressources/class.amavis.inc');

session_start();
$ldap=new clladp();
if(isset($_GET["loadhelp"])){loadhelp();exit;}

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

if(isset($_GET["whitelist"])){SaveWhiteList();exit;}
if(isset($_GET["del_whitelist"])){del_whitelist();exit;}
if(isset($_GET["js"])){js_popup();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["SelectedDomain"])){popup_switch();exit;}
if(isset($_GET["wblopt"])){wblopt_js();exit;}
if(isset($_GET["wblopt-popup"])){wblopt_popup();exit;}
if(isset($_GET["WBLReplicEnable"])){wblopt_save();exit;}
if(isset($_GET["WBLReplicNow"])){wblopt_replic();exit;}
if(isset($_GET["EnableWhiteListAndBlackListPostfix"])){ArticaRobotsSave();exit;}
if(isset($_GET["popup-domain-white"])){popup_domains();exit;}
if(isset($_GET["popup-domain-black"])){popup_domains();exit;}
if(isset($_GET["popup-hosts"])){popup_hosts();exit;}
if(isset($_GET["white-hosts"])){hosts_WhiteList();exit;}
if(isset($_GET["white-hosts-find"])){hosts_WhiteList_list();exit;}
if(isset($_GET["white-list-host"])){hosts_WhiteList_add();exit;}
if(isset($_GET["white-list-host-del"])){hosts_WhiteList_del();exit;}

if(isset($_GET["popup-global-black"])){blacklist_global_popup();exit;}
if(isset($_GET["popup-global-black-add"])){blacklist_global_add();exit;}
if(isset($_POST["popup-global-black-save"])){blacklist_global_save();exit;}
if(isset($_GET["popup-global-black-list"])){blacklist_global_list();exit;}
if(isset($_GET["GlobalBlackDelete"])){blacklist_global_delete();exit;}
if(isset($_GET["GlobalBlackDisable"])){blacklist_global_disable();exit;}

if(isset($_GET["popup-global-white"])){whitelist_global_popup();exit;}
if(isset($_GET["popup-global-white-add"])){whitelist_global_add();exit;}
if(isset($_POST["popup-global-white-save"])){whitelist_global_save();exit;}
if(isset($_GET["popup-global-white-list"])){whitelist_global_list();exit;}
if(isset($_GET["GlobalWhiteDisable"])){whitelist_global_disable();exit;}
if(isset($_GET["GlobalWhiteDelete"])){whitelist_global_delete();exit;}
if(isset($_GET["GlobalWhiteScore"])){whitelist_global_score();exit;}

if(isset($_GET["WhiteListResolvMX"])){WhiteListResolvMXSave();exit;}


function js_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{global_whitelist}');
	$data=file_get_contents('js/wlbl.js');
	$start="YahooWinS(700,'$page?popup=yes','$title');";
	if(isset($_GET["js-in-line"])){
		$start="document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';\n$('#BodyContent').load('$page?popup=yes');";
	}
	
	$html="
	$data
	
	function StartIndex(){
		$start
	}
	function EnableWhiteListAndBlackListPostfixEdit(){
		var EnableWhiteListAndBlackListPostfix=document.getElementById('EnableWhiteListAndBlackListPostfix').value;
		LoadAjax('EnableWhiteListAndBlackListPostfixDiv','$page?EnableWhiteListAndBlackListPostfix='+EnableWhiteListAndBlackListPostfix);
	
	}
	
	StartIndex();
	";
	echo $html;
	}
	
function wblopt_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{options}');
	$html="	
	function StartIndex2(){
		YahooWin(600,'$page?wblopt-popup=yes','$title');
	}
	

	
var x_WBLReplicNow= function (obj) {
	var results=obj.responseText;
	alert(results);
	StartIndex2();
	}
	
	
	function WBLReplicNow(){
		var XHR = new XHRConnection();
		XHR.appendData('WBLReplicNow','yes');
		document.getElementById('wbldiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WBLReplicNow);
	
	}		
	
	StartIndex2();
	";
	
	echo $html;
}

function ArticaRobotsSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableWhiteListAndBlackListPostfix",$_GET["EnableWhiteListAndBlackListPostfix"]);
	echo ArticaRobots();
	
}

function ArticaRobots(){
	
	$sock=new sockets();
	$EnableWhiteListAndBlackListPostfix=$sock->GET_INFO('EnableWhiteListAndBlackListPostfix');
	$p=Paragraphe_switch_img('{enable_artica_wbl_robots}','{enable_artica_wbl_robots_text}',
	"EnableWhiteListAndBlackListPostfix",$EnableWhiteListAndBlackListPostfix,'{enable_disbable}',300);
	$html="
	<table style='width:100%' class=table_form>
	<tr>
	<td>
	<div style='padding:3px;'>$p
	<div style='width:101%;text-align:right'>
		<input type='button' value='{edit}&nbsp;&nbsp;&raquo;&raquo;' OnClick=\"javascript:EnableWhiteListAndBlackListPostfixEdit();\">
	</div>
	</div>
	</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
	
}


function wblopt_replic(){
	$sock=new sockets();
	$sock->getfile("WBLReplicNow");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
}

function wblopt_popup(){
	$page=CurrentPageName();
	
	for($i=1;$i<30;$i++){
		$va=$i*10;
		$array[$va]=$va;
	}
	
	
	$auto=new autolearning_spam();
	
$days="<table style='width:100%' class=table_form>
<tr><td valign='top'><H3>{schedule}</h3>
<p class=caption>{run_every}...</p>
</td></tr>";	

for($i=0;$i<60;$i++){
	if($i<10){$mins[$i]="0$i";}else{$mins[$i]=$i;}
	}
for($i=0;$i<24;$i++){
	if($i<10){$hours[$i]="0$i";}else{$hours[$i]=$i;}
	}	
	
preg_match('#(.+?):(.+)#',$auto->WBLReplicSchedule["CRON"]["time"],$re);
$minutes=Field_array_Hash($mins,'msched',$re[2]);
$hour=Field_array_Hash($hours,'hsched',$re[1]);



while (list ($num, $line) = each ($auto->array_days)){
	$day=$line;
	$enabled=$auto->WBLReplicSchedule["DAYS"][$day];
	$days=$days."
	<tr>
		<td class=legend>{$day}</td>
		<td>".Field_checkbox($day,1,$enabled)."</td>
	</tr>";
	
}	

$days=$days."
<tr>
			<td class=legend>{time}</td>
			<td>$hour&nbsp;:&nbsp;$minutes</td>
		</tr>
</table>";

	
	$ArticaRobots=ArticaRobots();
	$WBLReplicEachMin=$auto->WBLReplicEachMin;
	
	if($WBLReplicEachMin==null){$WBLReplicEachMin=60;}
	if(preg_match('#([0-9]+)h#',$WBLReplicEachMin,$re)){
		$WBLReplicEachMin=$re[1]*60;
	}
	
	$form1="<table style='width:100%' class=table_form>
					<tr>
						<td class=legend>{enable_learning_spam_mailbox}:</td>
						<td>" . Field_checkbox('WBLReplicEnable',1,$auto->WBLReplicEnable) ."</td>
					</tr>
					<tr>
						<td class=legend>{enable_learning_ham_mailbox}:</td>
						<td>" . Field_checkbox('WBLReplicaHamEnable',1,$auto->WBLReplicaHamEnable) ."</td>
					</tr>
					<tr>
					<tr><td colspan=2><hr></td></tr>
						<td class=legend colspan=2>
							<input type='button' OnClick=\"javascript:WBLReplicNow()\" value='{replicate_now}&nbsp;&raquo;'>
						</td>
					</tr>	
				</table>";
	
	
	
	$html="<H1>{autolearning}</H1>
	<p class=caption>{autolearning_text}</p>
	<div id='wbldiv'>
	<form name='ffm1rep'>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top'>
		  $form1
		  <div id='EnableWhiteListAndBlackListPostfixDiv'>
		  $ArticaRobots
		  </div>
		</td>
		<td valign='top'>
			
			$days
		</td>
	</tr>
<tr>
		<td colspan=2 align='right'>
			<hr>
		</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>
			<input type='button' OnClick=\"javascript:ParseForm('ffm1rep','$page',true);\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>	
	</table>
	</form>
	</div>
		";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function wblopt_save(){
	$sock=new sockets();
	$WBLReplicEachMin=$_GET["WBLReplicEachMin"];
		if($WBLReplicEachMin>60){
		$WBLReplicEachMin=round($WBLReplicEachMin/60).'h';
	}
	
	$auto=new autolearning_spam();
	$auto->WBLReplicEachMin=$WBLReplicEachMin;
	$auto->WBLReplicaHamEnable=$_GET["WBLReplicaHamEnable"];
	$auto->WBLReplicEnable=$_GET["WBLReplicEnable"];
	
$time="{$_GET["hsched"]}:{$_GET["msched"]}";

$auto->WBLReplicSchedule["CRON"]["time"]=$time;
$auto->WBLReplicSchedule["TIME"]["time"]=$time;
	
	while (list ($num, $line) = each ($_GET)){
		$auto->WBLReplicSchedule["DAYS"][$num]=$line;
	}
	$auto->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
	}

function popup_switch(){
	$domain=$_GET["SelectedDomain"];
	$type=$_GET["type"];
	
	
	$formbl="
	<input type='hidden' id='selected_domain_black' value='$domain'>
	<table style='width:100%'>
			<tr>
			<td class=legend><strong style='font-size:13px'>{from}:</td>
			<td>" . Field_text('wlfrom_black',$_GET["whitelist"],'width:220px;font-size:13px;padding:3px',null,null,null,false,"AddblwformCheck2(1,event)") ."</td>
			<td class=legend><strong strong style='font-size:13px'>{recipient}:</td>
			<td>" . Field_text('wlto_black',$_GET["recipient"],'width:220px;font-size:13px;padding:3px',null,null,null,false,"AddblwformCheck2(1,event)") ."</td>
			<td align='right'>". button("{add}","Addblwform_black(1)")."</td>
			</tr>
		</table>";
	
	
	$formwl="<table style='width:100%'>
			<tr>
			<td class=legend><strong style='font-size:13px'>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:220px;font-size:13px;padding:3px',null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td class=legend><strong style='font-size:13px'>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:220px;font-size:13px;padding:3px',null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td align='right'>". button("{add}","Addblwform(0)")."</td>
			</tr>
		</table>";
	
	$tpl=new templates();
	switch ($type) {
		case "white":echo $tpl->_ENGINE_parse_body($formwl.whitelistdom($domain));exit;break;
		case "black":echo $tpl->_ENGINE_parse_body($formbl.blacklistdom($domain));exit;break;
		case null:echo $tpl->_ENGINE_parse_body(whitelistdom($domain)).$tpl->_ENGINE_parse_body(blacklistdom($domain));exit;break;	
		}	
	
}

function popup_hosts(){
	$page=CurrentPageName();
	$tpl=new templates();
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->javascript_parse_text('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div class=explain>{PostfixAutoBlockDenyAddWhiteList_explain}</div>
	</td>
	<td style='padding-left:10px' align='right'>". imgtootltip("cluster-replica-add.png","{add}","AddHostWhite()")."</td>
	</tr>
	</table>
	
	<div id='white-hosts' style='height:350px;overflow:auto'></div>
	
	
	<script>
	var x_AddHostWhite=function(obj){
    	var tempvalue=obj.responseText;
      	if(tempvalue.length>3){alert(tempvalue);}
 	  	LoadAjax('white-hosts','$page?white-hosts=yes');
      }	
	
	
	function AddHostWhite(){
		var server=prompt('$PostfixAutoBlockDenyAddWhiteList_explain');
		if(server){
			var XHR = new XHRConnection();
			XHR.appendData('white-list-host',server);
			XHR.sendAndLoad('$page', 'GET',x_AddHostWhite);
			}
		}
		
	function DelHostWhite(server){
			var XHR = new XHRConnection();
			XHR.appendData('white-list-host-del',server);
			XHR.sendAndLoad('$page', 'GET',x_AddHostWhite);
		}
		
	
		LoadAjax('white-hosts','$page?white-hosts=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function hosts_WhiteList_add(){
	if($_GET["white-list-host"]==null){echo "NULL VALUE";return null;}
	
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "$error";
		die();
	}	
	
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$_GET["white-list-host"])){
		$ipaddr=gethostbyname($_GET["white-list-host"]);
		$hostname=$_GET["white-list-host"];
	}else{
		$ipaddr=$_GET["white-list-host"];
		$hostname=gethostbyaddr($_GET["white-list-host"]);
	}
	
	$sql="INSERT IGNORE INTO postfix_whitelist_con (ipaddr,hostname) VALUES('$ipaddr','$hostname')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?smtp-whitelist=yes");	
}

function hosts_WhiteList_del(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "$error";
		die();
	}	
		
	$found=false;
	$server=$_GET["white-list-host-del"];
	$sql="DELETE FROM postfix_whitelist_con WHERE ipaddr='$server'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="DELETE FROM postfix_whitelist_con WHERE hostname='$server'";
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?smtp-whitelist=yes");
	
}


function hosts_WhiteList(){
	$page=CurrentPageName();
	$tpl=new templates();


	$html="
	<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{host}</td>
		<td>". Field_text("PostfixAutoBlockWhiteList-search",null,"font-size:14px;padding:3px;width:220px",null,null,null,false,"PostfixAutoBlockWhiteListSearchCheck(event)")."</td>
		<td width=1%>". button("{search}","PostfixAutoBlockWhiteListSearch()")."</td>
	</tr>
	</table>
	<div id='PostfixAutoBlockWhiteList-list' style='width:100%;height:298px;overflow:auto'></div>
	</center>
	<script>
		function PostfixAutoBlockWhiteListSearchCheck(e){
			if(checkEnter(e)){PostfixAutoBlockWhiteListSearch();}
		}
		
		function PostfixAutoBlockWhiteListSearch(){
			var se=escape(document.getElementById('PostfixAutoBlockWhiteList-search').value);
			LoadAjax('PostfixAutoBlockWhiteList-list','$page?white-hosts-find='+se);
		
		}
	PostfixAutoBlockWhiteListSearch();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function hosts_WhiteList_list(){
	
	$search=$_GET["white-hosts-find"];
	$search="*".$search."*";
	$search=str_replace("*","%",$search);
	$search=str_replace("%%","%",$search);
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con WHERE (ipaddr LIKE '$search') OR (hostname LIKE '$search') LIMIT 0,100";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	

	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:80%'>
	<thead class='thead'>
		<tr>
			<th>".imgtootltip("plus-24.png","{add}","AddHostWhite()")."</th>
			<th colspan=2></th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["ipaddr"]==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["hostname"]==null){$ligne["hostname"]=gethostbyname($ligne["ipaddr"]);}
		
		$html=$html . "<tr class=$classtr>
		<td><strong style='font-size:13px'><code>{$ligne["ipaddr"]} ({$ligne["hostname"]})</code></td>
		<td width=1%>" . imgtootltip("delete-32.png","{delete}","DelHostWhite('{$ligne["ipaddr"]}')")."</td>
	</tr>";
		
		
	}
	$html=$html."</tbody></table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function popup(){
	
	$array["popup-domain-white"]="{white list}";
	$array["popup-global-white"]="{white list}:{global}";
	$array["popup-hosts"]="{hosts}:{white list}";
	$array["popup-domain-black"]="{domains}:{black list}";
	$array["popup-global-black"]="{black list}:{global}";
	$tpl=new templates();
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_wbladmin style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_wbladmin').tabs({
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


function popup_domains(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$domain=$ldap->hash_get_all_domains();
	$domain[null]='{all}';
	$time=time();
	
	if(isset($_GET["popup-domain-white"])){$selected_type="white";}else{$selected_type="black";}
	
	
	$array["white"]='{white list}';
	$array["black"]='{black list}';	
	$array[null]='{all}';
	$field=Field_array_Hash($domain,'selected_domain',null,"SelectDomain()",null,0,"font-size:13px;padding:3px");
	$tpl=new templates();
	
	$whitelist_explain=$tpl->_ENGINE_parse_body("{whitelist_explain}");
	
	
	$old_wbl="<td valign='top'>
			<table style='width:100%' class=table_form ". element_rollover("Loadjs('$page?wblopt=yes')").">
				<tr>
					<td width=1% valign='top'>" . imgtootltip('32-settings-black.png',"{options}","Loadjs('$page?wblopt=yes')")."</td>
					<td valign='top'>
						<div style='font-size:13px;font-weight:bold'>{autolearning}</div>
						<p class=caption>{autolearning_text}</p>
					</td>
				</tr>
			</table>
		</td>";
	
	$html="
	<input type='hidden' id='selected_form' name='selected_form' value='$selected_type'>
	<input type='hidden' id='selected_form_$time' name='selected_form' value='$selected_type'>
	<div class=explain>$whitelist_explain</div>
	<div style='width:100%;text-align:right'>
	<table>
		<tr>
			<td class=legend style='font-size:13px'>{domains}:</td>
			<td>$field</td>
		</tr>
	</table>
	</div>
	
	<div id='wblarea_$time' style='width:100%;height:250px;overflow:auto'></div>
	
	<script>
	function SelectDomain(){
		var selected_domain=document.getElementById('selected_domain').value;
		var selected_form=document.getElementById('selected_form_$time').value;
		LoadAjax('wblarea_$time','$page?SelectedDomain='+selected_domain+'&type=$selected_type&time=$time');
	}
	
	var x_Addwl=function(obj){
    	var tempvalue=obj.responseText;
      	if(tempvalue.length>3){alert(tempvalue);}
 	  	LoadAjax('wblarea_$time','whitelists.admin.php?SelectedDomain='+mem_domain+'&type=$selected_type');
      }	
	
function Addblwform(){
      var XHR = new XHRConnection();
      mem_domain=document.getElementById('selected_domain').value;
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('whitelist',document.getElementById('wlfrom').value);
      XHR.appendData('recipient',document.getElementById('wlto').value);
      XHR.appendData('wbl',0);
      document.getElementById('wblarea_$time').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('$page', 'GET',x_Addwl);
      }	
      
function Addblwform_black(){
      var XHR = new XHRConnection();
      mem_domain=document.getElementById('selected_domain').value;
      XHR.appendData('RcptDomain',document.getElementById('selected_domain_black').value);
      XHR.appendData('whitelist',document.getElementById('wlfrom_black').value);
      XHR.appendData('recipient',document.getElementById('wlto_black').value);
      XHR.appendData('wbl',1);
      document.getElementById('wblarea_$time').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('$page', 'GET',x_Addwl);
      }	      
      
      
function DeleteWhiteList(to,from){
      var XHR = new XHRConnection();
      wbl=0;
      mem_domain=document.getElementById('selected_domain').value;
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('del_whitelist',from);
      XHR.appendData('recipient',to);
      XHR.appendData('wbl','0');
      document.getElementById('wblarea_$time').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('$page', 'GET',x_Addwl);    
      }
      
function DeleteBlackList(to,from){
      var XHR = new XHRConnection();
      XHR.appendData('RcptDomain',document.getElementById('selected_domain').value);
      XHR.appendData('del_whitelist',from);
      wbl=1;
      XHR.appendData('recipient',to);
      XHR.appendData('wbl','1');
      document.getElementById('wblarea_$time').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('$page', 'GET',x_Addwl);   
	 }

function AddblwformCheck2(ztype,e){
	if(checkEnter(e)){
		Addblwform_black(ztype);
	}
}	 
	
	
	SelectDomain();
	
	</script>
	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_tabs(){
	$page=CurrentPageName();
	$array["white"]='{white list}';
	$array["black"]='{black list}';
	if($_GET["section"]==null){$_GET["section"]="white";}
		
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('list','$page?main=$num&section=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<br><div id=tablist>$html</div><br>";		
}	


function whitelist(){
	$ldap=new clladp();
	
	$domain=$ldap->hash_get_all_domains();
	if(!is_array($domain)){return null;}
	
	while (list ($num, $line) = each ($domain)){
		$html=$html . whitelistdom($num);
		
		
	}
	$page=main_tabs () . "<br>" . RoundedLightGreen("
		<table style='width:99%'>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:120px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:120px',
			null,null,null,false,"AddblwformCheck(0,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(0);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>")."
	<br>	
	
	
	$html";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($page);
	
}

function whitelistdom($domain=null){

	$ldap=new clladp();
	if($domain<>null){$domain="*";}
	$hash=$ldap->WhitelistsFromDomain($domain);
	
	
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th colspan=2>{search}:$domain</th>
			<th>{from}</th>
			<th>{recipients}</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	

if(is_array($hash)){	
	while (list ($from, $line) = each ($hash)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$recipient_domain=$from;
		if(preg_match("#(.+?)@(.+)#",$recipient_domain,$re)){$recipient_domain=$re[2];}
		$ou=$ldap->ou_by_smtp_domain($recipient_domain);		
		while (list ($num, $wl) = each ($line)){
		$html=$html . 
			"<tr class=$classtr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong style='font-size:13px'>$ou</strong></td>
				<td><strong style='font-size:13px'>$wl</strong>
				<td><strong style='font-size:13px'>$from</strong></td>
				<td width=1%>" . imgtootltip('delete-32.png','{delete}',"DeleteWhiteList('$from','$wl');")."</td>
			</tr>";}
		
	}}
	
$html=$html . "</tbody></table>";
$form=$html;


return $form;

}

function SaveWhiteList(){
	$tpl=new templates();
	$to=$_GET["recipient"];
	$wbl=$_GET["wbl"];
	$RcptDomain=$_GET["RcptDomain"];
	
	$from=$_GET["whitelist"];
	if($to==null){
		$to="*@$RcptDomain";
	}
	
if($from==null){
		echo $tpl->_ENGINE_parse_body('{from}: {error_miss_datas}');return false;
	}	
	
	
	if(substr($to,0,1)=='@'){
		$domain=substr($to,1,strlen($to));
	}else{
		if(strpos($to,'@')>0){
			$tbl=explode('@',$to);
			$domain=$tbl[1];
		}else{
			$domain=$to;
			$to="@$to";
		}
	}
	
	$tbl[0]=str_replace("*","",$tbl[0]);
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	if($domains[$domain]==null){
		echo $tpl->_ENGINE_parse_body('{recipient}: {error_unknown_domain} '.$domain);return false;
	}
	
	if($tbl[0]==null){
		$ldap->WhiteListsAddDomain($domain,$from,$wbl);
		return true;
	}else{
		$uid=$ldap->uid_from_email($to);
		if($uid==null){
			echo $tpl->_ENGINE_parse_body('{recipient}: {error_no_user_exists} '.$to);return false;
		}
		$ldap->WhiteListsAddUser($uid,$from,$wbl);
	}
}


function del_whitelist(){
	$ldap=new clladp();
	$to=$_GET["recipient"];
	$from=$_GET["del_whitelist"];
	$ldap->WhiteListsDelete($to,$from,$_GET["wbl"]);
	}




function blacklist(){
	$ldap=new clladp();
	
	$domain=$ldap->hash_get_all_domains();
	if(!is_array($domain)){return null;}
	
	while (list ($num, $line) = each ($domain)){
		$html=$html . blacklistdom($num);
		
		
	}
	$page=main_tabs () . "<br>" . RoundedLightGreen("
		<table style='width:99%'>
			<tr>
			<td><strong>{from}:</td>
			<td>" . Field_text('wlfrom',$_GET["whitelist"],'width:120px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td><strong>{recipient}:</td>
			<td>" . Field_text('wlto',$_GET["recipient"],'width:120px',
			null,null,null,false,"AddblwformCheck(1,event)") ."</td>
			<td align='right'><input type='button' OnClick=\"javascript:Addblwform(1);\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>")."
	<br>	
	
	
	$html";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($page);
	
}

function blacklistdom($domain=null){
	if($domain==null){$domain="*";}
	$ldap=new clladp();
	$hash=$ldap->BlackListFromDomain($domain);	
	
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th colspan=2>{search}:$domain</th>
			<th>{from}</th>			
			<th>{recipients}</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	
if(is_array($hash)){	
	while (list ($from, $line) = each ($hash)){
		$recipient_domain=$from;
		if(preg_match("#(.+?)@(.+)#",$from,$re)){$recipient_domain=$re[2];}
		$ou=$ldap->ou_by_smtp_domain($recipient_domain);
		while (list ($num, $wl) = each ($line)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			
		$html=$html . 
			"<tr class=$classtr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong style='font-size:13px'>$ou</strong></td>
				<td><strong style='font-size:13px'>$wl</strong>
				<td><strong style='font-size:13px'>$from</strong></td>
				<td width=1%>" . imgtootltip('delete-32.png','{delete}',"DeleteBlackList('$from','$wl');")."</td>
			</tr>";}
		
	}}
	
$html=$html . "</table>";

$form=$html;


return $form;

}



class autolearning_spam{
	var $WBLReplicEachMin="6h";
	var $WBLReplicEnable=0;
	var $WBLReplicaHamEnable=0;
	var $WBLReplicSchedule=array();
	var $array_days=array();
	
	
	
	function autolearning_spam(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$this->WBLReplicEachMin=$sock->GET_INFO('WBLReplicEachMin');
		$this->WBLReplicEnable=$sock->GET_INFO('WBLReplicEnable');
		$this->WBLReplicaHamEnable=$sock->GET_INFO('WBLReplicaHamEnable');
		$ini->loadString($sock->GET_INFO('WBLReplicSchedule'));
		$this->WBLReplicSchedule=$ini->_params;
		$this->array_days=array("sunday","monday","tuesday","wednesday","thursday","friday","saturday");
		$this->BuildDefault();
		
		
	}
	
	function BuildDefault(){
		if($this->WBLReplicEachMin==null){$this->WBLReplicEachMin="6h";}
		if($this->WBLReplicEnable==null){$this->WBLReplicEnable=0;}
		if($this->WBLReplicaHamEnable==null){$this->WBLReplicaHamEnable=0;}
		
		while (list ($num, $line) = each ($this->array_days)){
			if($this->WBLReplicSchedule["DAYS"][$line]==null){$this->WBLReplicSchedule["DAYS"][$line]=1;}
			}
		if($this->WBLReplicSchedule["TIME"]["time"]==null){
			$this->WBLReplicSchedule["CRON"]["time"]="3:0";
			$this->WBLReplicSchedule["TIME"]["time"]="3:0";
		}
		reset($this->array_days);
	}
	
	
	function Save(){
		$days=null;
		$sock=new sockets();
		$sock->SET_INFO('WBLReplicEachMin',$this->WBLReplicEachMin);
		$sock->SET_INFO('WBLReplicEnable',$this->WBLReplicEnable);
		$sock->SET_INFO('WBLReplicaHamEnable',$this->WBLReplicaHamEnable);
		
		while (list ($num, $line) = each ($this->array_days)){
			if($this->WBLReplicSchedule["DAYS"][$line]==1){$days[]=$num;}
		}
		if(is_array($days)){
			
			$this->WBLReplicSchedule["CRON"]["days"]=implode(',',$days);
		}else{
			$this->WBLReplicSchedule["CRON"]["days"]=null;
		}
		$this->WBLReplicSchedule["CRON"]["time"]=$this->WBLReplicSchedule["TIME"]["time"];
		
		$ini=new Bs_IniHandler();
		$ini->_params=$this->WBLReplicSchedule;
		$sock->SaveConfigFile($ini->toString(),'WBLReplicSchedule');
		$sock->getfile("delcron:artica-autolearn");
		if($this->WBLReplicSchedule["CRON"]["days"]<>null){
			if(preg_match('#(.+?):(.+)#',$this->WBLReplicSchedule["CRON"]["time"],$re)){
				$sock->getfile("addcron:{$re[2]} {$re[1]} * * {$this->WBLReplicSchedule["CRON"]["days"]} root /usr/share/artica-postfix/bin/artica-learn >/dev/null 2>&1;artica-autolearn");
			}
		}
		
	}
	
}


function whitelist_global_popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$WhiteListResolvMX=$sock->GET_INFO("WhiteListResolvMX");
	if(!is_numeric($WhiteListResolvMX)){$WhiteListResolvMX=0;}
	
	
	$tpl=new templates();
	$add=$tpl->_ENGINE_parse_body("{add}");
	if($_GET["hostname"]==null){$_GET["hostname"]="master";}
	if($_GET["ou"]==null){$_GET["ou"]="master";}
	$popup_title=$tpl->_ENGINE_parse_body("{domains}:{white list}:{global}::{add}");
	$html="
	<table>
	<tr>
	<td width=99%><div class=explain>{whitelist_global_explain}</div></td>
	<td width=1%><div style='text-align:right'>". imgtootltip("64-plus.png","{add}","GlobalWhiteListAdd()")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<table style='width:220px'>
			<tr>
				<td class=legend>{wbl_resolv_mx}:</td>
				<td>". Field_checkbox("WhiteListResolvMX",1,$WhiteListResolvMX,"WhiteListResolvMXSave()")."</td>
				<td width=1%>". help_icon("{wbl_resolv_mx_explain}")."</td>
			</tr>
		</table>
	</table>
	<br>
	
	<center>
	<table class=form>
	<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("GlobalWhiteSearch",null,"font-size:14px;padding:3px;width:450px","script:GlobalWhiteSearchEnter(event)")."</td>
	</tr>
	</table>
	</center>
	<div id='GlobalWhite-list'></div>
	
	<script>
		function GlobalWhiteListAdd(){
			YahooWin4('550','$page?popup-global-white-add=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$_GET["hostname"]}::$popup_title');
		
		}
		
		function WhiteListResolvMXSave(){
			var enabled=0;
			if(document.getElementById('WhiteListResolvMX').checked){enabled=1;}
			var XHR = new XHRConnection();
			XHR.appendData('WhiteListResolvMX',enabled);
			XHR.sendAndLoad('$page', 'GET');		
		
		}
		
		function GlobalWhiteSearchEnter(e){
			if(checkEnter(e)){GlobalWhiteRefresh();}
		}
		
		function GlobalWhiteRefresh(){
			var se=escape(document.getElementById('GlobalWhiteSearch').value);
			LoadAjax('GlobalWhite-list','$page?popup-global-white-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}&search='+se);
		}
		GlobalWhiteRefresh();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
	
}

function blacklist_global_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add=$tpl->_ENGINE_parse_body("{add}");
	if($_GET["hostname"]==null){$_GET["hostname"]="master";}
	if($_GET["ou"]==null){$_GET["ou"]="master";}
	$popup_title=$tpl->_ENGINE_parse_body("{domains}:{black list}:{global}::{add}");
	$html="
	<table>
	<tr>
	<td width=99%><div class=explain>{blacklist_global_explain}</div></td>
	<td width=1%><div style='text-align:right'>". button("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{add}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","GlobalBlackListAdd()")."</td>
	</tr>
	</table>
	<br>
	
	<center>
	<table class=form>
	<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("GlobalBlackSearch",null,"font-size:14px;padding:3px;width:450px","script:GlobalBlackSearchEnter(event)")."</td>
	</tr>
	</table>
	</center>
	<div id='GlobalBlack-list'></div>
	
	<script>
		function GlobalBlackListAdd(){
			YahooWin4('550','$page?popup-global-black-add=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$_GET["hostname"]}::$popup_title');
		
		}
		
		function GlobalBlackSearchEnter(e){
			if(checkEnter(e)){GlobalBlackRefresh();}
		}
		
		function GlobalBlackRefresh(){
			var se=escape(document.getElementById('GlobalBlackSearch').value);
			LoadAjax('GlobalBlack-list','$page?popup-global-black-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}&search='+se);
		}
		GlobalBlackRefresh();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}




function blacklist_global_add(){
	$tpl=new templates();
	$page=CurrentPageName();

	$html="
	<div id='globalblack-smtp-div'>
	<div class=explain>{blacklist_global_add_explain}</div>
	<textarea id='globalblack-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","GlobalBlackSave()")."</div>
	</div>
	<script>
	
	var x_GlobalBlackSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		YahooWin4Hide();
		GlobalBlackRefresh();
	}			
		
	function GlobalBlackSave(){
		var XHR = new XHRConnection();
		XHR.appendData('popup-global-black-save',document.getElementById('globalblack-servers-container').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('globalblack-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_GlobalBlackSave);		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function whitelist_global_add(){
	$tpl=new templates();
	$page=CurrentPageName();

	$html="
	<div id='globalwhite-smtp-div'>
	<div class=explain>{whitelist_global_add_explain}</div>
	<textarea id='globalwhite-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","GlobalWhiteSave()")."</div>
	</div>
	<script>
	
	var x_GlobalWhiteSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		YahooWin4Hide();
		GlobalWhiteRefresh();
	}			
		
	function GlobalWhiteSave(){
		var XHR = new XHRConnection();
		XHR.appendData('popup-global-white-save',document.getElementById('globalwhite-servers-container').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('globalwhite-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_GlobalWhiteSave);		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);	
}



function blacklist_global_save(){
	
	$hostname=$_POST["hostname"];
	$datas=explode("\n",$_POST["popup-global-black-save"]);
	$prefix="INSERT INTO postfix_global_blacklist (sender,hostname) VALUES ";
	
	if(!is_array($datas)){echo "No data";return;}
	while (list ($num, $words) = each ($datas) ){	
		if(trim($words)==null){continue;}
		$words=addslashes($words);
		$ws[]="('$words','$hostname')";
	}
	
	$q=new mysql();
	$q->BuildTables();
	$sql=$prefix.@implode(",",$ws);
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions={$_GET["hostname"]}");	
	
}

function whitelist_global_save(){
	$hostname=$_POST["hostname"];
	$datas=explode("\n",$_POST["popup-global-white-save"]);
	$prefix="INSERT INTO postfix_global_whitelist (sender,hostname) VALUES ";
	
	if(!is_array($datas)){echo "No data";return;}
	while (list ($num, $words) = each ($datas) ){	
		if(trim($words)==null){continue;}
		$words=addslashes($words);
		$ws[]="('$words','$hostname')";
	}
	
	$q=new mysql();
	$q->BuildTables();
	$sql=$prefix.@implode(",",$ws);
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-whitelisted-global=yes");	
	
}

function whitelist_global_list(){
	$tpl=new templates();
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$EnableAmavisDaemon=$users->EnableAmavisDaemon;
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}	
	if($EnableAmavisDaemon==1){
		$amavis=new amavis();
		$max_score=$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"];
	}	
	
	$max_score_white_text=$tpl->javascript_parse_text("{max_score_white_text}\\n{score}:$max_score");
	
	
	$page=CurrentPageName();
	$se="%{$_GET["search"]}%";
	$se=str_replace("*","%",$se);
	$se=str_replace("%%","%",$se);
	
	
	$sql="SELECT * FROM postfix_global_whitelist WHERE `hostname`='{$_GET["hostname"]}' AND `sender` LIKE '$se' ORDER BY sender LIMIT 0,100";
	$tpl=new templates();
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	echo $tpl->_ENGINE_parse_body("
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{sender}</th>
		<th>{score}</th>
		<th>{enabled}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>");		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",1,$ligne["enabled"],"GlobalWhiteDisable('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","GlobalWhiteDelete('{$ligne["ID"]}')");
		$modifyScore="<a href=\"javascript:blur();\" OnClick=\"javascript:GlobalScoreModify('{$ligne["ID"]}','$score');\" style='text-decoration:underline;font-weight:bold'>";
		
		if($score==0){$score="{no}";}else{$score="-{$ligne["score"]}";}
		$icon="datasource-32.png";
		if($EnableAmavisDaemon==0){$score="{disabled}";}
		
		echo $tpl->_ENGINE_parse_body( "
		<tr  class=$classtr>
		<td width=1%><img src='img/$icon'></td>
		<td><strong style='font-size:14px'><code>{$ligne["sender"]}</code></td>
		<td width=1% align='center'><strong style='font-size:14px' id='score_{$ligne["ID"]}'>$modifyScore$score</a></strong></td>
		<td width=1% align='center'>$disable</td>
		<td width=1%>$delete</td>
		</td>
		</tr>");
		
	}
	echo"</tbody></table>
	
	
	<script>
	
	
	var x_GlobalWhiteDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		GlobalWhiteRefresh();
	}	
	
	var x_GlobalWhiteDisable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		
	}		
	
	function GlobalWhiteDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('GlobalWhiteDelete',key);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		document.getElementById('GlobalWhite-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_GlobalWhiteDelete);
		}	
		
	function GlobalWhiteDisable(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('GlobalWhiteDisable',1);}else{XHR.appendData('GlobalWhiteDisable',0);}
		XHR.sendAndLoad('$page', 'GET',x_GlobalWhiteDisable);
	}
	
	
	
	
	function GlobalScoreModify(ID,score){
		var score=prompt('$max_score_white_text',score);
		if(score){
			var XHR = new XHRConnection();
			XHR.appendData('GlobalWhiteScore','yes');
			XHR.appendData('score',score);
			XHR.appendData('ID',ID);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			document.getElementById('score_'+ID).innerHTML=score;		
			XHR.sendAndLoad('$page', 'GET',x_GlobalWhiteDisable);		
		}
	}
			
	
	</script>";
	
		
		
	
}




function blacklist_global_list(){
	$page=CurrentPageName();
	$se="%{$_GET["search"]}%";
	$se=str_replace("*","%",$se);
	$se=str_replace("%%","%",$se);
	
	
	$sql="SELECT * FROM postfix_global_blacklist WHERE `hostname`='{$_GET["hostname"]}' AND `sender` LIKE '$se' ORDER BY sender LIMIT 0,100";
	$tpl=new templates();
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{sender}</th>
		<th>{enabled}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",1,$ligne["enabled"],"GlobalBlackDisable('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","GlobalBlackDelete('{$ligne["ID"]}')");
		
		$icon="datasource-32.png";
		
		$html=$html . "
		<tr  class=$classtr>
		<td width=1%><img src='img/$icon'></td>
		<td><strong style='font-size:14px'><code>{$ligne["sender"]}</code></td>
		<td width=1% align='center'>$disable</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$html=$html."</tbody></table>
	
	
	<script>
	var x_GlobalBlackDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		GlobalBlackRefresh();
	}	
	
	var x_GlobalBlackDisable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		
	}		
	
	function GlobalBlackDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('GlobalBlackDelete',key);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		document.getElementById('GlobalBlack-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_GlobalBlackDelete);
		}	
		
	function GlobalBlackDisable(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('GlobalBlackDisable',1);}else{XHR.appendData('GlobalBlackDisable',0);}
		XHR.sendAndLoad('$page', 'GET',x_GlobalBlackDisable);
	}
			
	
	</script>";
	
		
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function blacklist_global_delete(){
	if(!is_numeric($_GET["GlobalBlackDelete"])){return null;}
	$sql="DELETE FROM postfix_global_blacklist WHERE ID='{$_GET["GlobalBlackDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions={$_GET["hostname"]}");	
}
function whitelist_global_delete(){
	if(!is_numeric($_GET["GlobalWhiteDelete"])){return null;}
	$sql="DELETE FROM postfix_global_whitelist WHERE ID='{$_GET["GlobalWhiteDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-whitelisted-global=yes");	
}

function whitelist_global_score(){
	if(!is_numeric($_GET["ID"])){return null;}
	$sql="UPDATE postfix_global_whitelist SET score='{$_GET["score"]}' WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-whitelisted-global=yes");	
}


function whitelist_global_disable(){
	if(!is_numeric($_GET["ID"])){return null;}
	$sql="UPDATE postfix_global_whitelist SET enabled='{$_GET["GlobalWhiteDisable"]}' WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-whitelisted-global=yes");
}

function blacklist_global_disable(){
	if(!is_numeric($_GET["ID"])){return null;}
	$sql="UPDATE postfix_global_blacklist SET enabled='{$_GET["GlobalBlackDisable"]}' WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions={$_GET["hostname"]}");
}

function WhiteListResolvMXSave(){
	$sock=new sockets();
	$sock->SET_INFO("WhiteListResolvMX",$_GET["WhiteListResolvMX"]);
	$sock->getFrameWork("cmd.php?WhiteListResolvMX=yes");
}


?>