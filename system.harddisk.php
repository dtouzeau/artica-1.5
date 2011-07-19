<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.harddrive.inc');
	include_once('ressources/charts.php');
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}
	if($_GET["main"]=='graph'){echo graph();exit;}
	if($_GET["main"]=='config'){echo folders_list();exit;}
	if(isset($_GET["SaveFolderList"])){SaveFolderList();exit;}
	if(isset($_GET["AddFolderList"])){AddFolderList();exit;}
	if(isset($_GET["DeleteFolderList"])){DeleteFolderList();exit;}
	if(isset($_GET["follow"])){follow();exit;}
	
	
	applications_Status();	

function applications_Status(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	
	
	$html="<div id='hardlist'>$all</div>
	<div id='folderslist'></div>
	<script>LoadAjax('hardlist','$page?main=graph');</script>
	
	";
	
	

	
	$JS["JS"][]="js/system.js";
	$tpl=new template_users('{folders_monitor}',$html,0,0,0,0,$JS);
	echo $tpl->web_page;
	
}

function graph(){
	$usersmenus=new usersMenus();
	$all=main_tabs() ."<br>
	<H5>{folders_statistics}</H5>
	<br>
	<div style='padding:3px;border:1px dotted #CCCCCC'>
	".InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?FollowHardDisks=yes",550,450,"FFFFFF",true,$usersmenus->ChartLicence) ."</div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($all);
	
	
}

function main_tabs(){
	$page=CurrentPageName();
	$array["graph"]='{folders_statistics}';
	$array["config"]='{parameters}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('hardlist','$page?main=$num&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function folders_list(){

	$hard=new harddrive();
	$hard->BuildSizes();
	

	$html="<table style='width:100%'>";
	if(is_array($hard->main_array["folders_list"])){
		reset($hard->main_array["folders_list"]);
		
		
		while (list ($num, $ligne) = each ($hard->main_array["folders_list"]) ){
			$id=md5($ligne);
			$html=$html."<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>" . Field_text($id,$ligne) . "</td>
			<td><input type='button' OnClick=\"javascript:SaveFolderList($num,'$id');\" value='{edit}&nbsp;&raquo;'></td>
			<td>" . imgtootltip('x.gif','{delete}',"DeleteFolderList($num)") . "</td>
			</tr>
			
			";
		
		}	
	
		
	}
	
	$html=$html."<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>" . Field_text('addfodler') . "</td>
			<td><input type='button' OnClick=\"javascript:AddFolderList();\" value='{add}&nbsp;&raquo;'></td>
			<td>&nbsp;</td>
			</tr>";
	$html=$html. "</table>";
	$html=main_tabs()."<br><h5>{parameters}</h5><br><p class=caption>{parameters_text}</p>" .RoundedLightGrey($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}


function disk(){
$tpl=new templates();
	$sys=new systeminfos();
	$hash=$sys->DiskUsages();
	$page=CurrentPageName();	
	if(!is_array($hash)){return null;}
	$img="<img src='img/fw_bold.gif'>";
	$html="
	<table style='width:100%'>
<tr>
	<td align='right' colspan=2>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('disk','$page?disk=yes');")."</td>
</tr>	
		<tr>
		<td width=1% valign='top'><img src='img/disk-64.png'></td>
		<td valign='top'>
			<H5>{disks_usage}</h5>
			<table style='width:100%' align=center>
				<tr style='background-color:#CCCCCC'>
					<td>&nbsp;</td>
					<td><strong>{Filesystem}</strong></td>
					<td><strong>{size}</strong></td>
					<td><strong>{used}</strong></td>
					<td><strong>{available}</strong></td>
					<td align='center'><strong>{pourcent}</strong></td>
					<td><strong>{mounted_on}</strong></td>
				</tr>
	";
	
	 while (list ($num, $ligne) = each ($hash) ){
	 	$html=$html . "<tr " . CellRollOver().">
	 	<td width=1% class=bottom>$img</td>
	 	<td class=bottom>{$ligne[0]}</td>
	 	<td class=bottom>{$ligne[2]}</td>
	 	<td class=bottom>{$ligne[3]}</td>
	 	<td class=bottom>{$ligne[4]}</td>
	 	<td align='center' class=bottom><strong>{$ligne[5]}</strong></td>
	 	<td class=bottom>{$ligne[6]}</td>
	 	</tr>
	 	";
	 	
	 }
	 
	 $html=$html . "</table>";
	 
	return "<br>".RoundedLightGrey($tpl->_ENGINE_parse_body($html)."</td></tr></table></td></tr></table>");
	
}

function follow(){
	if(!preg_match('#(.+?):(.*)#',$_GET["datas"],$re)){
	return nul;}
	$re[2]=trim($re[2]);
	$source_path=$re[1];
	$source_size=$re[2];
	
	if($source_path<>"Total"){
	
			$socks=new sockets();
			$datas=$socks->getfile("FollowFolderSize:$source_path");
			$tbl=explode("\n",$datas);
			if(is_array($tbl)){
				while (list ($num, $ligne) = each ($tbl) ){
					if(preg_match('#([0-9]+)\s+(.+)#',$ligne,$re)){
						$array["{$re[1]}.$num"]=$re[2];
					}
					
				}
				
			krsort($array);
			$table="<table style='width:100%'>";
			while (list ($num, $ligne) = each ($array) ){
					preg_match('#([0-9]+)\.#',$num,$le);
					$size=$le[1];
					if($size>1000){$size=round($size/1000,2) ." mb";}else{$size=$size . " bytes";}
					$table=$table . "<tr>
					<td width=1%><img src='img/fw_bold.gif'>
					<td width=1% nowrap><strong>$size</strong></td>
					<td width=99%><strong>$ligne</strong></td>
					</tr>
					";
					
					
					
				}
				
				$table=$table . "</table>";
			
			}
	}else{
		$table="<H3>Total:$source_size</h3>";
	}
	
	$table=RoundedLightGrey($table);
	echo "
	<br><H2>$source_path ($source_size)</H2><br>$table";
	
	
}

function SaveFolderList(){
	$path=$_GET["SaveFolderList"];
	$index=$_GET["index"];
	$hard=new harddrive();
	$hard->edit_path($index,$path);
}
function AddFolderList(){
	$path=$_GET["AddFolderList"];
	$hard=new harddrive();
	$hard->add_path($path);
}
function DeleteFolderList(){
	$hard=new harddrive();
	$hard->delete_path($_GET["DeleteFolderList"]);
}

?>