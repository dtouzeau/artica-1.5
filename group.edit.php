<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	
	if (isset($_GET["NewGroup"])){NewGroup();exit;	}
	if(isset($_GET["GroupLists"])){GroupLists();exit;}
	if(isset($_GET["GroupDelete"])){GroupDelete();exit;}
	if(isset($_GET["GroupEdit"])){GroupEdit();exit;}
	if(isset($_GET["GroupSaveIdentity"])){GroupSaveIdentity();exit;}
	if(isset($_POST["GroupSavePrivileges"])){GroupSavePrivileges();exit;}
	if(isset($_GET["GroupUserAdd"])){GroupUserAdd();exit;}
	if(isset($_GET["GroupUserDelete"])){GroupUserDelete();exit;}
	if(isset($_GET["PageEditGroup"])){PageEditGroup();exit;}
	
function NewGroup(){
	
	
		$date=date('ymdhI');
		$ldap=new clladp();
		$ou=$_GET["NewGroup"];
		$dn="cn=New Group $date,ou=$ou,dc=organizations,$ldap->suffix";
		$update_array["cn"][0]="New Group $date";
		$update_array["gidNumber"][0]=$ldap->_GenerateGUID();
		$update_array["description"][0]="New posix group";
		$update_array["objectClass"][]='posixGroup';
		$update_array["objectClass"][]='ArticaSettings';
		$update_array["objectClass"][]='top';
		if($ldap->ldap_add($dn,$update_array)==false){
			echo  
			"Error: Adding {$update_array["gidNumber"][0]} gid 
			cn=New Group\n".
			$ldap->ldap_last_error;
			exit;
		}
		
		echo "OK";
		
		}
		
		
function GroupTabs($gid=0){
	
	$tabs[0]="{group}&nbsp;{identity}";
	$tabs[1]="{group}&nbsp;{rules}";
	$tabs[2]="{group}&nbsp;{users}";
	$html="\n\t<ul id=tablist>
		<ul'>\n";
	while (list ($num, $ligne) = each ($tabs) ){
		if($num==$_GET["tab"]){$sid="id='tab_current'";}else{$sid=null;}
			$html=$html . "\t\t<li ><a href=\"#\" OnClick=\"javascript:TabGroupEdit('$gid','$num');\" $sid>$ligne</a></li>\n";
		
	}
	$html=$html . "</ul>";
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>" . Paragraphe('folder-group-64.jpg','{group_identity}','{group_identity_text}',"javascript:TabGroupEdit($gid,0)")."</td>
	</tr>	
	<tr>
	<td valign='top' width=1%>" . Paragraphe('folder-security-64.jpg','{privileges}','{privileges_text}',"javascript:TabGroupEdit($gid,1)")."</td>
	</tr>		
	</table>
	";
	
	
	$tpl=new templates();
	return  $tpl->_parse_body($html);
	
}

function GroupUsers($gid){
	$tabs=GroupTabs($gid);
	$gid=$_GET["GroupEdit"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);	
	
	$ou=$hash["ou"];
	$users=$ldap->hash_users_ou($ou);
	if(is_array($users)){
		$USER_LIST="<table>
		<tr class=rowT>
			<td>{domain} {users}</TD>
		</tr>
		<tr class='rowH'>
		<td colspan=2>{group how to add user}</td>
		</tr>
		";
		while (list ($num, $ligne) = each ($users) ){
			if($class="rowA"){$class="rowB";}else{$class="rowA";}
			if($hash["members"][$num]==null){
			$USER_LIST=$USER_LIST . "<tr class=$class>
			<td><a href='#' OnClick=\"javascript:GroupUserAdd($gid,'$num');\">$ligne</td>
			<tr>";
			}
		}
		$USER_LIST=$USER_LIST . "</table>";
	}
	
	
	if(is_array($hash["members"])){
	$USER_GLIST="<table>
		<tr class=rowT>
			<td>{group} {users}</TD>
		</tr>
		<tr class='rowH'>
		<td colspan=2>{group how to del user}</td>
		</tr>
		";

		while (list ($num, $ligne) = each ($hash["members"]) ){
			if($class="rowA"){$class="rowB";}else{$class="rowA";}
			$USER_GLIST=$USER_GLIST . "<tr class=$class>
			<td><a href='#' OnClick=\"javascript:GroupUserDelete($gid,'$num');\">$ligne</td>
			<tr>";
			
		}
	$USER_GLIST=$USER_GLIST . "</table>"	;
	}		
	
	
	
	
	
$html="

	$tabs
	<table style='margin:0px;padding:0px;border:0px;margin-top:5px'>
	<tr>
	<td valign='top' width=1%><img src='img/user-group-90.gif'></td>
	<td width=50% valign='top'>$USER_GLIST</td>
	<td width=50% valign='top'>$USER_LIST</td>
	</tr>
	</table>
	";

$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
	
}
		

function GroupPrivileges($gid){
	$tabs=GroupTabs($gid);
		
	$p=new HtmlPages();
	$pg=$p->PageGroupPrivileges($gid);
$html="

	
	<table style='margin:0px;padding:0px;border:0px;margin-top:5px'>
	<tr>
	<td valign='top' width=1%>
	$tabs
	</td>
	<td valign='top'>
	$pg</td>
	</tr>
	</table>
	
		";
$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
	
}

function PageEditGroup(){
	$_GET["GroupEdit"]=$_GET["PageEditGroup"];
	GroupEdit();
	
}

		
function GroupEdit(){
		$gid=$_GET["GroupEdit"];
	switch ($_GET["tab"]) {
		case 1:return GroupPrivileges($gid);
			break;
		case 2:return GroupUsers($gid);
			break;
		default:
			break;
	}
	

	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	
	
	if(!is_array($hash)){return null;}
	$page=CurrentPageName();
	$tabs=GroupTabs($gid);
	$html="
	<table style='margin:0px;padding:0px;border:0px;margin-top:5px'>
	<td valign='top'>$tabs</td>
	<td valign='top'>
	<form name='ffm1'>
	<FIELDSET style='width:100%'>
		<LEGEND>{$hash["cn"]} {identity}</LEGEND>
		<input type='hidden' name='GroupSaveIdentity'  value='$gid'>
		<table class='Grey'>
			<tr class='rowN'>
			
			<td align='right' nowrap><strong>{group name}:</td>
			<td><input type='text' name='cn' id='cn' value=\"{$hash["cn"]}\" style='width:100%'></td>
			</tr>
			<tr><td colspan=2>&nbsp;</td></tr>
			<tr class='rowN'>
			<td align='right' valign='top'><strong>{description}:</td>
			<td>
				<textarea ' name='description' style='width:100%;heigth:50px;border:1px dotted #CCCCCC;font-family:Courrier New' >{$hash["description"]}</textarea>
			</td>
			</tr>
			
			<tr><td colspan=2>&nbsp;</td></tr>			
			<tr class='rowN'>
			<td colspan=2 align='right' style='padding-right:10px'><input type='button' value='{submit}' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td>
			</tr>						
		</table>
		</form>
			
		
		
	</FIELDSET>
	</td>
	
	";
	
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	
	
}
		
function GroupSaveIdentity(){
	$gid=$_GET["GroupSaveIdentity"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$dn=$hash["dn"];
	$tpl=new templates();
	if(preg_match('#cn=([a-zA-Z0-9\.\-_\(\)\s]+)#',$dn,$reg)){
		$oldcn=$reg[1];
		$error="\noldcn=$oldcn\n";
		
		if($oldcn<>$_GET["cn"]){
			$error=$error . "rename the group to {$_GET["cn"]}\n";
			$ldap->ldap_group_rename($dn,"cn={$_GET["cn"]}");
			$hash=$ldap->GroupDatas($gid);
			$dn=$hash["dn"];
			if($ldap->ldap_last_error<>null){
				echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error.$error);
				return null;
			}else{$error=null;	}
	}
	}
	$error=null;
	
	$update_array["description"]=$_GET["description"];
	$ldap->Ldap_modify($dn,$update_array);

	if($ldap->ldap_last_error<>null){
		echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error.$error);
	}else{echo $tpl->_ENGINE_parse_body('{success}');}

}

function GroupSavePrivileges(){
	
	while (list ($num, $ligne) = each ($_POST) ){
		$datas=$datas."[$num]=" . '"'  . $ligne . '"' . "\n";
	}
	
	$gid=$_POST["GroupSavePrivileges"];
	$update_array["ArticaGroupPrivileges"]=$datas;
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$dn=$hash["dn"];	
	$ldap->Ldap_modify($dn,$update_array);
	$tpl=new templates();
	if($ldap->ldap_last_error==null){$ldap->ldap_last_error="{success}";}
	echo $tpl->_parse_body($ldap->ldap_last_error);	
}
		
function GroupDelete(){
	$gid=$_GET["GroupDelete"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$ldap->ldap_delete($hash["dn"],false);
	$_GET["GroupLists"]=$hash["ou"];
	echo GroupLists();
	}
	
	
function GroupUserAdd(){
	$gid=$_GET["GroupUserAdd"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$updtate_array["memberUid"]=$_GET["userid"];
	$ldap->Ldap_add_mod($hash["dn"],$updtate_array);
	if($ldap->ldap_last_error==null){$ldap->ldap_last_error="{success}";}
	$tpl=new templates();
	echo "success";
	
	exit;
	
}

function GroupUserDelete(){
	$gid=$_GET["GroupUserDelete"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$hash["members"];
	unset($hash["members"][$_GET["userid"]]);
	while (list ($num, $ligne) = each ($hash["members"]) ){
		$update_array["memberUid"][]=$num;
		}
	$ldap->Ldap_modify($hash["dn"],$update_array);
	if($ldap->ldap_last_error==null){$ldap->ldap_last_error="{success}";}
	$tpl=new templates();
	echo "success";
	exit;	
	}
		
		
function GroupLists(){
	$ldap=new clladp();
	$hash=$ldap->hash_groups($_GET["GroupLists"]);
	if(!is_array($hash)){return null;}
	
	$html="<table>
	<tr class=rowT>
	<td colspan=4>{group list}</td>
	</tr>";
	
	while (list ($num, $ligne) = each ($hash) ){
		if($class=='rowA'){$class='rowB';}else{$class="rowA";}
		$html=$html . "<tr class=$class OnMouseOver=\"javascript:this.className='rowH'\" onmouseout=\"javascript:this.className='$class'\">
		<td width=1%'><img src='img/user-group-22.gif'></td>
		<td><a href='#' OnClick=\"javascript:GroupEdit({$ligne["gid"]},0);\"> {$num}</td>
		<td>{$ligne["description"]}</td>
		<td width=1%'><a href='#' OnClick=\"javascript:GroupDelete({$ligne["gid"]});\"><img src='img/x.gif'></a></td>
		</tr>
		";
		
	}
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	
}
	
?>