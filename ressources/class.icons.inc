<?php
include_once(dirname(__FILE__).'/class.images.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.roundcube.inc');
include_once(dirname(__FILE__).'/class.user.inc');
class deficons{
	
var $array_icons=array();
var $categories=array();
var $array_icons_users=array();
var $categories_users=array();
var $UsersMenus;

function deficons($uid=null){
	
		if(isset($GLOBALS["__CLASS-ICONS"])){
			foreach (get_object_vars($GLOBALS["__CLASS-ICONS"]) as $key => $value){
  				$this->$key = $value;
				}
				return null;
			}	
	
	
	$users=new usersMenus();
	$this->UsersMenus=$users;
	$this->network();
	$this->mailsecu();
	$this->mail();
	$this->relay();
	$this->samba();
	$this->system();
	$this->organizations();
	$this->evenements();
	$this->User($uid);
	$this->UserAdmin($uid);
	
	if(count($this->categories)==0){
		if(!is_array($GLOBALS["deficons"]["categories"])){
			while (list ($num, $ligne) = each ($this->array_icons) ){
				$cl=$ligne["CLASS"];
				$this->categories[$cl]="{{$cl}}";
				$GLOBALS["deficons"]["categories"][$cl]="{{$cl}}";
			}			
		}else{
			$this->categories=$GLOBALS["deficons"]["categories"];
		}
	}
	reset($this->array_icons);
	
	if(count($this->categories_users)==0){
			if(!is_array($GLOBALS["deficons"]["categories_users"])){
				while (list ($num, $ligne) = each ($this->array_icons_users) ){
					$cl=$ligne["CLASS"];
					$this->categories_users[$cl]="{{$cl}}";
					$GLOBALS["deficons"]["categories_users"][$cl]="{{$cl}}";
				}			
			}else{
				$this->categories_users=$GLOBALS["deficons"]["categories_users"];
			}
		}	

	reset($this->array_icons_users);
	$GLOBALS["__CLASS-ICONS"]=$this;	
	
	
	
}
function BuildIconUsers($def){
	$tpl=new templates();
	$title="{{$this->array_icons_users[$def]["TITLE"]}}";
	if($this->array_icons_users[$def]["TITLE_NO_TRANSLATE"]==true){
		$title=$this->array_icons_users[$def]["TITLE"];
	}
	
	$p=Paragraphe($this->array_icons_users[$def]["IMG"],$title,"{{$this->array_icons_users[$def]["TEXT"]}}","javascript:".$this->array_icons_users[$def]["JS"],
	"{{$this->array_icons_users[$def]["TEXT"]}}");
	return $tpl->_ENGINE_parse_body($p,$this->array_icons_users[$def]["LANG"]);
}

function BuildIcon($def){
	$tpl=new templates();
	$title="{{$this->array_icons[$def]["TITLE"]}}";
	if($this->array_icons[$def]["TITLE_NO_TRANSLATE"]==true){
		$title=$this->array_icons[$def]["TITLE"];
	}
	
	$p=Paragraphe($this->array_icons[$def]["IMG"],$title,"{{$this->array_icons[$def]["TEXT"]}}","javascript:".$this->array_icons[$def]["JS"],"{{$this->array_icons[$def]["TEXT"]}}");
	return $tpl->_ENGINE_parse_body($p,$this->array_icons[$def]["LANG"]);
}
function BuildIconRowUser($def,$pos=0){
$tpl=new templates();
	$title="{{$this->array_icons_users[$def]["TITLE"]}}";
	if($this->array_icons_users[$def]["TITLE_NO_TRANSLATE"]==true){
		$title=$this->array_icons_users[$def]["TITLE"];
	}
	$original_image="img/{$this->array_icons_users[$def]["IMG"]}";
	$targeted_image="ressources/logs/22-{$this->array_icons_users[$def]["IMG"]}";
	$img=new images($original_image);
		if(!is_file($targeted_image)){
			$path=$img->createthumb($original_image,$targeted_image,22,22,false,true,false);
		}
		
	$html="<tr ". CellRollOver().">
	<td valign='top' width=1%><img src='$targeted_image'></td>
	<td valign='top'><strong>$title</strong></td>
	<td valign='top' width=1%>" . imgtootltip("down-22.png","{down}","WidgetUDown('$pos')")."</td>
	<td valign='top' width=1%>" . imgtootltip("up-22.png","{up}","WidgetUUp('$pos')")."</td>
	<td valign='top' width=1%>&nbsp;&nbsp;</td>
	<td valign='top' width=1%>" . imgtootltip("22-delete.png","{delete}","DelWidgetUIcon('$def')")."</td>
	
	</tr>
	";
		
	return $tpl->_ENGINE_parse_body($html,$this->array_icons_users[$def]["LANG"]);
}


function BuildIconRow($def,$pos=0){
$tpl=new templates();
	$title="{{$this->array_icons[$def]["TITLE"]}}";
	if($this->array_icons[$def]["TITLE_NO_TRANSLATE"]==true){
		$title=$this->array_icons[$def]["TITLE"];
	}
	$original_image="img/{$this->array_icons[$def]["IMG"]}";
	$targeted_image="ressources/logs/22-{$this->array_icons[$def]["IMG"]}";
	$img=new images($original_image);
		if(!is_file($targeted_image)){
			$path=$img->createthumb($original_image,$targeted_image,22,22,false,true,false);
		}
		
	$html="<tr ". CellRollOver().">
	<td valign='top' width=1%><img src='$targeted_image'></td>
	<td valign='top'><strong>$title</strong></td>
	<td valign='top' width=1%>" . imgtootltip("down-22.png","{down}","WidgetDown('$pos')")."</td>
	<td valign='top' width=1%>" . imgtootltip("up-22.png","{up}","WidgetUp('$pos')")."</td>
	<td valign='top' width=1%>&nbsp;&nbsp;</td>
	<td valign='top' width=1%>" . imgtootltip("22-delete.png","{delete}","DelWidgetIcon('$def')")."</td>
	
	</tr>
	";
		
	return $tpl->_ENGINE_parse_body($html,$this->array_icons[$def]["LANG"]);
}

public function Build32_widgets_users(){
	$target_path_img=dirname(__FILE__).'/logs';
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($this->array_icons_users) ){
		$img=new images("img/".$ligne["IMG"]);
		if(!is_file("ressources/logs/32-{$ligne["IMG"]}")){
			$path=$img->createthumb("img/{$ligne["IMG"]}","ressources/logs/32-{$ligne["IMG"]}",32,32,false,true,false);
		}
		
		$title="{$ligne["TITLE"]}";
		if($ligne["TITLE_NO_TRANSLATE"]==true){
			$title="noacco:{$ligne["TITLE"]}";
		}
			
		
		
		$array[$ligne["CLASS"]][$num]=$tpl->_ENGINE_parse_body(Paragraphe32($title,"{$ligne["TEXT"]}","AddWidgetUIcon('$num')","ressources/logs/32-{$ligne["IMG"]}"),$ligne["LANG"]);
		
		
	}
	return $array;
	
}



function Build32_widgets(){
	$target_path_img=dirname(__FILE__).'/logs';
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($this->array_icons) ){
		$img=new images("img/".$ligne["IMG"]);
		if(!is_file("ressources/logs/32-{$ligne["IMG"]}")){
			$path=$img->createthumb("img/{$ligne["IMG"]}","ressources/logs/32-{$ligne["IMG"]}",32,32,false,true,false);
		}
		
		$title="{$ligne["TITLE"]}";
		if($ligne["TITLE_NO_TRANSLATE"]==true){
			$title="noacco:{$ligne["TITLE"]}";
		}
			
		
		
		$array[$ligne["CLASS"]][$num]=$tpl->_ENGINE_parse_body(Paragraphe32($title,"{$ligne["TEXT"]}","AddWidgetIcon('$num')","ressources/logs/32-{$ligne["IMG"]}"),$ligne["LANG"]);
		
		
	}
	return $array;
	
}

private function UserAdmin($uid=null){
	$users=new usersMenus();
	$user=new user($uid);
	$ou=$user->ou;
	$sock=new sockets();
	if($ou==null){return null;}
	
	
$array["DEF_ICO_USER_ORG_SMTP"]=array(
			"IMG"=>"folder-transport-64.png",
			 "JS"=>"Loadjs('domains.edit.domains.php?js=yes&ou=$ou')",
			 "TITLE"=>"localdomains",
			 "TEXT"=>"localdomains_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	
			
$array["DEF_ICO_USER_ORG_QUAR"]=array(
			"IMG"=>"folder-quarantine-extrainfos-64.png",
			 "JS"=>"Loadjs(\"domains.quarantine.php?js=$ou\")",
			 "TITLE"=>"quarantine_manager",
			 "TEXT"=>"quarantine_manager_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);
if(!$users->ARTICA_META_ENABLED){			
	$array["DEF_ICO_USER_ORG_ADDUSER"]=array(
			"IMG"=>"folder-useradd-64.png",
			 "JS"=>"Loadjs('domains.add.user.php?ou=$ou')",
			 "TITLE"=>"create_user",
			 "TEXT"=>"create_user_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);
}else{
	if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
		$array["DEF_ICO_USER_ORG_ADDUSER"]=array(
			"IMG"=>"folder-useradd-64.png",
			 "JS"=>"Loadjs('domains.add.user.php?ou=$ou')",
			 "TITLE"=>"create_user",
			 "TEXT"=>"create_user_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	
	}
}	

$array["DEF_ICO_USER_ORG_GROUP"]=array(
			"IMG"=>"folder-group-64.png",
			 "JS"=>"Loadjs('domains.edit.group.php?ou=$ou&js=yes')",
			 "TITLE"=>"manage_groups",
			 "TEXT"=>"manage_groups_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	

$array["DEF_ICO_USER_ORG_AD"]=array(
			"IMG"=>"folder-import-ad-64.png",
			 "JS"=>"Loadjs('domains.ad.import.php?ou=$ou')",
			 "TITLE"=>"ad_import",
			 "TEXT"=>"ad_import_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	


$array["DEF_ICO_USER_ORG_STATS"]=array(
			"IMG"=>"64-milterspy.png",
			 "JS"=>"Loadjs('statistics.ou.php?ou=$ou')",
			 "TITLE"=>"statistics",
			 "TEXT"=>"statistics_ou_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	

$array["DEF_ICO_USER_ORG_JOOMLA"]=array(
			"IMG"=>"64.joomla.png",
			 "JS"=>"Loadjs('domains.joomla.php?ou=$ou')",
			 "TITLE"=>"APP_JOOMLA",
			 "TEXT"=>"manage_your_joomla_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);	

$array["DEF_ICO_USER_ORG_SUGAR"]=array(
			"IMG"=>"64.joomla.png",
			 "JS"=>"Loadjs('domains.sugarcrm.php?ou=$ou')",
			 "TITLE"=>"APP_SUGARCRM",
			 "TEXT"=>"manage_your_sugarcrm_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"domains.manage.org.index.php"
			);			
			
$array["DEF_ICO_EVENTS_RTMMAIL"]=array(
		"IMG"=>"64-rtmm.png",
		"JS"=>"Loadjs('RTMMail.php')",
		"TITLE"=>"RTMMail",
		"TEXT"=>"RTMMail_TEXT",
		"CLASS"=>"menu_user",
		"LANG"=>"RTMMailConfig.php");
	
$array["DEF_ICO_USER_ORG_ROUNDCUBE"]=array(
		"IMG"=>"64-roundcube.png",
		"JS"=>"Loadjs('domains.roundcube.php?ou=$ou')",
		"TITLE"=>"APP_ROUNDCUBE",
		"TEXT"=>"manage_your_roundcube_text",
		"CLASS"=>"menu_user",
		"LANG"=>"domains.manage.org.index.php");

$array["DEF_ICO_USER_ORG_SETTINGS"]=array(
		"IMG"=>"64-roundcube.png",
		"JS"=>"Loadjs('domains.organization-settings.php?ou=$ou')",
		"TITLE"=>"ORG_SETTINGS",
		"TEXT"=>"ORG_SETTINGS_TEXT",
		"CLASS"=>"menu_user",
		"LANG"=>"domains.manage.org.index.php");

$array["DEF_ICO_USER_ORG_WBL"]=array(
		"IMG"=>"bg_chess-64.png",
		"JS"=>"Loadjs('domains.white.list.robots.php?ou=$ou')",
		"TITLE"=>"enable_artica_wbl_robots",
		"TEXT"=>"enable_artica_wbl_robots_icon",
		"CLASS"=>"menu_user",
		"LANG"=>"domains.manage.org.index.php");

		$array["DEF_ICO_SENDTOALL"]=array(
			 "IMG"=>"64-sendmail.png",
			 "JS"=>"Loadjs('domains.sendmail.php?ou=$ou')",
			 "TITLE"=>"send_to_all_users",
			 "TEXT"=>"send_to_all_users_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"domains.sendmail.php");
					
	$this->array_icons_users=$this->array_icons_users+$array;		
}

private function User($uid=null){
	
	$array["DEF_ICO_USER_ACCOUNT"]=array(
			"IMG"=>"identity-64.png",
			 "JS"=>"Loadjs('users.account.php?js=yes')",
			 "TITLE"=>"menu_account_text",
			 "TEXT"=>"menu_account_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);
			
	$array["DEF_ICO_USER_BACKUP"]=array(
			"IMG"=>"folder-64-artica-backup.png",
			 "JS"=>"MyHref('users.backup.php')",
			 "TITLE"=>"backup",
			 "TEXT"=>"menu_backup_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);	

	$array["DEF_ICO_USER_ADDRESSBOOK"]=array(
			"IMG"=>"64-addressbook.png",
			 "JS"=>"Loadjs('users.addressbook.index.php')",
			 "TITLE"=>"ADDRESSBOOK",
			 "TEXT"=>"ADDRESSBOOK_TEXT",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);		

	$array["DEF_ICO_USER_MYADDRESSBOOK"]=array(
			"IMG"=>"64-your-address-book.png",
			 "JS"=>"Loadjs('my.addressbook.php')",
			 "TITLE"=>"YOUR_ADDRESSBOOK",
			 "TEXT"=>"YOUR_ADDRESSBOOK_TEXT",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);

	$array["DEF_ICO_USER_OPENVPN"]=array(
			"IMG"=>"64-openvpn.png",
			 "JS"=>"Loadjs('users.openvpn.index.php?&uid=$uid')",
			 "TITLE"=>"APP_OPENVPN",
			 "TEXT"=>"menu_openvpn_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);
			
	$r=new roundcube();
	$uri=$r->roundCubeArray["user_link"];
	$array["DEF_ICO_USER_ROUNDCUBE"]=array(
			"IMG"=>"folder-usermailbox-64.png",
			 "JS"=>"s_PopUpFull('$uri',800,600,'webmail');",
			 "TITLE"=>"webmail",
			 "TEXT"=>"menu_webmail_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);

	$array["DEF_ICO_USER_ROUNDCUBE"]=array(
			"IMG"=>"folder-64-fetchmail.png",
			 "JS"=>"Loadjs('wizard.fetchmail.newbee.php?script=yes&uid=$uid');",
			 "TITLE"=>"fetchmail",
			 "TEXT"=>"menu_fetchmail_text",
			 "CLASS"=>"menu_user",
			 "LANG"=>"menus.builder.php"
			);
$this->array_icons_users=array();
$this->array_icons_users=$this->array_icons_users+$array;

}

function samba(){
	$users=$this->UsersMenus;;
	$array=array();
	if(posix_getuid()==0){!$users->AsSambaAdministrator=true;}
	if(!$users->AsSambaAdministrator){return null;}
	
			$array["DEF_ICO_SAMBA"]=array(
			"IMG"=>"64-samba.png",
			 "JS"=>"javascript:Loadjs('samba.index.php?script=smbpop')",
			 "TITLE"=>"APP_SAMBA",
			 "TEXT"=>"APP_SAMBA_TEXT",
			 "CLASS"=>"FILE_SHARE",
			 "LANG"=>"fileshares.index.php"
			);
			
			$array["DEF_ICO_SHARE"]=array(
			"IMG"=>"64-samba.png",
			 "JS"=>"javascript:Loadjs('SambaBrowse.php')",
			 "TITLE"=>"SHARE_FOLDER",
			 "TEXT"=>"SHARE_FOLDER_TEXT",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);

			$array["DEF_ICO_SHARE_USB"]=array(
			"IMG"=>"64-usbdisk.png",
			 "JS"=>"javascript:Loadjs('samba.usb.php?js=yes')",
			 "TITLE"=>"usb_share",
			 "TEXT"=>"usb_share_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);

			$array["DEF_ICO_NTPOL"]=array(
			"IMG"=>"64-user-policies.png",
			 "JS"=>"javascript:Loadjs('NTconfig.pol.php')",
			 "TITLE"=>"NTconfig",
			 "TEXT"=>"NTconfig_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);		

			$array["DEF_ICO_NTPOL_HOWTO"]=array(
			"IMG"=>"64-howto.png",
			 "JS"=>"javascript:s_PopUpFull('http://www.artica.fr/index.php/samba-file-sharing/53-domains-a-administration/246-define-windows-clients-policies-trough-your-pdc-',800,800,'')",
			 "TITLE"=>"NTconfig_howto_title",
			 "TEXT"=>"NTconfig_howto_title_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);

			$array["DEF_ICO_SAMBA_BEHAVIOR"]=array(
			"IMG"=>"64-server-ask.png",
			 "JS"=>"javascript:Loadjs('samba.index.php?script=yes&behavior=yes')",
			 "TITLE"=>"windows_network_neighborhood",
			 "TEXT"=>"windows_network_neighborhood_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"samba.index.php"
			);

			$array["DEF_ICO_SMB_ROOT_PASSWD"]=array(
			"IMG"=>"members-priv-64.png",
			 "JS"=>"javascript:Loadjs('samba.index.php?script=yes&behavior-admin=yes')",
			 "TITLE"=>"domain_admin",
			 "TEXT"=>"domain_admin_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"samba.index.php"
			);	

			$array["DEF_ICO_SAMBA_SYNCHRO"]=array(
			"IMG"=>"server-syncronize-64.png",
			 "JS"=>"javascript:Loadjs('samba.index.php?syncronize=yes')",
			 "TITLE"=>"samba_synchronize",
			 "TEXT"=>"samba_synchronize_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"samba.index.php"
			);	

		$array["DEF_ICO_DEVCONTROL"]=array(
			"IMG"=>"usb-logo-64.png",
			 "JS"=>"Loadjs('usb.browse.php')",
			 "TITLE"=>"APP_DEVICE_CONTROL",
			 "TEXT"=>"APP_DEVICE_CONTROL_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"usb.browse.php"
			);

		$array["DEF_ICO_ROAMINGP"]=array(
			"IMG"=>"logon-profiles-64.png",
			 "JS"=>"Loadjs('samba.roaming.php')",
			 "TITLE"=>"roaming_profiles",
			 "TEXT"=>"roaming_profiles_text",
			 "CLASS"=>"FILE_SHARE",
			 "LANG"=>"fileshares.index.php"
			);				
			
			
			

			
			
	
		$cups=Paragraphe('64-printer-grey.png',"{APP_CUPS}",'{feature_not_installed}');

//$audit=Paragraphe('folder-64-spamassassin.png','{samba_audit}','{samba_audit_text}',"new:smb-audit/index.php",null,210,null,0,false);
	
		
		if($users->CUPS_INSTALLED){
			
			$array["DEF_ICO_CUPS"]=array(
			"IMG"=>"64-printer.png",
			 "JS"=>"javascript:Loadjs('cups.index.php')",
			 "TITLE"=>"APP_CUPS",
			 "TEXT"=>"APP_CUPS_TEXT",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);	
			
		}
		
		$this->array_icons=$this->array_icons+$array;
		
	
	
}

function squid(){
$users=$this->UsersMenus;;
	if(posix_getuid()==0){!$users->AsSquidAdministrator=true;}
	if(!$users->AsSquidAdministrator){return null;}
	if(!$users->SQUID_INSTALLED){return null;}
	
$array["DEF_ICO_SQUID"]=array(
			"IMG"=>"64-squid.png",
			 "JS"=>"Loadjs('squid.newbee.php')",
			 "TITLE"=>"web_proxy",
			 "TEXT"=>"web_proxy_text",
			 "CLASS"=>"web_proxy",
			 "LANG"=>"squid.index.php"
			);	

		
}




function system(){
	$users=$this->UsersMenus;;
	if(posix_getuid()==0){!$users->AsArticaAdministrator=true;}
	if(!$users->AsArticaAdministrator){return null;}
	
		$array["DEF_ICO_MYSQL"]=array(
			"IMG"=>"folder-64-backup.png",
			 "JS"=>"Loadjs('mysql.index.php')",
			 "TITLE"=>"mysql_settings",
			 "TEXT"=>"mysql_settings_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"artica.settings.php"
			);
			
		$array["DEF_ICO_ZABBIX"]=array(
			"IMG"=>"zabbix_med.gif",
			 "JS"=>"Loadjs('zabbix.php')",
			 "TITLE"=>"APP_ZABIX_SERVER",
			 "TEXT"=>"APP_ZABIX_SERVER_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"artica.settings.php"
			);			
			
		$array["DEF_ICO_LDAP"]=array(
			"IMG"=>"ldap-performances-64.png",
			 "JS"=>"Loadjs('artica.settings.php?js-ldap-interface=yes')",
			 "TITLE"=>"ldap_performances",
			 "TEXT"=>"ldap_performances_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"artica.settings.php"
			);			
			
			
		$array["DEF_ICON_PHILESIGHT"]=array("IMG"=>"philesight-64.png",
			 "JS"=>"Loadjs('philesight.php')",
			 "TITLE"=>"APP_PHILESIGHT",
			 "TEXT"=>"APP_PHILESIGHT_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>""
			);				
			
		
		
			
		$array["DEF_ICO_DEVCONTROL"]=array(
			"IMG"=>"usb-logo-64.png",
			 "JS"=>"Loadjs('usb.browse.php')",
			 "TITLE"=>"APP_DEVICE_CONTROL",
			 "TEXT"=>"APP_DEVICE_CONTROL_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"usb.browse.php"
			);			
			
			
		if($users->NFS_SERVER_INSTALLED){
		 $array["DEF_ICO_SHARE_NFS"]=array(
			"IMG"=>"nfs-64.png",
			 "JS"=>"javascript:Loadjs('SambaBrowse.php')",
			 "TITLE"=>"SHARE_FOLDER",
			 "TEXT"=>"SHARE_FOLDER_TEXT",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);
			
		/* $array["DEF_ICO_SAN_CLUSTER"]=array(
			"IMG"=>"san-64.png",
			 "JS"=>"javascript:Loadjs('san.cluster.php')",
			 "TITLE"=>"SAN_CLUSTER",
			 "TEXT"=>"SAN_CLUSTER_TEXT",
			 "CLASS"=>"SYSTEM",
			  "LANG"=>"san.cluster.php"
			);			
			
			*/
		}
		
		if($users->autofs_installed){
			$array["DEF_ICO_AUTOFS_NFS"]=array(
			"IMG"=>"database-network-64.png",
			 "JS"=>"javascript:Loadjs('nfs-client.php')",
			 "TITLE"=>"NFS_CLIENT",
			 "TEXT"=>"NFS_CLIENT_TEXT",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"fileshares.index.php"
			);
			
			$array["DEF_ICO_AUTOFS_CENTER"]=array(
			"IMG"=>"magneto-64.png",
			 "JS"=>"javascript:Loadjs('autofs.php')",
			 "TITLE"=>"automount_center",
			 "TEXT"=>"automount_center_text",
			 "CLASS"=>"FILE_SHARE",
			"LANG"=>"automount.php"	);

			$array["DEF_ICO_AUTOFS_RESTART"]=array(
			"IMG"=>"service-restart-64.png",
			 "JS"=>"javascript:Loadjs('automount.restart.php')",
			 "TITLE"=>"restart_autofs",
			 "TEXT"=>"restart_autofs_text",
			 "CLASS"=>"SYSTEM",
				"LANG"=>"automount.php"				
			);
	}
			$array["DEF_ICO_DISKS"]=array(
			"IMG"=>"64-hd.png",
			 "JS"=>"Loadjs('system.internal.disks.php')",
			 "TITLE"=>"internal_hard_drives",
			 "TEXT"=>"internal_hard_drives_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"configure.server.php"
			);
		
			
		$array["DEF_ICO_MYSQL_PWD"]=array(
			"IMG"=>"change-mysql-64.png",
			 "JS"=>"Loadjs('mysql.password.php')",
			 "TITLE"=>"MYSQL_PASSWORD_USER",
			 "TEXT"=>"MYSQL_PASSWORD_USER_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"mysql.index.php"
			);
			
		$array["DEF_ICO_MYSQL_USER"]=array(
			"IMG"=>"mysql-user-settings.png",
			 "JS"=>"Loadjs('mysql.index.php?mysql-settings-popup=yes')",
			 "TITLE"=>"MYSQL_USER_NET",
			 "TEXT"=>"MYSQL_USER_NET_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"mysql.index.php"
			);			

		$array["DEF_ICO_PHP_EVENTS"]=array(
			"IMG"=>"php-logs-64.png",
			 "JS"=>"Loadjs('php.logs.php')",
			 "TITLE"=>"PHP_EVENTS",
			 "TEXT"=>"PHP_EVENTS_TEXT",
			 "CLASS"=>"events",
			 "LANG"=>""
			);	

	
			
		$array["DEF_ICO_SSL_KEY"]=array(
			"IMG"=>"64-ssl-key.png",
			 "JS"=>"Loadjs('postfix.tls.php?js-certificate=yes')",
			 "TITLE"=>"ssl_certificate",
			 "TEXT"=>"ssl_certificate_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"configure.server.php"
			);			

		
			
		$array["DEF_ICO_GB_SETS"]=array(
			"IMG"=>"64-settings.png",
			 "JS"=>"Loadjs('artica.settings.php?js=yes')",
			 "TITLE"=>"artica_settings",
			 "TEXT"=>"artica_settings_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"admin.tab.php"
			);	
			
		$array["DEF_ICO_UPDAR"]=array(
			"IMG"=>"folder-64-artica-update.png",
			 "JS"=>"Loadjs('artica.update.php?js=yes')",
			 "TITLE"=>"artica_autoupdate",
			 "TEXT"=>"artica_autoupdate_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"system.index.php"
			);	

		$array["DEF_ICO_CONTROLCENTER"]=array(
			"IMG"=>"add-remove-64.png",
			 "JS"=>"Loadjs('setup.index.php?js=yes')",
			 "TITLE"=>"application_setup",
			 "TEXT"=>"application_setup_txt",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"system.index.php"
			);				
			
		$array["DEF_ICO_TROUBLESHOOTS"]=array(
			"IMG"=>"64-troubleshoots.png",
			 "JS"=>"Loadjs('index.troubleshoot.php')",
			 "TITLE"=>"troubleshoot",
			 "TEXT"=>"troubleshoot_explain",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"configure.server.php"
			);	
			
		$array["DEF_ICO_APT"]=array(
			"IMG"=>"64-apt.png",
			 "JS"=>"Loadjs('artica.repositories.php')",
			 "TITLE"=>"repository_manager",
			 "TEXT"=>"repository_manager_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"system.index.php"
			);
			
		$array["DEF_ICO_PROC_INFOS"]=array(
			"IMG"=>"processor-64.png",
			 "JS"=>"Loadjs('system.nic.infos.php?info=proc')",
			 "TITLE"=>"proc_hardware_infos",
			 "TEXT"=>"proc_hardware_infos_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"system.nic.infos.php"
			 
			);			
			
	if($users->dar_installed){
		$array["DEF_ICO_DARLIST"]=array(
			"IMG"=>"folder-64-books.png",
			 "JS"=>"Loadjs('dar.browse.php')",
			 "TITLE"=>"display_dar_collection",
			 "TEXT"=>"display_dar_collection_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"dar.index.php"
			);
			
		$array["DEF_ICO_RUNBACKUP"]=array(
			"IMG"=>"64-run-backup.png",
			 "JS"=>"Loadjs('dar.index.php?perform-backup=yes')",
			 "TITLE"=>"run_backup",
			 "TEXT"=>"run_backup_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"dar.index.php"
			);	
		}
		
		
	if($users->NTPD_INSTALLED){
		$array["DEF_ICO_NTP"]=array(
			"IMG"=>"folder-64-ntp.png",
			 "JS"=>"Loadjs('ntpd.index.php')",
			 "TITLE"=>"APP_NTPD",
			 "TEXT"=>"APP_NTPD_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"system.index.php"
			);
		

			}

		$array["DEF_ICO_CLAMAV"]=array(
			"IMG"=>"clamav-64.png",
			 "JS"=>"Loadjs('clamav.index.php')",
			 "TITLE"=>"clamav_av",
			 "TEXT"=>"clamav_av_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"configure.server.php"
			);		
		
			
		$array["DEF_ICO_FRESHCLAM_SETTINGS"]=array(			
			"IMG"=>"64-update-settings.png",
			 "JS"=>"Loadjs('clamav.index.php?freshclam-js=yes')",
			 "TITLE"=>"APP_FRESHCLAM",
			 "TEXT"=>"product_update_settings_text",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"clamav.index.php"
			);
			
			
			$array["DEF_ICO_ARTICA_CRON_SCHEDULE"]=array(
			"IMG"=>"cron-64.png",
			 "JS"=>"Loadjs('artica.performances.php?cron-js=yes')",
			 "TITLE"=>"ARTICA_PROCESS_SCHEDULE",
			 "TEXT"=>"ARTICA_PROCESS_SCHEDULE_TEXT",
			 "CLASS"=>"SYSTEM",
			 "LANG"=>"artica.performances.php"			
			
			
			
			);

		
		

			
			

			
	$this->array_icons=$this->array_icons+$array;
}


function evenements(){
$users=$this->UsersMenus;;
$array=array();
	if(posix_getuid()==0){!$users->AsSystemAdministrator=true;}

if(!$users->AsSystemAdministrator){return $array["DEF_ICO_EVENTS_RTMMAIL"]=array(
					"IMG"=>"64-rtmm.png",
					 "JS"=>"Loadjs('RTMMail.php')",
					 "TITLE"=>"RTMMail",
					 "TEXT"=>"RTMMail_TEXT",
					 "CLASS"=>"events",
					 "LANG"=>"RTMMailConfig.php"
					);}


if($users->POSTFIX_INSTALLED){			
		$array["DEF_ICO_EVENTS_RTMMAIL"]=array(
					"IMG"=>"64-rtmm.png",
					 "JS"=>"Loadjs('RTMMail.php')",
					 "TITLE"=>"RTMMail",
					 "TEXT"=>"RTMMail_TEXT",
					 "CLASS"=>"events",
					 "LANG"=>"RTMMailConfig.php"
					);
					
		$array["DEF_ICO_EVENTS_SMTP_DAILY_STATS"]=array(
					"IMG"=>"statistics-network-64.png",
					 "JS"=>"Loadjs('smtp.daily.statistics.php')",
					 "TITLE"=>"monthly_statistics",
					 "TEXT"=>"monthly_statistics_text",
					 "CLASS"=>"events",
					 );
					 
		$array["DEF_ICO_EVENTS_POSTFIX"]=array(
					"IMG"=>"64-mailevents.png",
					 "JS"=>"s_PopUp(\"postfix.events.php?pop=true\",450,400)",
					 "TITLE"=>"postfix_events",
					 "TEXT"=>"logs_viewer_text",
					 "CLASS"=>"events",
					 "LANG"=>"postfix.index.php"
					);	
					
		$array["DEF_ICO_EVENTS_MGREYLITS_DUMP"]=array(
					"IMG"=>"folder-rules2-64-info.png",
					 "JS"=>"Loadjs('milter.greylist.index.php?dumpfile-js=yes')",
					 "TITLE"=>"MILTERGREYLIST_STATUSDUMP",
					 "TEXT"=>"MILTERGREYLIST_STATUSDUMP_TEXT",
					 "CLASS"=>"events",
					 "LANG"=>"milter.greylist.index.php"
					);	
					
		$array["DEF_ICO_EVENTS_MAILGRAPH"]=array(
					"IMG"=>"statistics-64.png",
					 "JS"=>"Loadjs('mailgraph.php')",
					 "TITLE"=>"APP_MAILGRAPH_ICON",
					 "TEXT"=>"APP_MAILGRAPH_ICON_TEXT",
					 "CLASS"=>"events",
					 "LANG"=>"postfix.index.php"
					);						

	
					
					
									 

		if($users->cyrus_imapd_installed){
			
			$array["DEF_ICO_EVENTS_IMAPCON"]=array(
					"IMG"=>"database-stats-64.png",
					 "JS"=>"Loadjs('imap.daily.statistics.php')",
					 "TITLE"=>"daily_mailboxes_connections_statistics",
					 "TEXT"=>"daily_mailboxes_connections_statistics_text",
					 "CLASS"=>"events",
					 
					);	
					 
		}
					 
			
		}

		
	
	
	



$array["DEF_ICO_EVENTS_ARTICA"]=array(
			"IMG"=>"64-jumelles.png",
			 "JS"=>"Loadjs('artica.events.php')",
			 "TITLE"=>"artica_events",
			 "TEXT"=>"artica_events_text",
			 "CLASS"=>"events",
			 "LANG"=>"configure.server.php"
			);
			
$array["DEF_ICO_EVENTS_RTMM"]=array(
			"IMG"=>"64-jumelles.png",
			 "JS"=>"Loadjs('RTMMailConfig.php?events-js=yes')",
			 "TITLE"=>"artica_events_status",
			 "TEXT"=>"artica_events_status_text",
			 "CLASS"=>"events",
			);			



$array["DEF_ICO_EVENTS_BACKUP"]=array(
			"IMG"=>"64-jumelles.png",
			 "JS"=>"Loadjs('dar.index.php?js-logs=yes')",
			 "TITLE"=>"BACKUP",
			 "TEXT"=>"view_logs",
			 "CLASS"=>"events",
			 "LANG"=>"dar.index.php"
			);
			



			
			
if(is_array($array)){			
	$this->array_icons=$this->array_icons+$array;
}	
	
}


function organizations(){
$users=$this->UsersMenus;;	
$sock=new sockets();
if(!$users->AsOrgAdmin){return null;}
if(!$users->ARTICA_META_ENABLED){
$array["DEF_ICO_USERADD"]=array(
			"IMG"=>"folder-useradd-64.png",
			 "JS"=>"Loadjs('create-user.php')",
			 "TITLE"=>"add_user",
			 "TEXT"=>"add user explain",
			 "CLASS"=>"ORG",
			 "LANG"=>"configure.server.php"
			);
}else{
	if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
		$array["DEF_ICO_USERADD"]=array(
			"IMG"=>"folder-useradd-64.png",
			 "JS"=>"Loadjs('create-user.php')",
			 "TITLE"=>"add_user",
			 "TEXT"=>"add user explain",
			 "CLASS"=>"ORG",
			 "LANG"=>"configure.server.php"
			);		
	}
}

$array["DEF_ICO_ORGS"]=array(
			"IMG"=>"64-see-orgs.png",
			 "JS"=>"Loadjs('domains.index.php?js=yes')",
			 "TITLE"=>"your_organizations",
			 "TEXT"=>"organizations_text",
			 "CLASS"=>"ORG",
			 "LANG"=>"admin.tabs.php"
			);

$ldap=new clladp();
$hash=$ldap->hash_get_ou(true);
if(is_array($hash)){
	while (list ($num, $ligne) = each ($hash) ){
		if($num==null){continue;}
		$array["DEF_ICO_ORGNAME_{$num}"]=array(
			"IMG"=>"folder-org-64.png",
			 "JS"=>"Loadjs('domains.manage.org.index.php?js=yes&ou=$num')",
			 "TITLE"=>"$num",
			 "TITLE_NO_TRANSLATE"=>true,
			 "TEXT"=>"manage_organisations_text",
			 "CLASS"=>"ORG",
			 "LANG"=>"domains.index.php"
			);
		}
	
}
		
$this->array_icons=$this->array_icons+$array;
	
}


function network(){
	$users=$this->UsersMenus;;
	if(posix_getuid()==0){$users->AsPostfixAdministrator=true;}
	if(!$users->AsSystemAdministrator){return null;}
	if($users->BIND9_INSTALLED){
		$array["DEF_ICO_BIND9"]=array(
			"IMG"=>"folder-64-bind9.png",
			 "JS"=>"Loadjs('index.bind9.php?script=yes')",
			 "TITLE"=>"APP_BIND9",
			 "TEXT"=>"APP_BIND9_TEXT",
			 "CLASS"=>"NETWORK"
			);
		}
	
		$array["DEF_ICO_NIC_INFOS"]=array(
			"IMG"=>"64-win-nic-infos.png",
			 "JS"=>"Loadjs('system.nic.infos.php')",
			 "TITLE"=>"network_hardware_infos",
			 "TEXT"=>"network_hardware_infos_text",
			 "CLASS"=>"NETWORK",
			 "LANG"=>"system.nic.infos.php"
			 
			);	
			
			
		$array["DEF_ICO_PDNS"]=array(
			"IMG"=>"dns-64.png",
			 "JS"=>"Loadjs('pdns.php')",
			 "TITLE"=>"APP_PDNS",
			 "TEXT"=>"APP_PDNS_TEXT",
			 "CLASS"=>"NETWORK",
			 "LANG"=>"system.nic.infos.php"
			);
			
		$array["DEF_ICO_ETC_HOSTS"]=array(
			"IMG"=>"host-file-64.png",
			 "JS"=>"Loadjs('system-etc-hosts.php')",
			 "TITLE"=>"etc_hosts",
			 "TEXT"=>"etc_hosts_text",
			 "CLASS"=>"NETWORK"
			);			

		$array["DEF_ICO_MYSQL_CLUSTER"]=array(
			"IMG"=>"64-cluster.png",
			 "JS"=>"Loadjs('mysql.cluster.php')",
			 "TITLE"=>"MYSQL_CLUSTER",
			 "TEXT"=>"MYSQL_CLUSTER_TEXT",
			 "CLASS"=>"NETWORK",
			);	
			
		$array["DEF_ICO_MYSQL_BROWSE"]=array(
			"IMG"=>"mysql-browse-database.png",
			 "JS"=>"Loadjs('mysql.browse.php')",
			 "TITLE"=>"browse_mysql_server",
			 "TEXT"=>"browse_mysql_server_text",
			 "CLASS"=>"NETWORK",
			 "LANG"=>"mysql.index.php"
			);				
	
	if($users->OPENVPN_INSTALLED){
			$array["DEF_ICO_OPENVPN"]=array(
			"IMG"=>"64-openvpn.png",
			 "JS"=>"Loadjs('index.openvpn.php')",
			 "TITLE"=>"APP_OPENVPN",
			 "TEXT"=>"APP_OPENVPN_TEXT",
			 "CLASS"=>"NETWORK"
			);
	}
	
	if($users->dhcp_installed){
		$array["DEF_ICO_DHCP"]=array(
			"IMG"=>"64-dhcp.png",
			 "JS"=>"Loadjs('index.gateway.php?index_dhcp=yes')",
			 "TITLE"=>"APP_DHCP",
			 "TEXT"=>"APP_DHCP_TEXT",
			 "CLASS"=>"NETWORK"
			);
			
	$array["DEF_ICO_DHCP_CONFIG"]=array(
			"IMG"=>"64-server-dhcp-script.png",
			 "JS"=>"YahooWin3(700,'index.gateway.php?show-script=yes','{APP_DHCP_MAIN_CONF}')",
			 "TITLE"=>"APP_DHCP_MAIN_CONF",
			 "TEXT"=>"APP_DHCP_MAIN_CONF_TEXT",
			 "CLASS"=>"NETWORK"
			);
			
	$array["DEF_ICO_DHCP_ROUTES"]=array(
			"IMG"=>"computer-routes-64.png",
			 "JS"=>"Loadjs('dhcpd-routes.php')",
			 "TITLE"=>"APP_DHCP_ROUTES_CONF",
			 "TEXT"=>"APP_DHCP_ROUTES_CONF_TEXT",
			 "CLASS"=>"NETWORK"
			);			
			
	$array["DEF_ICO_DHCP_EVENTS"]=array(
			"IMG"=>"64-dhcpd-logs.png",
			 "JS"=>"Loadjs('dhcpd.events.php')",
			 "TITLE"=>"APP_DHCP_EVENTS",
			 "TEXT"=>"APP_DHCP_EVENTS_TEXT",
			 "CLASS"=>"NETWORK","LANG"=>"index.gateway.php"
			);
			
	$array["DEF_ICO_OPENVPN_ARTICA_CLIENTS"]=array(
			"IMG"=>"2monitors-stars-64.png",
			 "JS"=>"Loadjs('openvpn.artica.php')",
			 "TITLE"=>"ARTICA_VPN_CONNECT",
			 "TEXT"=>"ARTICA_VPN_CONNECT_TEXT",
			 "CLASS"=>"NETWORK","LANG"=>"openvpn.artica.php"
			);			
	
			
			
	}
	
$array["DEF_ICO_ADDCOMP"]=array(
			"IMG"=>"64-add-computer.png",
			 "JS"=>"YahooUser(670,'domains.edit.user.php?userid=newcomputer$&ajaxmode=yes','New computer');",
			 "TITLE"=>"ADD_COMPUTER",
			 "TEXT"=>"ADD_COMPUTER_TEXT",
			 "CLASS"=>"NETWORK"
			);	
			
		$array["DEF_ICO_REMOTE_STORAGE"]=array(			
			"IMG"=>"software-back-64.png",
			 "JS"=>"Loadjs('storage.center.php')",
			 "TITLE"=>"APP_STORAGE_CENTER",
			 "TEXT"=>"APP_STORAGE_CENTER_TEXT",
			 "CLASS"=>"NETWORK",
			 "LANG"=>""
			);	

		$array["DEF_ICO_OCS"]=array(			
			"IMG"=>"64-ocs.png",
			 "JS"=>"Loadjs('ocs.ng.php')",
			 "TITLE"=>"APP_OCSI",
			 "TEXT"=>"APP_OCSI_TEXT",
			 "CLASS"=>"NETWORK",
			 "LANG"=>""
			);				

			
			
			
$array["DEF_ICO_BROWSE_COMP"]=array(
			"IMG"=>"64-win-nic-browse.png",
			 "JS"=>"Loadjs('computer-browse.php')",
			 "TITLE"=>"browse_computers",
			 "TEXT"=>"browse_computers_text",
			 "CLASS"=>"NETWORK",
			  "LANG"=>"configure.server.php"
			);				
			
		
	

	
$array=$array+array(
	"DEF_ICO_GATEWAY"=>array(
			"IMG"=>"relayhost.png",
			 "JS"=>"Loadjs('index.gateway.php?script=yes')",
			 "TITLE"=>"APP_ARTICA_GAYTEWAY",
			 "TEXT"=>"APP_ARTICA_GAYTEWAY_TEXT",
			 "CLASS"=>"NETWORK"
			),	
			
	"DEF_ICO_GATEWAY"=>array(
			"IMG"=>"64-asgateway.png",
			 "JS"=>"javascript:YahooWin(350,'index.gateway.php?AsGatewayForm=yes')",
			 "TITLE"=>"ARTICA_AS_GATEWAY",
			 "TEXT"=>"ARTICA_AS_GATEWAY_TEXT",
			 "CLASS"=>"NETWORK",
			"LANG"=>"index.gateway.php"
			),			
			
					

	"DEF_ICO_IFCFG"=>array(
			"IMG"=>"64-win-nic.png",
			 "JS"=>"Loadjs('system.nic.config.php?js=yes')",
			 "TITLE"=>"net_settings",
			 "TEXT"=>"net_settings_text",
			 "CLASS"=>"NETWORK"
			),

		"DEF_ICO_COMPUTER_ADD"=>array(
			"IMG"=>"64-add-computer.png",
			 "JS"=>"YahooUser(670,'domains.edit.user.php?userid=newcomputer$&ajaxmode=yes','New computer');",
			 "TITLE"=>"ADD_COMPUTER",
			 "TEXT"=>"ADD_COMPUTER_TEXT",
			 "CLASS"=>"NETWORK"
			),				

	"DEF_ICO_EXTERNAL_PORTS"=>array(
			"IMG"=>"64-firewall-search.png",
			 "JS"=>"Loadjs('external-ports.php')",
			 "TITLE"=>"EXTERNAL_PORTS",
			 "TEXT"=>"EXTERNAL_PORTS_TEXT",
			 "CLASS"=>"NETWORK",
			),

	);

	$this->array_icons=$this->array_icons+$array;
	
}


function mail(){
	$users=$this->UsersMenus;;
	if(posix_getuid()==0){!$users->AsMailBoxAdministrator=true;}
	if(!$users->AsMailBoxAdministrator){return null;}
	if($users->cyrus_imapd_installed){
	$array["DEF_ICO_CYRUS"]=array(
			"IMG"=>"bg-cyrus-64.png",
			 "JS"=>"Loadjs('cyrus.index.php')",
			 "TITLE"=>"APP_CYRUS",
			 "TEXT"=>"mange_cyrus_mailbox",
			 "CLASS"=>"mailbox",
			 "LANG"=>"postfix.index.php"
			);
			
	$array["DEF_ICO_CYRUS_PASS"]=array(
			"IMG"=>"cyrus-password-64.png",
			 "JS"=>"Loadjs('cyrus.password.php')",
			 "TITLE"=>"cyrus password",
			 "TEXT"=>"change_cyrus_password",
			 "CLASS"=>"mailbox",
			 "LANG"=>"cyrus.index.php"
			);	
			
	$array["DEF_ICO_CYRUS_AV"]=array(
			"IMG"=>"virus-server-64.png",
			 "JS"=>"Loadjs('cyrus.antivirus.php')",
			 "TITLE"=>"cyrus_scan_antivirus",
			 "TEXT"=>"cyrus_scan_antivirus_text",
			 "CLASS"=>"mailbox",
			 "LANG"=>"cyrus.index.php"
			);				

	$array["DEF_ICO_ALTERMIME"]=array(
			"IMG"=>"icon_settings-64.png",
			 "JS"=>"Loadjs('amavis.index.php?altermime-js=yes')",
			 "TITLE"=>"disclaimer",
			 "TEXT"=>"disclaimer_text",
			 "CLASS"=>"mailbox",
			 "LANG"=>"amavis.index.php"
			);			
		
			
			
			
			
	$array["DEF_ICO_MULTIDOMAIN"]=array(
			"IMG"=>"folder-org-64.png",
			 "JS"=>"Loadjs('postfix.index.php?script=multidomains')",
			 "TITLE"=>"multidomains",
			 "TEXT"=>"multidomains_text",
			 "CLASS"=>"mailbox",
			 "LANG"=>"postfix.index.php"
			);
			
		if($users->cyrus_murder_installed){
		$array["DEF_ICO_IMAP_MURDER"]=array(
			 "IMG"=>"64-murder.png",
			 "JS"=>"Loadjs('cyrus.murder.php')",
			 "TITLE"=>"APP_CYRUS_MURDER",
			 "TEXT"=>"APP_CYRUS_MURDER_ICON_TEXT",
			 "CLASS"=>"mailbox",
			 
			);
		}
		
		
		if($users->autofs_installed){
			$array["DEF_ICO_IMAP_BACKUP"]=array(
			 "IMG"=>"disk-backup-64.png",
			 "JS"=>"Loadjs('cyrus.backup.php')",
			 "TITLE"=>"APP_CYRUS_BACKUP",
			 "TEXT"=>"APP_CYRUS_BACKUP_TEXT",
			 "CLASS"=>"mailbox",
			);
		}
			
	}

	
	$array["DEF_ICO_FETCHMAIL"]=array(
			"IMG"=>"fetchmail-rule-64.png",
			 "JS"=>"Loadjs('fetchmail.index.php?ajax=yes')",
			 "TITLE"=>"APP_FETCHMAIL_TINY",
			 "TEXT"=>"APP_FETCHMAIL_TEXT",
			 "CLASS"=>"mailbox",
			 "LANG"=>"postfix.index.php"
			);
	
	


			
	if($users->roundcube_installed){
		$array["DEF_ICO_ROUNDCUBE"]=array(
			"IMG"=>"64-roundcube.png",
			 "JS"=>"Loadjs('roundcube.index.php?script=yes')",
			 "TITLE"=>"APP_ROUNDCUBE",
			 "TEXT"=>"APP_ROUNDCUBE_TEXT",
			 "CLASS"=>"mailbox",
			 "LANG"=>"postfix.index.php"
			);
						
	}
	if($users->cyrus_imapd_installed){
	$array["DEF_ICO_RESTOREMBX"]=array(
			 "IMG"=>"64-restore-mailbox.png",
			 "JS"=>"Loadjs('dar.restorembx.php')",
			 "TITLE"=>"restore_mailbox",
			 "TEXT"=>"restore_mailbox_text",
			 "CLASS"=>"mailbox",
			 "LANG"=>"dar.restorembx.php"
	);}

	if($users->POSTFIX_INSTALLED){
		$array["DEF_ICO_SENDTOALL"]=array(
			 "IMG"=>"64-sendmail.png",
			 "JS"=>"Loadjs('domains.sendmail.php')",
			 "TITLE"=>"send_to_all_users",
			 "TEXT"=>"send_to_all_users_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"domains.sendmail.php");
		
		$array["DEF_ICO_POSTFIX_NETWORK"]=array(
			 "IMG"=>"folder-network-64.png",
			 "JS"=>"Loadjs('postfix.network.php?ajax=yes')",
			 "TITLE"=>"postfix_network",
			 "TEXT"=>"postfix_network_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");

		$array["DEF_ICO_POSTFIX_TRANSPORT"]=array(
			 "IMG"=>"folder-transport-64.png",
			 "JS"=>"Loadjs('postfix.routing.table.php?js=yes')",
			 "TITLE"=>"transport_table",
			 "TEXT"=>"transport_table_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");	

		$array["DEF_ICO_POSTFIX_APPLY"]=array(
			 "IMG"=>"system-64.png",
			 "JS"=>"Default_ApplyConfigPostfix()",
			 "TITLE"=>"apply config",
			 "TEXT"=>"APPLY_SETTINGS_POSTFIX",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");		
		
		$array["DEF_ICO_POSTFIX_QUEUE"]=array(
			 "IMG"=>"folder-queue-64.png",
			 "JS"=>"Loadjs('postfix.queue.monitoring.php?js=yes')",
			 "TITLE"=>"queue_monitoring",
			 "TEXT"=>"queue_monitoring_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");	

		$array["DEF_ICO_POSTFIX_RELAYHOST"]=array(
			 "IMG"=>"64-relayhost.png",
			 "JS"=>"relayhost()",
			 "TITLE"=>"relayhost_title",
			 "TEXT"=>"relayhost_title_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");

		$array["DEF_ICO_POSTFIX_RELAYHOSTSSL"]=array(
			 "IMG"=>"folder-64-routing-secure.png",
			 "JS"=>"Loadjs('postfix.relayssl.php?js=yes')",
			 "TITLE"=>"relayhost_ssl",
			 "TEXT"=>"relayhost_ssl_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");
		
		$array["DEF_ICO_POSTFIX_TLS"]=array(
			 "IMG"=>"64-tls-server.png",
			 "JS"=>"Loadjs('postfix.tls.php')",
			 "TITLE"=>"tls_title",
			 "TEXT"=>"DEF_ICO_POSTFIX_TLS_TEXT",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");		


		$array["DEF_ICO_SEND_TO_ORANGE"]=array(
			 "IMG"=>"64-relayhost-orange.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=orangefr')",
			 "TITLE"=>"relayhost_orange",
			 "TEXT"=>"relayhost_orange_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");

		$array["DEF_ICO_SEND_TO_OLEANE"]=array(
			 "IMG"=>"64-oleane.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=oleane')",
			 "TITLE"=>"relayhost_oleane",
			 "TEXT"=>"relayhost_oleane_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");
		
		$array["DEF_ICO_SEND_TO_ONEONE"]=array(
			 "IMG"=>"1and1.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=1and1')",
			 "TITLE"=>"relayhost_1and1",
			 "TEXT"=>"relayhost_1and1_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");

		$array["DEF_ICO_SEND_TO_WANADOO"]=array(
			 "IMG"=>"64-logo-wanadoo.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=wanadoo')",
			 "TITLE"=>"relayhost_wanadoo",
			 "TEXT"=>"relayhost_wanadoo_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");

		$array["DEF_ICO_SEND_TO_FREE"]=array(
			 "IMG"=>"free-fai-logo.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=free')",
			 "TITLE"=>"relayhost_free",
			 "TEXT"=>"relayhost_free_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");	

		$array["DEF_ICO_SEND_TO_LAPOSTE"]=array(
			 "IMG"=>"laposte-net.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=laposte')",
			 "TITLE"=>"relayhost_laposte",
			 "TEXT"=>"relayhost_laposte_text",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");			

		$array["DEF_ICO_POSTFIX_NOTIFS"]=array(
			 "IMG"=>"recup-remote-mail.png",
			 "JS"=>"Loadjs('postfix.notifs.php?hostname=master')",
			 "TITLE"=>"POSTFIX_SMTP_NOTIFICATIONS",
			 "TEXT"=>"POSTFIX_SMTP_NOTIFICATIONS_TEXT",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");
		
		
		if($users->cyrus_imapd_installed){
			$array["DEF_ICO_CYR_CLUSTER"]=array(
			 "IMG"=>"64-cluster.png",
			 "JS"=>"Loadjs('cyrus.clusters.php')",
			 "TITLE"=>"CYRUS_CLUSTER",
			 "TEXT"=>"CYRUS_CLUSTER_TEXT",
			 "CLASS"=>"mailbox",
			 "LANG"=>"cyrus.clusters.php");
			
			$array["DEF_ICO_CYR_REBUILD"]=array(
			 "IMG"=>"system-64.png",
			 "JS"=>"Loadjs('cyrus.rebuild.php')",
			 "TITLE"=>"REBUILD_CYRUS",
			 "TEXT"=>"REBUILD_CYRUS_TEXT",
			 "CLASS"=>"mailbox",
			 "LANG"=>"cyrus.rebuild.php");			
		}
			
			
		

		if($users->MAILMAN_INSTALLED){
		$array["DEF_ICO_POSTFIX_MAILMAN"]=array(
			 "IMG"=>"folder-group2-64.png",
			 "JS"=>"Loadjs('mailman.php?script=yes')",
			 "TITLE"=>"APP_MAILMAN",
			 "TEXT"=>"manage_distribution_lists",
			 "CLASS"=>"MAIL",
			 "LANG"=>"postfix.index.php");	
			
		}
	}
	
	$this->array_icons=$this->array_icons+$array;
	
}


function mailsecu(){
	$users=$this->UsersMenus;;
	if(posix_getuid()==0){!$users->AsPostfixAdministrator=true;}
	
		if(!$users->AsPostfixAdministrator){
			return null;
		}
		if(!$users->POSTFIX_INSTALLED){
			return null;
		}
	
	if($users->AMAVIS_INSTALLED){
		$array["DEF_ICO_AMAVIS"]=array(
			"IMG"=>"64-amavis.png",
			 "JS"=>"Loadjs('amavis.index.php?ajax=yes')",
			 "TITLE"=>"APP_AMAVISD_NEW",
			 "TEXT"=>"APP_AMAVISD_NEW_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);
			
		$array["DEF_ICO_AMAVIS_RESTART"]=array(
			"IMG"=>"64-amavis.png",
			 "JS"=>"Loadjs('amavis.restart.php')",
			 "TITLE"=>"apply config",
			 "TEXT"=>"APPLY_SETTINGS_AMAVIS",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"amavis.index.php"
			);			
			
			
			
	}
	
	if($users->MILTERGREYLIST_INSTALLED){
		$array["DEF_ICO_MILTER_GREYLIST"]=array(
			"IMG"=>"64-milter-greylist.png",
			 "JS"=>"Loadjs('milter.greylist.index.php?js=yes')",
			 "TITLE"=>"APP_MILTERGREYLIST",
			 "TEXT"=>"APP_MILTERGREYLIST_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);
		}
		
	if($users->kas_installed){
		$array["DEF_ICO_KAS3"]=array(
			"IMG"=>"folder-caterpillar.png",
			 "JS"=>'Loadjs("kas.group.rules.php?ajax=yes")',
			 "TITLE"=>"APP_KAS3",
			 "TEXT"=>"KAS3_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);
		
	}else{
	
	}
	
	if($users->POSTFIX_INSTALLED){
		$array["DEF_ICO_POSTFIX_TLS"]=array(
			"IMG"=>"64-tls-server.png",
			 "JS"=>'Loadjs("postfix.tls.php")',
			 "TITLE"=>"tls_title",
			 "TEXT"=>"DEF_ICO_POSTFIX_TLS_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);
			
		$array["DEF_ICO_POSTFIX_WHITELIST"]=array(
			"IMG"=>"64-bind9-add-zone.png",
			 "JS"=>'Loadjs("postfix.whitelist.php")',
			 "TITLE"=>"PostfixAutoBlockDenyAddWhiteList",
			 "TEXT"=>"PostfixAutoBlockDenyAddWhiteList_explain",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);		

		$array["DEF_ICO_POSTFIX_REGEX"]=array(
			"IMG"=>"bg_regex-64.png",
			 "JS"=>'Loadjs("domains.postfix.multi.regex.php?ou=master&hostname=master")',
			 "TITLE"=>"global_smtp_rules",
			 "TEXT"=>"global_smtp_rules_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);				
			
			$global_smtp_rules=Paragraphe('64-administrative-tools.png','{global_smtp_rules}','{global_smtp_rules_text}',"javascript:Loadjs('smtp.rules.php?ajax=yes')",null,210,null,0,true);
			
		
			
	}
	
		$array["DEF_ICO_ASSP"]=array(
			"IMG"=>"assp-64.png",
			 "JS"=>'Loadjs("assp.php")',
			 "TITLE"=>"APP_ASSP",
			 "TEXT"=>"APP_ASSP_ICON_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);	
	

	
	
	$array2=array(	
	"DEF_ICO_MAIL_POLICYDW"=>array(
			"IMG"=>"64-cop-settings.png",
			 "JS"=>"Loadjs('policyd-weight.php')",
			 "TITLE"=>"APP_POLICYD_WEIGHT",
			 "TEXT"=>"APP_POLICYD_WEIGHT_ICON_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),					



	"DEF_ICO_ACTIVE_MAIL"=>array(
			"IMG"=>"64-folder-install.png",
			 "JS"=>"Loadjs('postfix.index.php?script=antispam')",
			 "TITLE"=>"AS_ACTIVATE",
			 "TEXT"=>"AS_ACTIVATE_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),

	"DEF_ICO_BLOCK_DOMAIN"=>array(
			"IMG"=>"64-folder-install.png",
			 "JS"=>"Loadjs('postfix.index.php?script=deny_domain')",
			 "TITLE"=>"BLOCK_DOMAIN",
			 "TEXT"=>"BLOCK_DOMAIN_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_BLOCK_MILTERBEHAVIOR"=>array(
			"IMG"=>"64-milter-behavior.png",
			 "JS"=>"Loadjs('postfix.index.php?script=milterbehavior')",
			 "TITLE"=>"plugins_behavior",
			 "TEXT"=>"plugins_behavior_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),	

			
		
	"DEF_ICO_MAIL_IPABLES"=>array(
			"IMG"=>"64-bann-server-auto.png",
			 "JS"=>"Loadjs('postfix.iptables.php')",
			 "TITLE"=>"postfix_autoblock",
			 "TEXT"=>"postfix_autoblock_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_MAIL_POLICYDWEIGHT"=>array(
			"IMG"=>"64-cop-settings.png",
			 "JS"=>"Loadjs('policyd-weight.php')",
			 "TITLE"=>"APP_POLICYD_WEIGHT",
			 "TEXT"=>"APP_POLICYD_WEIGHT_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),
			
	"DEF_ICO_MAIL_WBL"=>array(
			"IMG"=>"bg_chess-64.png",
			 "JS"=>"Loadjs('whitelists.admin.php?js=yes')",
			 "TITLE"=>"global_whitelist",
			 "TEXT"=>"global_whitelist_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),			

	"DEF_ICO_MAIL_MGLIST"=>array(
			"IMG"=>"64-milter-greylist.png",
			 "JS"=>"Loadjs('milter.greylist.index.php?js=yes')",
			 "TITLE"=>"APP_MILTERGREYLIST",
			 "TEXT"=>"APP_MILTERGREYLIST_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_MAIL_BLOCKDOM"=>array(
			"IMG"=>"routing-domain-deny.png",
			 "JS"=>"Loadjs('postfix.index.php?script=deny_domain')",
			 "TITLE"=>"BLOCK_DOMAIN",
			 "TEXT"=>"BLOCK_DOMAIN_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),				

		


	"DEF_ICO_MAIL_AUTOCOMPRESS"=>array(
			"IMG"=>"64-winzip.png",
			 "JS"=>"Loadjs('auto-compress.php?script=winzip')",
			 "TITLE"=>"auto-compress",
			 "TEXT"=>"auto-compress_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			),				

			
	"DEF_ICO_MAIL_WHITELISTS"=>array(
			"IMG"=>"bg_chess-64.png",
			 "JS"=>"Loadjs('whitelists.admin.php?js=yes')",
			 "TITLE"=>"global_whitelist",
			 "TEXT"=>"global_whitelist_text",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			));
			
	if($users->POMMO_INSTALLED){
			$array2["DEF_ICO_POMMO"]=array(
			"IMG"=>"64-pommo.png",
			 "JS"=>"Loadjs('pommo.index.php?pommo-js=yes')",
			 "TITLE"=>"APP_POMMO",
			 "TEXT"=>"APP_POMMO_TEXT",
			 "CLASS"=>"MAIL_SECU",
			 "LANG"=>"postfix.index.php"
			);
	}

	if(is_array($array)){
		$array=$array+$array2;
	}else{
		$array=$array2;
	}
	if(is_array($array)){		
		$this->array_icons=$this->array_icons+$array;
	}
}

function relay(){
$array=array(	
	
 
	"DEF_ICO_POSTFIX"=>array(
			"IMG"=>"64-mail-server.png",
			 "JS"=>"Loadjs('postfix.index.php')",
			 "TITLE"=>"APP_POSTFIX",
			 "TEXT"=>"APP_POSTFIX",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	

	"DEF_ICO_POSTFIX_NETWORK"=>array(
			"IMG"=>"folder-network-64.png",
			 "JS"=>"Loadjs('postfix.network.php?ajax=yes')",
			 "TITLE"=>"postfix_network",
			 "TEXT"=>"postfix_network_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	
	"DEF_ICO_POSTFIX_TRANSPORT"=>array(
			"IMG"=>"folder-transport-64.png",
			 "JS"=>"Loadjs('postfix.routing.table.php?js=yes')",
			 "TITLE"=>"transport_table",
			 "TEXT"=>"transport_table_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_POSTFIX_QUEUE"=>array(
			"IMG"=>"folder-queue-64.png",
			 "JS"=>"Loadjs('postfix.queue.monitoring.php?js=yes')",
			 "TITLE"=>"queue_management",
			 "TEXT"=>"queue_management_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_POSTFIX_SSL"=>array(
			"IMG"=>"folder-64-routing-secure.png",
			 "JS"=>"Loadjs('postfix.relayssl.php?js=yes')",
			 "TITLE"=>"relayhost_ssl",
			 "TEXT"=>"relayhost_ssl_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	

	"DEF_ICO_POSTFIX_ORANGE"=>array(
			"IMG"=>"64-relayhost-orange.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=orangefr')",
			 "TITLE"=>"relayhost_orange",
			 "TEXT"=>"relayhost_orange_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	
			
	"DEF_ICO_POSTFIX_OLEANE"=>array(
			"IMG"=>"64-oleane.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=oleane')",
			 "TITLE"=>"relayhost_oleane",
			 "TEXT"=>"relayhost_oleane_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),	

	"DEF_ICO_POSTFIX_ONEANDONE"=>array(
			"IMG"=>"1and1.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=1and1')",
			 "TITLE"=>"relayhost_1and1",
			 "TEXT"=>"relayhost_1and1_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),

	"DEF_ICO_POSTFIX_WANADOO"=>array(
			"IMG"=>"64-logo-wanadoo.png",
			 "JS"=>"Loadjs('postfix.index.php?script=orangefr&isp=wanadoo')",
			 "TITLE"=>"relayhost_wanadoo",
			 "TEXT"=>"relayhost_wanadoo_text",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),				
	"DEF_ICO_POSTFIX_APPLY"=>array(
			"IMG"=>"system-64.png",
			 "JS"=>"Default_ApplyConfigPostfix()",
			 "TITLE"=>"apply config",
			 "TEXT"=>"APPLY_SETTINGS_POSTFIX",
			 "CLASS"=>"MTA",
			 "LANG"=>"postfix.index.php"
			),			

);
		if(is_array($array)){$this->array_icons=$this->array_icons+$array;}			
	
}



}

	







?>