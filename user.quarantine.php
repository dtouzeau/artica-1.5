<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.mysql.inc');
session_start();
if(isset($_GET["UserEmptyQuarantine"])){UserEmptyQuarantine();exit;}

INDEX();exit;

function INDEX(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];	
		
$html="<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'><img src='img/bg_quarantaine.jpg'>
</td>
<input type='hidden' value='{empty_quarantine_text_mesgbox}' id='empty_quarantine_text_mesgbox'>
<td valign='top'>
	<table>";

	$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-quarantine-query-64.jpg','{query_quarantine}','{query_quarantine_text}','user.quarantine.query.php') ."</td></tr>";	
	$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-quarantine-delete-64.jpg','{empty_quarantine}','{empty_quarantine_text}',"javascript:UserEmptyQuarantine();") ."</td></tr>
	
	";		
	
$sql=new MySqlQueries();	
		
$html=$html . "</table>
</td>
</tr>
<tr><td colspan=2>

<H3>{overview}</H3>
<table style='width:90%'>
<tr>
<td valign='top'><H4>{top} {domains}</H4>{$sql->Quarantine_TOP_Domains($mail)}</td>
<td valign='top'><H4>{top} {senders}</H4>{$sql->Quarantine_TOP_sender($mail)}</td>
</tr>
</table>

" . Graph($mail) ."
</table>
";
$JS["JS"][]="js/user.quarantine.js";
$tpl=new template_users('{manage_your_quarantine}',$html,0,0,0,0,$JS);
echo $tpl->web_page;	
	
}
function UserEmptyQuarantine(){
	$ldap=new clladp();
	include_once('ressources/class.mysql.inc');
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];	
	$sql="UPDATE `messages` SET `Deleted` = '1' WHERE mail_to LIKE '%$mail%' AND `filter_action`='quarantine' AND Deleted='0'";
	
	QUERY_SQL($sql);
	include_once('ressources/class.sockets.inc');
	$sock=new sockets();	
	$sock->getfile('quarantine_delete_all:'.$_SESSION["uid"]);		
	
	
}
function Graph($email){
	include_once('ressources/class.mysql.inc');
	include_once('ressources/charts.php');
	$usermenus=new usersMenus();
	$tpl=new templates();



	$sql="SELECT COUNT(ID) as tcount FROM messages WHERE mail_to LIKE '%$mail%' AND  quarantine='1'";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$quarantine_count=$ligne["tcount"];	
	
	$sql="SELECT COUNT(ID) as tcount FROM messages WHERE mail_to LIKE '%$mail%' AND  quarantine='0'";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$safe=$ligne["tcount"];		
	
	$Graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?USER_QUARANTINE=$quarantine_count&SAFE=$safe",250,250,"FFFFFF",true,$usermenus->ChartLicence);
	$Graph2=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?tempsQuarantine=$email",250,250,"FFFFFF",true,$usermenus->ChartLicence);

	
	
	$html="
	<table style='width:100%'>
	<tr>
	<td>
	<H5>{quarantines_graph}</H5>
	<center>
	$Graph
	</center>
	</td>
	<td width=50%>
	<H5>{quarantines_graph} ({monthly})</H5>	
	$Graph2
	</td>
	</tr>
	</table>
	";
	return $html;
	
}



















/*
if(isset($_GET["delete_file"])){delete_file();}
if(isset($_GET["deletall"])){deletall();}
*/

$table=Quanrantine_list();
$html="<p>&nbsp;</p>
<fieldset><legend>{manage_your_quarantine}</legend>
<br>
<table style='width:100%'>
<tr>
<td valign='top' width=50%>"  . PagesNumber() . "$table</td>
</tr>
</table>
</fieldset>

";

$tpl=new template_users("{manage_your_quarantine}",$html,$_SESSION);

echo $tpl->web_page;


function Quanrantine_list(){
	include_once('ressources/class.sockets.inc');
	$sock=new sockets();
	$quarantine_datas=preg_split("#\s+#",$sock->getfile('quarantine_size:' . $_SESSION["uid"]));
	$quarantine_size=$quarantine_datas[0];
	$quarantine_number=$quarantine_datas[1];
	$page=CurrentPageName();
	if(isset($_GET["next"])){$_GET["p"]=$_GET["from"]+1;}
	if(!isset($_GET["p"])){$_GET["p"]=0;}

	$pages_number=$quarantine_number/100;
	$start=$_GET["p"]*100;
	$end=$start+100;
	
	$datas=$sock->getfile("quarantine_list:$start-$end-".$_SESSION["uid"]);
	$datast=explode("\n",$datas);
	if(!is_array($datast)){return null;}
	$months=array("Jan"=>"01","Feb"=>"02" ,"Mar"=>"03","Apr"=>"04", "May"=>"05","Jun"=>"06", "Jul"=>"07", "Aug"=>"08", "Sep"=>"09", "Oct"=>"10","Nov"=>"11", "Dec"=>"12");
	$html="<table>";
	while (list ($num, $ligne) = each ($datast) ){
		if(preg_match('#<file>(.+)</file><from>(.+)</from><time>(.+)</time><subject>(.+)</subject>#',$ligne,$regs)){
		$file=$regs[1];
		$from=$regs[2];
		$subj=$regs[4];
		$time=$regs[3];
		$subj=wordwrap($subj, 60, " ", true);
		$subj=htmlentities($subj);
		if(preg_match('#(\w+),\s+([0-9]+)\s+(\w+)\s+([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#',$time,$ar)){
			$date=$ar[2] . "/" . $months[$ar[3]] . "/" . $ar[4];
			$time=$ar[5].":".$ar[6];
		}
		if(preg_match('#([a-zA-Z0-9\.\-_@]+)#',$from,$ir)){
			$from=$ir[1];
		}
		$style="style='border-bottom:1px dotted #CCCCCC'";
		$html=$html . "<tr>
		<td width=1%' $style><img src='img/mailbox_storage.gif'></td>
		<td $style>$date</td>
		<td $style>$time</td>
		<td $style>$from</td>
		<td $style>$subj</td>
		<td width=1% $style>" . imgtootltip('ed_delete.gif','{delete}',"window.location.href='$page?p={$_GET["p"]}&delete_file=$file'") ."</td>
		</tr>
		";
		}
		
	}
	return $html . "</table>";
}

function PagesNumber(){
	$page=CurrentPageName();
	include_once('ressources/class.sockets.inc');
	$sock=new sockets();
	$quarantine_datas=preg_split("#\s+#",$sock->getfile('quarantine_size:' . $_SESSION["uid"]));
	$quarantine_size=$quarantine_datas[0];
	$quarantine_number=$quarantine_datas[1];
	if(!isset($_GET["p"])){$_GET["p"]=1;}
	
	if(($_GET["p"]/10)>1){
		$_GET["next"]='yes';	
		$_GET["from"]=$_GET["p"];
	}
	
	if(isset($_GET["next"])){
		$start=$_GET["from"];
		$rt=$_GET["p"]-10;
		if($rt>0){
			$line_start="<li><a href='$page?next=10&from=$rt'>&laquo;&laquo;</a></li>";
		}
	}else{
		
		$start=1;}
	
	
	
	
	
	$pages_number=round($quarantine_number/100);

	$h="<div><table width=100%><tr><td><strong style='font-size:11px'>$quarantine_number emails, $pages_number pages, $quarantine_size kb</strong></td><td align='right'><input type='submit' value='{delete_quarantine}&nbsp;&raquo;' OnClick=\"javascript:window.location.href='$page?deletall=yes'\"></td></tr></table>";
	for($i=$start;$i<=$pages_number;$i++){
		$max=$max+1;
		if($max==10){
			$nextpage=$i-1;
			$list=$list."<li><a href='$page?next=10&from=$nextpage'>&raquo;&raquo;</a></li>\n";
			break;}
			if($_GET["p"]==$i){$class="id='tab_current'";}else{$class=null;}
		$list=$list."<li><a href='$page?p=$i' $class>Page $i</a></li>\n";
		
	}
	if($pages_number>1){
		$max_end="<li><a href='$page?p=$pages_number'>&raquo;&raquo;&nbsp;&raquo;&raquo;</a></li>";
	}
	return "$h<br><br><div id=tablist>$line_start$list$max_end</div>";
	
}
function delete_file(){
include_once('ressources/class.sockets.inc');
	$sock=new sockets();	
	$sock->getfile('quarantine_delete_file:'.$_GET["delete_file"].":::"  . $_SESSION["uid"]);
	
}


?>