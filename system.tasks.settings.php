<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["TasksAdd"])){TaskAddForm();exit;}
if(isset($_GET["TasksForm"])){echo TasksAdd();exit;}
if(isset($_GET["CronAddTime"])){CronAddTime();exit;}
if(isset($_GET["CronDeleteSchedule"])){CronDeleteSchedule();exit;}
if(isset($_GET["SaveTaskSettings"])){SaveTaskSettings();exit;}
if(isset($_GET["CronReloadMasterTable"])){echo ImportingLocalTasks();exit();}
if(isset($_GET["CronReloadMasterTable"])){echo ImportingLocalTasks();exit();}
if(isset($_GET["CronTaskDelete"])){CronTaskDelete();exit;}
if(isset($_GET["CronAddArticaTasks"])){CronAddArticaTasks();exit;}
if(isset($_GET["CronAddArticaTasksSelect"])){CronAddArticaTasksSelect();exit;}
if(isset($_GET["CronAddArticaTasksSave"])){CronAddArticaTasksSave();exit;}
if(isset($_GET["ImportingLocalTasks"])){ImportingLocalTasks();exit;}

INDEX();

function INDEX(){
$html="
<div style='background-image:url(img/folder-tasks-128.jpg);background-position:right top;background-repeat:no-repeat;width:100%;height:auto'>
	
	<center>
		<input type='button' value='{add_new_task}&nbsp;&raquo;' OnClick=\"javascript:TasksAdd();\">&nbsp;&nbsp;<input type='button' value='{reload}&nbsp;&raquo;' OnClick=\"javascript:CronReloadMasterTable();\">
		&nbsp;&nbsp;<input type='button' value='{add_artica_tasks}&nbsp;&raquo;' OnClick=\"javascript:CronAddArticaTasks();\">
	</center>

	<div id='local_tasks'></div>
</div>
<script>LoadAjax('local_tasks','system.tasks.settings.php?ImportingLocalTasks=yes');</script>
";




$cfg["JS"][]="js/task.js";
$tpl=new template_users('{system_tasks}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}


function ImportingLocalTasks(){
	$task=new cron();
	$hash=$task->Hash_get_tasks_list();
	if(is_array($hash)){ksort($hash);}else{
		writelogs("no array datas",__FUNCTION__,__FILE__);
	}
	
	
	
	writelogs(count($hash) . ' lines',__FUNCTION__,__FILE__);
	$html="<table style='width:600px'>
	<tr style='background-color:#CCCCCC'>
	<td><strong>&nbsp;</strong></td>
	<td><strong>{name}</strong></td>
	<td><strong>{observations}</strong></td>
	<td><strong>&nbsp;</strong></td>
	</tr>
	
	";
	if(is_array($hash)){
		while (list ($filename, $array) = each ($hash) ){
			$delete=$array["DEL"];
			if($delete=='yes'){
				$styleadd="text-decoration: line-through;color:#CCCCCC;background-color:#EAEAEA";
				$array["OBS"]='{delete}';}else{$styleadd=null;}
		$html=$html . "<tr>
		<td width=1% valign='top' class=bottom><img src='img/task-table.jpg'></td>
		<td valign='top' class=bottom style='$styleadd' " . CellRollOver("TasksAdd('$filename')")."><code style='font-size:11px'>$filename</code></td>
		<td style='font-size:9px;text-align:justify;$styleadd' class=bottom>{$array["OBS"]}&nbsp;</td>
		<td>" . imgtootltip('x.gif','{delete}',"TaskDelete('$filename');")."</td>
		</tr>
		";
		
		}
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	echo $html;
	
	
	
}

function TaskAddForm(){
$html="<div style='padding:20px' id='TaskDatas'>" . TasksAdd() . "</div>";
echo $html;
}

function TasksAdd(){
		$task=new cron();	
		if($_GET["taskName"]<>null){		
			$array=$task->GetTaskDatas($_GET["taskName"]);
			$taskname="<input type='hidden' name='taskname' id='taskname' value='{$_GET["taskName"]}'>{$_GET["taskName"]}";
			$taskCommandArray=$array["DATAS"];
			$taskCommand=$taskCommandArray["CMD"];
			$observations=$array["OBS"];
			$title=$_GET["taskName"];
			$TaskMAILTO=$array["MAIL"];
			$TaskUser=$array["DATAS"]["USER"];
		}else{
			$taskname=Field_text('taskname',null,null,null,'TaskStripCarcTaskName()');
			$title="{add_new_task}";
		}
		
		if($TaskUser==null){$TaskUser='root';}
		
//print_r($taskCommandArray);
$html="
<form name='FFMTASK'>
<H4 style='margin-bottom:10px'>$title</H4>
<table style='width:100%' style='margin:5px'>
<tr>
<td align='right' style='font-weight:bold;margin:5px;'>{task_name}:</td>
<td align='left'><strong>$taskname</strong></td>
</tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr>
<td align='right' style='font-weight:bold' valign='top'>{add_every}:</td>
<td align='left'>
	<table style='width:100%;margin:3px;padding:3px;border:1px solid #CCCCCC'>
	<tr>
	<td valign='top' align='center' style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{hours}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{minutes}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{days}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{dayw}</strong></td>
	<td valign='top' align='center' style=';border-bottom:1px solid #CCCCCC'><strong>{months}</strong></td>
	
	</tr>
	<tr>
	<td valign='top' align='center' style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>".Field_text('HOURS',null,'width:40px',null,null,'{give_a_number_0_60}',true,"CronAddTime(event,this)")."</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>".Field_text('MINUTES',null,'width:40px',null,null,'{give_a_number_0_60}',true,"CronAddTime(event,this)")."</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong".Field_text('DAY',null,'width:40px',null,null,'{give_a_number_1_31}',true,"CronAddTime(event,this)")."</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>" . Field_array_Hash($task->array_day_of_week,'DAYW',null,"CronAddTimeList(this)")."</strong></td>
	<td valign='top' align='center' style=';border-bottom:1px solid #CCCCCC'>" . Field_array_Hash($task->array_month,'MONTH',null,"CronAddTimeList(this)")."</td>
	
	</tr>	
	</table>

</tr>

<td align='right' style='font-weight:bold' valign='top'>{run_every}:</td>
<td align='left'>
	<table style='width:100%;margin:3px;padding:3px;border:1px solid #CCCCCC'>
	<tr>
	<td valign='top' align='center' style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{hours}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{minutes}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{days}</strong></td>
	<td valign='top' align='center'  style='border-right:1px dotted #CCCCCC;border-bottom:1px solid #CCCCCC'><strong>{dayw}</strong></td>
	<td valign='top' align='center' style=';border-bottom:1px solid #CCCCCC'><strong>{months}</strong></td>
	
	
	</tr>
	<tr>
	<td valign='top'  style='border-right:1px dotted #CCCCCC'>" . TableVirgule('HOURS',$taskCommandArray["HOURS"]) ."</td>
	<td valign='top'  style='border-right:1px dotted #CCCCCC'>" . TableVirgule('MINUTES',$taskCommandArray["MINUTES"]) ."</td>
	<td valign='top'  style='border-right:1px dotted #CCCCCC'>" . TableVirgule('DAY',$taskCommandArray["DAY"]) ."</td>
	<td valign='top'  style='border-right:1px dotted #CCCCCC'>" . TableVirgule('DAYW',$taskCommandArray["DAYW"]) ."</td>
	<td valign='top'>" . TableVirgule('MONTH',$taskCommandArray["MONTH"]) ."</td>
	</tr>
	</table>

	</td>
</tr>
</table>
<table style='width:100%'>
<tr>
<td align='left' style='font-weight:bold;border-bottom:1px solid #CCCCCC' valign='top'>{task_command}:</td>
</tr>
<tr>
<td align='left'><textarea name='TaskCommand' id='TaskCommand' style='width:100%;margin:4px;font-family:Arial;font-size:11px;'>$taskCommand</textarea></td>
</tr>
<tr>
<td align='left' style='font-weight:bold;border-bottom:1px solid #CCCCCC' valign='top'>{observations}:</td>
</tr>
<tr>
<td align='left'><textarea name='TaskObservations' id='TaskObservations' style='width:100%;margin:4px;font-family:Arial;font-size:11px;'>$observations</textarea></td>
</tr>
<tr>
<td align='left' style='font-weight:bold;border-bottom:1px solid #CCCCCC' valign='top'>{mailto_task}:</td>
</tr>
<tr>
<td align='left'>" . Field_text('TaskMAILTO',$TaskMAILTO)."</td>
</tr>
<tr>
<tr>
<td align='left'><strong>{username}</strong>:</td>
</tr>
<tr>
<tr>
<td align='left'>" . Field_array_Hash($task->LocalArrayUser,'taskUser',$TaskUser)."</td>
</tr>
<tr>


<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveTaskSettings();\"></td>
</tr>


</table>


</div>
";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
}

function TableVirgule($type,$line){
	if(strpos($line,',')==0){
		$array[]=$line;
	}else{$array=explode(',',$line);}
	
	$task=new cron();
	
	$html="<center><table style='width:10px' align='center'>";
	
	while (list ($index, $value) = each ($array) ){
		$delete=imgtootltip('x.gif','{delete}&nbsp;'.$value,"CronDeleteSchedule('$type','$index')");
		$img="<img src='img/fw.gif'>";
		if($value=='*'){$delete="&nbsp;";$img="&nbsp;";}
		
		switch ($type) {
			case "MONTH":$value=$task->array_month[$value];break;
			case "DAYW":$value=$task->array_day_of_week[$value];break;
			default:break;
		}
		
		
		$html=$html . "<tr>
					<td width=1%>$img</td>
					<td width=5px>$value</td>
					<td width=1%>$delete</td>
				</tr>";
	}
	
	return $html . "</table></center>";
}
function CronAddTime(){
	$tpl=new templates();
	$FieldName=$_GET["FieldName"];
	$FieldDatas=$_GET["FieldDatas"];
	$taskName=$_GET["CronAddTime"];
	if($FieldDatas<>'*'){if(!is_numeric($FieldDatas)){echo $tpl->_ENGINE_parse_body('{error_integer_waiting}');exit;}}
	
	$task=new cron();
	$arrayTask=$task->GetTaskDatas($taskName);
	$data_time=$arrayTask["DATAS"][$FieldName];
	
	writelogs("$taskName:: CMD={$arrayTask["DATAS"]["CMD"]}" ,__FUNCTION__,__FILE__);
	writelogs("$taskName:: Data times=$data_time New data=$FieldDatas" ,__FUNCTION__,__FILE__);
	
	if(strpos($data_time,',')==0){
		$array_time[]=$data_time;
	}else{
		$array_time=explode(',',$data_time);
		
	}
	
	$array_time[]=$FieldDatas;
	
	
	//prevent double data :
	while (list ($index, $value) = each ($array_time) ){
		$newArray[$value]=$value;
	}
	//sort the values
	ksort($newArray);

	
	
	$newline=implode(",",$newArray);
	$newline=str_replace('*','',$newline);
	
	if($FieldDatas=='*'){$newline="*";}
	if(substr($newline,0,1)==','){$newline=substr($newline,1,strlen($newline));}
	$arrayTask["DATAS"][$FieldName]=$newline;
	$command=$task->ImplodeCronCommand($arrayTask);
	$task->SaveTaskCommandLine($command,$taskName);
	
}
function CronDeleteSchedule(){
	$tpl=new templates();
	$FieldName=$_GET["FieldName"];
	$index=$_GET["index"];
	$taskName=$_GET["CronDeleteSchedule"];	
	$task=new cron();
	$arrayTask=$task->GetTaskDatas($taskName);	
	$data_time=$arrayTask["DATAS"][$FieldName];
	$array_time=explode(',',$data_time);
	
	writelogs("Delete $taskName=>$FieldName  index=$index value={$array_time[$index]}",__FUNCTION__,__FILE__);
	
	unset($array_time[$index]);
	//prevent double data :
	while (list ($index, $value) = each ($array_time) ){
		$newArray[$value]=$value;
	}
	//sort the values
	if(is_array($newArray)){ksort($newArray);}
	
	if(count($newArray)>0){
		$newline=implode(",",$newArray);
	}else{$newline="*";}
	$arrayTask["DATAS"][$FieldName]=$newline;
	$command=$task->ImplodeCronCommand($arrayTask);
	$task->SaveTaskCommandLine($command,$taskName);	
	}
function SaveTaskSettings(){
	$TaskMAILTO=$_GET["TaskMAILTO"];
	$taskUser=$_GET["taskUser"];
	$TaskObservations=$_GET["TaskObservations"];
	$TaskCommand=$_GET["TaskCommand"];
	$taskname=$_GET["SaveTaskSettings"];
	$taskname=str_replace(' ','-',$taskname);
	$taskname=str_replace('.','-',$taskname);
	$task=new cron();
	$task->SaveTaskSettings($taskname,$taskUser,$TaskCommand,$TaskMAILTO,$TaskObservations);	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
function CronTaskDelete(){
	$cron=new cron();
	$cron->TaskDelete($_GET["CronTaskDelete"]);	
}

function CronAddArticaTasks(){
	
	$cron=new cron();
	while (list ($index, $value) = each ($cron->array_artica_task) ){
		$arr[$index]="{{$index}}";
		}
	$arr[null]='{select}';
	$field=Field_array_Hash($arr,'articaTasks',null,'CronAddArticaTasksSelect()');
	
	
	$html="<div style='padding:20px'><H3>{add_artica_tasks}</H3>
	{add_artica_tasks_text}
	<p>$field</p>
	<p class=caption id='tasks_field_obs'></p>
	</div>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}
function CronAddArticaTasksSelect(){
	$taskname=$_GET["CronAddArticaTasksSelect"];
	$cron=new cron();
	$tpl=new templates();
	$html=$cron->array_artica_task[$taskname]["CronFileDescriptions"];
	$html=$html . "<div style='text-align:right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:CronAddArticaTasksSave();\"></div>";
	echo $tpl->_ENGINE_parse_body($html);
	}
function CronAddArticaTasksSave(){
	$task_name=$_GET["CronAddArticaTasksSave"];
	$ldap=new clladp();
	$tpl=new templates();
	$dn="cn=$task_name,cn=system_cron_task,cn=artica,$ldap->suffix";	
	if($ldap->ExistsDN($dn)){
		echo $tpl->_ENGINE_parse_body('{error_task_already_exists}');
		exit;
	}
	$cron=new cron();
	$upd['cn'][0]="$task_name";
	$upd['objectClass'][0]='ArticaCronDatas';
	$upd['objectClass'][1]='top';
	$upd['CronFileCommand'][0]=$cron->array_artica_task[$task_name]["CronFileCommand"];
	$upd['CronFileDescriptions'][0]=$cron->array_artica_task[$task_name]["CronFileDescriptions"];
	$upd['CronFileMailto'][0]=$cron->array_artica_task[$task_name]["CronFileMailto"];
	$upd["CronFileToDelete"][0]="no";
	$ldap->ldap_add($dn,$upd);	
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
	
	

