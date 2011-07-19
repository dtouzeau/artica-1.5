<?php
ini_set('memory_limit', '64M');
include_once('ressources/class.templates.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/charts.php');

if(isset($_GET["quarantine_resend"])){quarantine_resend();exit;}
if(isset($_GET["QuaratinePie"])){build_statistics();exit;}
if(isset($_GET["main"])){main_switch();exit;}
if(isset($_GET["tab"])){main_mail_switch();exit;}
if(isset($_GET["msgbodyif"])){main_mail_Inside();exit;}

main();exit;

function main(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];
	$Quarantine_message_number=Quarantine_message_number();	
		
$html="
<center><img src='img/bg_quarantaine.jpg'><strong style='border-bottom:1px dotted #CCCCCC'>{$_SESSION["uid"]}, $Quarantine_message_number emails</strong></center>
<div id='content_q'></div>
<input type='hidden' value='{empty_quarantine_text_mesgbox}' id='empty_quarantine_text_mesgbox'>


<script>
LoadAjax('content_q','$page?main=today');
</script>
";
$JS["JS"][]="js/user.quarantine.js";
$tpl=new template_users('{manage_your_quarantine}',$html,0,0,0,0,$JS);
echo $tpl->web_page;	
	
}


function Quarantine_message_number(){
$ldap=new clladp();
$hash=$ldap->UserDatas($_SESSION["uid"]);
$hash["mailAlias"][]=$hash["mail"];

if(count($hash["mailAlias"])>0){
	while (list ($num, $array) = each ($hash["mailAlias"]) ){
		$recieve[]="OR storage_recipients.recipient='{$array}'";
		
	}
	$a=implode(" ",$recieve);
	$mymails=substr($a,2,strlen($a));
	
	
}
	
$sql="SELECT Count(`quarantine`.MessageID) AS tcount FROM `quarantine`,storage_recipients WHERE 
		`quarantine`.MessageID=storage_recipients.MessageID 
			AND (".query_aliases().")";
		$mysql=new mysql();
		$ligne=mysql_fetch_array($mysql->QUERY_SQL($sql,'artica_backup'));
		return $ligne["tcount"]	;
}


function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="today";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	
	if($_GET["filter"]=="stats"){$_GET["filter"]=$_GET["main"];}
	$array["today"]='{today}';
	$array["yesterday"]='{yesterday}';
	$array["week"]='{this_week}';
	$array["stats"]="{statistics} {{$_GET["filter"]}}";
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('content_q','$page?main=$num&search={$_GET["search"]}&filter={$_GET["filter"]}')\" $class>$ligne</a></li>\n";
		}
		
	$html=$html . "<li><a href=\"javascript:LoadFind()\" $class>{search} &laquo;{$_GET["search"]}&raquo;</a></li>\n";	
	return "<br><div id=tablist>$html</div>";		
}

function main_mailtabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]="ShowMail";};
	$page=CurrentPageName();
	$array["ShowMail"]='{view_message}';
	$array["howto"]='{informations}';
	$array["resend"]='{resend}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('dialog1_content','$page?tab=$num&msgid={$_GET["msgid"]}')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<br><div id=tablist>$html</div>");		
}

function main_switch(){
	switch ($_GET["main"]) {
		case "today":$_GET["filter"]='today';main_today();exit;break;
		case "yesterday":$_GET["filter"]='yesterday';$_GET["section"]="yesterday";main_today();exit;break;
		case "week":$_GET["filter"]='week';$_GET["section"]="week";main_today();exit;break;
		case "stats":$_GET["section"]="{$_GET["filter"]}";main_statistics();exit;break;
		default:
			break;
	}
	
}

function main_mail_switch(){
	switch ($_GET["tab"]) {
		case "ShowMail":main_mail();exit;break;
		case "howto":main_mail_headers();exit;break;
		case "resend":main_mail_resend();exit;break;
	
		default:
			break;
	}	
	
}


function main_mail(){
	
	$page=CurrentPageName();
	echo main_mailtabs()."<br><iframe style='width:100%;height:700px;border:0px' src='$page?msgbodyif={$_GET["msgid"]}&msgid={$_GET["msgid"]}'>";
	
	
}

function main_mail_resend(){
	$msgid=$_GET["msgid"];
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mysql=new mysql();
	$sql="SELECT subject,mailfrom,subject FROM `quarantine` WHERE MessageID='$msgid';";
	$ligne=mysql_fetch_array($mysql->QUERY_SQL($sql,'artica_backup'));
	
	
	
	
	$html=main_mailtabs()."
	<br><H2>{resend} ($msgid)</H2>
	<br>
	<H3>{$ligne["subject"]}</H3>
	<br>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50% style='margin:5px'>
	" . RoundedLightGreen("
			<table style='width:340px;border-left:3px solid #005447'>
			<tr>
			<td align='right'><strong>{from}:</strong></td>
			<td>&nbsp;{$ligne["mailfrom"]}</td>
			</tr>
			<tr>
			<td align='right'><strong>{to}:</strong></td>
			<td>&nbsp;{$hash["mail"]}</td>
			</tr>	
			</table>
			")."
			<br>
			<div id='smtp_results'></div>
			
	</td>
	
	
	<td valign='top' style='padding:10px'>
	" . RoundedLightBlue(Paragraphe('64-resend.png','{resend}','{resend_text}',"javascript:quarantine_resend(\"$msgid\",\"{$hash["mail"]}\")",'resend'))."
	</td>
	</tr>
	</table>
	";
	
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


function main_mail_headers(){
	$s=new mysql();
	$sql="SELECT header FROM `quarantine` WHERE MessageID='{$_GET["msgid"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	$tpl=new templates();
	$ligne["header"]=htmlentities($ligne["header"]);
	$ligne["header"]=nl2br($ligne["header"]);
	
	$ligne["header"]=str_replace('X-SpamTest-Rate',"<strong style='color:red'>X-SpamTest-Rate</strong>",$ligne["header"]);
	$ligne["header"]=str_replace('X-SpamTest-Header',"<strong style='color:red'>X-SpamTest-Header</strong>",$ligne["header"]); 
	$ligne["header"]=str_replace('X-Spam-Score',"<strong style='color:red'>X-Spam-Score</strong>",$ligne["header"]); 	
	
	
	echo main_mailtabs()."<br>
	<H2>{$_GET["msgid"]}</H2>
	
	<div style='width:100%;overflow-y:auto;height:400px'><code>{$ligne["header"]}</code></div>";	
	
}

function main_mail_Inside(){
	$s=new mysql();
	$sql="SELECT MessageBody FROM `quarantine` WHERE MessageID='{$_GET["msgbodyif"]}';";
	$ligne=mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
	$tpl=new templates();
	
	echo "<div style='width:100%;overflow-y:auto;height:400px'>{$ligne["MessageBody"]}</div>";
	
	
}

function query_aliases(){
	
	if(isset($_SESSION["qaliases"])){return $_SESSION["qaliases"];}
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$hash["mailAlias"][]=$hash["mail"];

	if(count($hash["mailAlias"])>0){
		while (list ($num, $array) = each ($hash["mailAlias"]) ){
			$recieve[]="OR storage_recipients.recipient='{$array}'";
		
		}
		$a=implode(" ",$recieve);
		$mymails=substr($a,2,strlen($a));
		return $mymails;
	}
}

function main_statistics(){
$page=CurrentPageName();
$users=new usersMenus();
$tabs=main_tabs();
unset($_GET["main"]);
while (list ($num, $val) = each ($_GET) ){
	$datas=$datas. "&$num=$val";
	
}

$graph=InsertChart('js/charts.swf',"js/charts_library","$page?QuaratinePie=yes$datas",350,350,"",true,$users->ChartLicence);	
$tpl=new templates();
$html="$tabs
<input type='hidden' id='search_intro' value='{search_intro}'>
	<input type='hidden' id='section' value='{$_GET["section"]}'>
	<input type='hidden' id='Search' value='{$_GET["search"]}'>
	<input type='hidden' id='main' value='{$_GET["main"]}'>
	<br><H2>{$_GET["filter"]} {top_senders} {$_GET["datas"]}</H2>
<div style='padding:3px;margin:3px;border:1px solid #CCCCCC' id='graph'>$graph</div>

";

echo $tpl->_ENGINE_parse_body($html);
	
}

function build_statistics(){

	$today=date('Y-m-d');
	$yesterday=date("Y-m-d",mktime(0,0,0,date("m") ,date("d")-1,date("Y")));
	$mysql=new mysql();
	$query_aliases=query_aliases();
	$search_pattern=$_GET["search"];
	if(!isset($_GET["section"])){$_GET["section"]='today';}	
	
	switch ($_GET["section"]) {
			
			case "today":$todate="AND DATE_FORMAT(`quarantine`.zDate,'%Y-%m-%d')='$today'";break;
			case "yesterday";$todate="AND DATE_FORMAT(`quarantine`.zDate,'%Y-%m-%d')='$yesterday'";break;
			case "week";
				$todate="AND WEEK(`quarantine`.zDate)=WEEK(NOW())";
				$date_plus=",DATE_FORMAT(`quarantine`.zDate,'%W') AS WeekDay";
				break;
			default:$todate="AND DATE_FORMAT(`quarantine`.zDate,'%Y-%m-%d')='$today'";break;
			
		}

		
	if(isset($_GET["datas"])){
		$field="`mailfrom_domain`,`mailfrom`";
		$addp="AND mailfrom_domain='{$_GET["datas"]}'";
	}else{
		$field="`mailfrom_domain`";
	}
	$sql="SELECT COUNT(*) as tcount, $field FROM quarantine,storage_recipients WHERE 
		`quarantine`.MessageID=storage_recipients.MessageID  $todate 
		 AND (".query_aliases().")
		 $addp
		 $search_patternq GROUP BY $field ORDER BY tcount DESC LIMIT 0,10";

$results=$mysql->QUERY_SQL($sql,'artica_backup');
		$textes[]='title';
		$donnees[]='';

		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(isset($ligne["mailfrom"])){
				$textes[]=$ligne["mailfrom"];
			}else{
				$textes[]=$ligne["mailfrom_domain"];
			}
			$donnees[]=$ligne["tcount"];
			
		}
		
		$page=CurrentPageName();
unset($_GET["QuaratinePie"]);
while (list ($num, $val) = each ($_GET) ){
	$datas=$datas. "&$num=$val";
	
}		
		
		$links=array("url"=>"javascript:LoadAjax('content_q','$page?main=stats$datas',_category_)","target"=>"javascript");
		//javascript:display_info( _col_, _row_, _value_, _category_, _series_, 'Hello World!' )" target='javascript'
		    include_once(dirname(__FILE__).'/listener.graphs.php');
			BuildPieChart(array($textes,$donnees),$links);
	
}


function main_today(){
	$today=date('Y-m-d');
	$yesterday=date("Y-m-d",mktime(0,0,0,date("m") ,date("d")-1,date("Y")));
	$mysql=new mysql();
	$query_aliases=query_aliases();
	$search_pattern=$_GET["search"];
	if(!isset($_GET["section"])){$_GET["section"]='today';}
	if(!isset($_GET["page"])){$_GET["page"]=0;}
	$limit_start=$_GET["page"]*50;
	
	
	if(strlen($search_pattern)>0){
		$search_patternq="AND MATCH (MessageBody) AGAINST ('".addslashes($search_pattern)."') >0";
		}

	
	
	
	$sql="SELECT count(*) as tcount FROM quarantine,storage_recipients WHERE 
		`quarantine`.MessageID=storage_recipients.MessageID 
		 $todate 
		 AND (".query_aliases().")
		 $search_patternq";
	
	$ligne=mysql_fetch_array($mysql->QUERY_SQL($sql,'artica_backup'));
	$messages_number=$ligne["tcount"];
	$pagenumber=round($messages_number/50);
	
	for($i=0;$i<=$pagenumber;$i++){
		$array[$i]="{page}&nbsp;" .($i+1);
	}
	
	
	$sql="SELECT 
		`quarantine`.MessageID,
		`quarantine`.subject,
		mailfrom,
		DATE_FORMAT(`quarantine`.zDate,'%H:%I:%S') AS time 
		$date_plus
		FROM `quarantine`,storage_recipients WHERE 
		`quarantine`.MessageID=storage_recipients.MessageID 
		$todate
		$search_patternq
		AND (".query_aliases().") 
		ORDER BY zDate DESC LIMIT $limit_start,50";
	
	
	
	$html=$html ."<br>
	<input type='hidden' id='search_intro' value='{search_intro}'>
	<input type='hidden' id='section' value='{$_GET["section"]}'>
	<input type='hidden' id='Search' value='{$_GET["search"]}'>
	<input type='hidden' id='main' value='{$_GET["main"]}'>
	<table style='width:100%'>
	<tr><td><strong style='font-size:12px'>$messages_number emails</td>
	<td align='right'><strong>{go_to_page}:</strong></td>
	<td width=1%>" . Field_array_Hash($array,'page',$_GET["page"],"quarantine_showpage()",null,0,'width:90px')."</td>
	</table>
	<table style='width:100%'>";
	
	
		$results=$mysql->QUERY_SQL($sql,'artica_backup');
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(isset($ligne["WeekDay"])){$weekday="<i>{$ligne["WeekDay"]}</i>&nbsp;";}
			$html=$html  . "
				<tr " . CellRollOver("ShowMessage('{$ligne["MessageID"]}')","{view_message}").">
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td>$weekday{$ligne["time"]}</td>
					<td>{$ligne["mailfrom"]}</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan=2 style='margin-bottom:3px;border-bottom:1px dotted #CCCCCC;padding-bottom:3px'>
						<strong>{$ligne["subject"]}</strong>
					</td>
				</tr>";
					
			}
			
		$html=$html . "</table>";
		
		$page=main_tabs().$html;
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($page);
	
}	
	
	
function quarantine_resend(){
	$sock=new sockets();
	$datas=$sock->getfile("quaresend:{$_GET["quarantine_resend"]};{$_GET["rcpto"]}");
	$datas=htmlentities($datas);
	$datas=nl2br($datas);
	$datas=str_replace("\n",'',$datas);
	$datas=str_replace("<br /><br />","<br>",$datas);
	echo "<code>$datas</code>";
	
	
}



?>
