<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.os.system.inc');

	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
	

if(isset($_GET["PID"])){PIDInfos();exit;}
if(isset($_GET["reload"])){echo page_proc();exit;}
if(isset($_GET["KillProcessByPid"])){KillProcessByPid();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["getmem"])){getmem();exit;}
if(isset($_GET["getcpu"])){getCpu();exit;}
if(isset($_GET["taskslist"])){processes();exit;}
if(isset($_GET["swap-infos"])){GetSwap();exit;}
js();

function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{empty_swap}');
	
	$html="
	var {$prefix}tant=0;

	
	function {$prefix}demarre(){
		if(!RTMMailOpen()){return;}
		{$prefix}tant = {$prefix}tant+1;
		if ({$prefix}tant < 10 ) {                           
	      	setTimeout(\"{$prefix}demarre()\",950);
	      } else {
				{$prefix}tant = 0;
				{$prefix}ChargeLogs();
				{$prefix}demarre();                                
	   }
	}

	function {$prefix}LoadPage(){
		RTMMail(700,'$page?popup=yes','$title');
		{$prefix}demarre();
	}
	
	var x_{$prefix}ChargeLogs3= function (obj) {
		var results=obj.responseText;
		document.getElementById('swap_infos').innerHTML=results;
			
	}		

	var x_{$prefix}ChargeLogs2= function (obj) {
		var results=obj.responseText;
		var randomnumber=Math.floor(Math.random()*11000);
		document.getElementById('CurrentSystemTaskManagerCPU').innerHTML=results;
		var XHR = new XHRConnection();
		XHR.appendData('swap-infos','yes');
		XHR.appendData('ran',randomnumber);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs3);			
	}	
	
	var x_{$prefix}ChargeLogs= function (obj) {
		var results=obj.responseText;
		document.getElementById('CurrentSystemTaskManager').innerHTML=results;
		var randomnumber=Math.floor(Math.random()*11000);
		var XHR = new XHRConnection();
		XHR.appendData('getcpu','yes');
		XHR.appendData('ran',randomnumber);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs2);		
				
	}	
	
	function {$prefix}ChargeLogs(){
		var randomnumber=Math.floor(Math.random()*11000);
		var XHR = new XHRConnection();
		XHR.appendData('getmem','yes');
		XHR.appendData('ran',randomnumber);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs);
	
	}
	
var x_ParseFormLDAP= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				LDAPInterFace();
			}		
	

	
	
{$prefix}LoadPage();";
echo $html;
}

function popup(){
	include_once("ressources/class.os.system.tools.inc");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?free-cache=yes");
	
	$os=new os_system();
	$mem=$os->html_Memory_usage();
	
	$html="<H1>{empty_swap}</H1>
	<table style=width:100%>
	<tr>
	<td valign='top'>
		<div id='CurrentSystemTaskManagerCPU'></div>	
		<div id='swap_infos'></div>
	</td>	
	<td valign='top'>
		<div id='CurrentSystemTaskManager'>$mem</div>
	</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function GetSwap(){
	include_once("ressources/class.os.system.tools.inc");
	$swap=new os_system();	
	$swapar=$swap->swap();
	$mem=$swap->memory();
	
	
$swap_percent=$mem["swap"]["percent"];
$swap_used=$mem["swap"]["used"];
$ram_free=$mem["ram"]["free"];
$ram_total=$mem["ram"]["total"];
$operation_disponible=$ram_free-$swap_used;	
if($operation_disponible<0){
	$operation_disponible=$swap_used-$ram_free;
	$operation_disponible="{no_memory_free_to_empty_cache} ({need}: ". FormatBytes(intval($operation_disponible)).")";
}

	$maxswap=FormatBytes($swapar[2]);
	$cureswap=FormatBytes($swapar[1]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<div style='width:100%;text-align:right;margin-top:10px;color:#640000'>
	<span style='font-size:16px;font-weight:bold'>
	swap:&nbsp;$cureswap/$maxswap ({$swapar[0]}%) </span><hr><span style='color:red'>$operation_disponible</span></div>");

}


function getmem(){
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$html=$os->html_Memory_usage();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function getLoad(){
	$users=new usersMenus();
	$sock=new sockets();
	$array_load=sys_getloadavg();
	$org_load=$array_load[0];
	$cpunum=intval($users->CPU_NUMBER);
	
	$load=intval($org_load);
	//middle =$cpunum on va dire que 100% ($cpunum*2) + orange =0,75*$cpunum
	$max_vert_fonce=$cpunum;
	$max_vert_tfonce=$cpunum+1;
	$max_orange=$cpunum*0.75;
	$max_over=$cpunum*2;
	$purc1=$load/$cpunum;
	$pourc=round($purc1*100,2);
	$color="#5DD13D";
	if($load>=$max_orange){
		$color="#F59C44";
	}
	
	if($load>$max_vert_fonce){
		$color="#C5792D";
	}

	if($load>$max_vert_tfonce){
		$color="#83501F";
	}	
	

	
	if($load>=$max_over){
		$color="#640000";
		$text="<br>".texttooltip("{overloaded}","{overloaded}","Loadjs('overloaded.php')",null,0,"font-size:9px;font-weight:bold;color:red");
	}	

	if($pourc>100){$pourc=100;}

return "
<tr>
	<td width=1% nowrap class=legend nowrap>{load_avg}:</strong></td>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
			<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
				<span style='color:white;font-size:11px;font-weight:bold'>$pourc%</span>
			</div>
		</div>
	</td>
	<td width=1% nowrap><strong>{load}: $org_load&nbsp;[$cpunum cpu(s)]$text</strong></td>
</tr>";		
}

function getCpu(){
	
	$sock=new sockets();
	$cpu_purc=$sock->getFrameWork("cmd.php?cpualarm=yes");
	$cpu_purc_text=$cpu_purc."%";
	$cpu_color="#5DD13D";
	if($cpu_purc>70){$cpu_color="#F59C44";}
	if($cpu_purc>90){$cpu_color="#D32D2D";}
	$pouc_disk_io_text="<br><span style='font-size:9px'>% CPU:$pouc_disk_io%</span>";
	$cpu="
	<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
		<div style='width:{$pouc_disk_io}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'></div>
	</div>
	
	
	";
	
	
$cpu="<tr>
				<td width=1% nowrap class=legend nowrap>{cpu_usage}:</strong></td>
				<td align='left'>
					<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $cpu_color'>
						<div style='width:{$cpu_purc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$cpu_color'>
							<strong>{$cpu_purc}%</strong></div>
					</div>
				</td>
				<td width=1% nowrap><strong style='color:$cpu_color'>{$cpu_purc}%</strong></td>
				</tr>";	
$load=getLoad();
	$html="<table>$cpu$load
		</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}



function Page(){
$html="<div style='padding:20px;height:350px;overflow:auto' id='page_taskM'>

" . page_proc() . "</div>";
echo $html;
	
}

function page_proc(){
	$sock=new sockets();
	$tpl=new templates();
	
	$sock->getfile('TaskManager');
	include_once("ressources/psps.inc");
	
	
	
	
	$html="
	<center><input type='button' OnClick=\"javascript:ReloadTaskManager();\" value='&laquo;&nbsp;{reload}&nbsp;&raquo;'></center>
	<table style='width:100%;border:1px solid #CCCCCC;padding:3px'>
	 	<tr style='background-color:#CCCCCC'>
	 	<td valign='middle' class='bottom' style='font-size:10px;font-weight:bold'>&nbsp;</td>
	 	<td valign='top' class='bottom' style='font-size:10px;font-weight:bold'>&nbsp;{process_name}</td>
	 	<td valign='top' class='bottom' style='font-size:10px;font-weight:bold'>&nbsp;PID</td>
	 	<td valign='top' class='bottom'  style='font-size:10px;font-weight:bold'>&nbsp;{memory}</td>
	 	</tr>
	 	";
	
	 while (list ($num, $ds) = each ($processes) ){
	 	
	 	$tools=ParseArray($ds['status']);
	 	$tooltip=CellRollOver("ProcessTaskEdit('{$num}')",$tools);
	 	$html=$html . "
	 	<tr $tooltip>
	 	<td valign='middle' class='bottom' style='font-size:10px'><img src='img/fw-vert-s.gif'></td>
	 	<td valign='top' class='bottom' style='font-size:10px'>{$ds['status']["name"]}</td>
	 	<td valign='top' class='bottom' style='font-size:10px'>{$num}</td>
	    <td valign='top' class='bottom'  style='font-size:10px'>". FormatBytes($ds['memory'])."</td>
	 	</tr>
	 	";}
	 	
	 
	
	$html=$html . "
	</table>
	";
	return  $tpl->_ENGINE_parse_body($html);
	
}


function ParseArray($LINE){
	
	while (list ($num, $ligne) = each ($LINE) ){
		
		
		$html=$html."<tr><td width=1% nowrap><strong>$num</strong></td><td width=1% nowrap><strong>$ligne</strong></td></tr>";
		
		
		
	}
	
	return "<table style=width:250px>$html</table>";
	
	
}

function PIDInfos(){
	$PID=$_GET["PID"];
	$sock=new sockets();
	$sock->getfile('TaskManager');
	include_once("ressources/psps.inc");
	$ARRAY=$processes[$PID];
	
	while (list ($num, $ligne) = each ($ARRAY["status"]) ){
		$html=$html .
		"
		<tr>
		<td align='right' valign='top'><strong style='font-size:11px'>$num:</td>
		<td align='left' valign='top'><strong style='font-size:11px'>$ligne</td>
		</tR>";
		
	}
	
	
	$html="
	<H4>{$ARRAY["status"]["processname"]} (pid $PID <code>{$ARRAY["process_path"]}</code>)</H4>
	
		<center><input type='button' value='{kill_process}&nbsp;&raquo;' OnClick=\"javascript:KillProcessByPid('$PID');\"></center>
	<div style='padding:20px;height:320px;overflow:auto'>
		<table>
			$html
		</table>
	</div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function KillProcessByPid(){
	$pid=$_GET["KillProcessByPid"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kill-pid-number={$_GET["KillProcessByPid"]}");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{success}\n$datas");
	
	
}
?>