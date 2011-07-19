<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsSambaAdministrator) OR (!$users->AsSystemAdministrator)){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["mount-point"])){add_auto_mount();exit;}
if(isset($_GET["mount-list"])){echo mount_list();exit;}
if(isset($_GET["AutoMountDelete"])){mount_delete();exit;}

js();



function js(){
	
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{add_auto_connection}');
	$title_default=$tpl->_ENGINE_parse_body('{automount_center}');
	
	
	$start="{$prefix}LoadPage()";
	if($_GET["computer"]<>null){$start="{$prefix}LoadPageForm();";}
	
	$html="
	
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
		function {$prefix}LoadPageForm(){
			RTMMail(680,'$page?popup=yes&computer={$_GET["computer"]}&type={$_GET["type"]}&src={$_GET["src"]}','$title');
		}
		
		function {$prefix}LoadPage(){
			RTMMail(680,'$page?popup=yes&field={$_GET["field"]}','$title_default');
		}		
		
var x_{$prefix}SaveAutoMountCenter= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}LoadPage();
	}	

	
function {$prefix}Reload(){
	LoadAjax('mount-list','$page?mount-list=yes');
}

var x_{$prefix}AutoMountDelete= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}Reload();
	}	
		
		function SaveAutoMountCenter(){
			var XHR = new XHRConnection();
			XHR.appendData('computer','{$_GET["computer"]}');
			XHR.appendData('type',document.getElementById('type').value);
			XHR.appendData('mount-point',document.getElementById('mount-point').value);
			XHR.appendData('src','{$_GET["src"]}');
			document.getElementById('form_add').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}SaveAutoMountCenter);
		}
		
		function AutoMountDelete(mount){
			var XHR = new XHRConnection();
			XHR.appendData('AutoMountDelete',mount);
			document.getElementById('mount-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}AutoMountDelete);			
		}
		
		function AutoMountSelect(res){
			if(!document.getElementById('{$_GET["field"]}')){
				alert('{$_GET["field"]} is not accessible !');
				return;
			}
			
			document.getElementById('{$_GET["field"]}').value=res;
			RTMMailHide();
		}

		
		
	
$start
";

echo $html;

}


function popup(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);	
	if($_GET["computer"]<>null){
		$form=form_connection();
	}
	
	$browse=Buildicon64('DEF_ICO_BROWSE_COMP');
	$usbbrowse=Buildicon64("DEF_ICO_DEVCONTROL");
	
	$mount_list=mount_list();
	
	$html="<H1>{automount_center}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'>$usbbrowse<br>$browse</td>
	<td valign='top'>
		<div id='form_add'>$form</div>
		<BR>
		<div style='text-align:right;margin-top:-25px;margin-bottom:5px'>". imgtootltip("20-refresh.png",'{refresh}',"{$prefix}Reload()")."</div>
			". RoundedLightWhite("<div id='mount-list' style='width:100%;height:200px;overflow:auto'>$mount_list</div>")."
	</td>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function form_connection(){
	
$type=$_GET["type"];
$src=$_GET["src"];
$computer_id=$_GET["computer"];

$computer=new computers($computer_id);

$mount_point="{$computer->ComputerRealName}_{$type}_".basename($src);
$mount_point=str_replace('.','_',$mount_point);
$mount_point=str_replace('-','_',$mount_point);
$mount_point=str_replace('$','',$mount_point);

if($type=="NFS"){
	$array["nfs3"]="NFSv3";
	$array["nfs4"]="NFSv4";
	$type=Field_array_Hash($array,'type','nfs3');
}

if($type=="SMB"){
	$type="smbfs<input type='hidden' id='type' value='smbfs'>";
	$credentials="<input type='button' OnClick=\"javascript:Loadjs('computer.passwd.php?uid=$computer_id')\" value='{COMPUTER_ACCESS}&nbsp;&raquo;'>";
	
}


$html="
<H3>{add_auto_connection}</H3>
<table style='width:100%' class=table_form>
<tr>
<td class=legend>{servername}:</td>
<td><strong>$computer->ComputerIP</strong></td>
</tr>
<tr>
<td class=legend>{filesystem}:</td>
<td><strong>$type&nbsp;&nbsp; $credentials</strong></td>
</tr>
<tr>
<td class=legend>{source}:</td>
<td><strong>$src</strong></td>
</tr>
<tr>
<td class=legend>{mount_point}:</td>
<td>". Field_text('mount-point',$mount_point,'width:220px')."</td>
</tr>
<tr><td colspan=2 align='right'><p class=caption>{add_auto_connection_explain}</p></td></tr>
		<tr>
			<td colspan=2 align=right><hr>
				<input type='button' OnClick=\"javascript:SaveAutoMountCenter();\" value='{add}&nbsp;&raquo;'>	
			</td>
		</tr>
</table>
";

$tpl=new templates();

return $tpl->_ENGINE_parse_body($html);

	
}

function add_auto_mount(){
$type=$_GET["type"];
$mount_point=$_GET["mount-point"];
$computer=$_GET["computer"];
$src=$_GET["src"];

if($type=='smbfs'){
	$cmp=new computers($computer);
	$ini=new Bs_IniHandler();
	$ini->loadString($cmp->ComputerCryptedInfos);
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];
	if($username<>null){
		$options=",username=$username,password=$password";
	}
	$pattern="-fstype=smbfs$options ://$cmp->ComputerIP/$src";
	
}


if(preg_match("#nfs[0-9]+#",$type)){
	if($type=='nfs3'){
		$pattern="-fstype=$type $cmp->ComputerIP:$src";
	}
	if($type=='nfs4'){
		$pattern="-fstype=$type $cmp->ComputerIP:/";
	}	
}

$ldap=new clladp();
$autofs=new autofs();
$dn="cn=$mount_point,ou=auto.automounts,ou=mounts,$ldap->suffix";
if(!$ldap->ExistsDN($dn)){
	$upd["ObjectClass"][]='top';
	$upd["ObjectClass"][]='automount';
	$upd["cn"][]=$mount_point;
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->ldap_add($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}
	
}else{
		$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
			echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
			return false;
		}	
	}
	


}

function mount_delete(){
	$ldap=new clladp();
	$dn="cn={$_GET["AutoMountDelete"]},ou=auto.automounts,ou=mounts,$ldap->suffix";
	if(!$ldap->ldap_delete($dn)){echo $ldap->ldap_last_error;}	
}

function mount_list(){
	
	$field=$_GET["field"];
	$autofs=new autofs();
	$hash=$autofs->automounts_Browse();
	
	$title="/automounts/...";
	if($field<>null){
		$title="{SELECT_RESSOURCE}";
	}
	$html="
	<H3>$title</H3>
	<table style='width:100%'>";
	while (list ($mount, $array) = each ($hash) ){
		$js=null;
		$delete=imgtootltip('ed_delete.gif','{delete}',"AutoMountDelete('$mount')");
		if($field<>null){
			$delete=imgtootltip('add-18.gif','{SELECT_RESSOURCE}',"AutoMountSelect('$mount')");
			$js="AutoMountSelect('$mount')";
		}
		$html=$html . "<tr " . CellRollOver($js).">
		<td valign='top' width=1%><img src='img/fw_bold.gif'></td>
		<td valign='top' width=1%><code style='font-weight:bold'>$mount</code></td>
		<td valign='top' width=1%><code style='font-weight:bold'>{$array["FS"]}</code></td>
		<td valign='top' width=99%><code style='font-weight:bold;font-size:9px'>{$array["SRC"]}</code></td>
		<td valign='top' width=1%>$delete</td>
		</tr>
		<tr>
			<td colspan=5 align='right'>
				<table>
					<tr>
						<td>". texttooltip('{backup}','{automount_add_backup}',"Loadjs('cyrus.backup.php?add-automount=$mount')")."&nbsp;|&nbsp;
						". texttooltip('{restore}','{automount_restore_from}',"Loadjs('cyrus.backup.restore.php?automount=$mount')")."&nbsp;|&nbsp;
						
						</td>
					</tr>
				</table>
				<hr>
			</td>
		</tr>
			";
		}
	$html=$html . "</table>";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html,'automount.php');	

}


?>