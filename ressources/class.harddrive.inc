<?php
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.sockets.inc');

class harddrive{
	
	var $main_array;
	
	function harddrive(){
	$this->__LoadFoldersList();
		
		
		
	}
	
	function __LoadFoldersList($verify=true){
		$ldap=new clladp();
		$attrs=array("ArticaFoldersSizeConfig");
		$sr =@ldap_read($ldap->ldap_connection,"cn=artica,$ldap->suffix",'objectClass=ArticaSettings',$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($verify){if(!isset($hash[0][strtolower("ArticaFoldersSizeConfig")]["count"])){$this->_builddefault();}}
			for($i=0;$i<$hash[0][strtolower("ArticaFoldersSizeConfig")]["count"];$i++){
				$this->main_array["folders_list"][]=$hash[0][strtolower("ArticaFoldersSizeConfig")][$i];
				
			}
				
			}
	}
	
function getDiskList(){
	$users=new usersMenus();
	$Disks=$users->disks_size;
	$page=CurrentPageName();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists(dirname(__FILE__)."/usb.scan.inc")){return array("usb.scan.inc no such file"=>false);}
	include_once(dirname(__FILE__)."/usb.scan.inc");	
	
	$added_disks=$sock->GET_INFO('HiddenDisksList');
	$tbl=explode("\n",$added_disks);

	if(is_array($tbl)){
		while (list ($num, $line) = each ($tbl) ){
			if($line==null){continue;}
			$added_array=explode(";",$line);
			$added_disks_array[$added_array[0]]=$added_array[1];
		}
	}	
	
	
	
	if($Disks<>null){
		$tbl=explode(";",$Disks);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$values=explode(",",$val);
				if(is_array($values)){
					$dc=$dc+1;
					$disk=$values[0];
					$size=$values[1];
					$occ=$values[2];
					$disp=$values[3];
					$pourc=$values[4];
					$path=$this->Get_mounted_path($disk,$_GLOBAL["usb_list"]);
					$arrayDisks[$disk]=array("POURC"=>$pourc,"SIZE"=>$size,"MOUNTED"=>$path);
					}
			}
		}
	}
	
	
	if(is_array($_GLOBAL["disks_list"])){
		while (list ($num, $ARRAY) = each ($_GLOBAL["disks_list"]) ){
			$disk=basename($num);
			if($ARRAY["MOUNTED"]<>null){
				$arrayDisks[$disk]=array("POURC"=>"0","SIZE"=>$ARRAY["SIZE"],"MOUNTED"=>$ARRAY["MOUNTED"]);
			}
			
			while (list ($partition_dev, $ARRAY_PARTITIONS) = each ($ARRAY["PARTITIONS"]) ){
				if($ARRAY_PARTITIONS["MOUNTED"]<>null){
					$disk=basename($partition_dev);
					$ARRAY_PARTITIONS["free_size"]=str_replace("%",'',$ARRAY_PARTITIONS["free_size"]);
					$infos=explode(";",$ARRAY_PARTITIONS["free_size"]);
					$arrayDisks[$disk]=array("POURC"=>$infos[3],"SIZE"=>$ARRAY_PARTITIONS["SIZE"],"MOUNTED"=>$ARRAY_PARTITIONS["MOUNTED"]);
					
				}
				
				
			}
			
		}
		
		
	}

	return $arrayDisks;
	
}	

private function Get_mounted_path($dev,$array){
$regex_pattern="#\/dev\/$dev#";
if(is_array($array)){
while (list ($num, $val) = each ($array) ){
		if(preg_match($regex_pattern,$val["PATH"])){
			return $val["mounted"];
			break;
		}
	}	
	
}}

function Folders_interdis(){
	$spool=false;
	if(isset($_GET["no-shares"])){$spool=true;}
	
	$l["/sys"]=true;
	$l["/initrd"]=true;
	$l["/dev"]=true;
	$l["/etc"]=true;
	$l["/boot"]=true;
	$l["/proc"]=true;
	$l["/root"]=true;
	$l["/var/lib"]=true;
	$l["/sbin"]=true;
	$l["/lib"]=true;
	$l["/bin"]=true;
	$l["/usr/libexec"]=true;
	$l["/usr/sbin"]=true;
	$l["/usr/bin"]=true;	
	$l["/usr/include"]=true;	
	$l["/usr/local"]=true;	
	$l["/usr/src"]=true;		
	$l["/usr/usr"]=true;
	$l["/usr/X11R6"]=true;
	$l["/usr/lib"]=true;
	$l["/usr/lib64"]=true;
	$l["/usr/src"]=true;	
	$l["/srv"]=true;
	$l["/var/log"]=true;
	$l["/var/db"]=true;
	$l["/var/lib"]=true;
	$l["/var/lock"]=true;
	$l["/var/mail"]=true;
	$l["/var/milter-greylist"]=true;
	if($spool){
		$l["/var/spool"]=true;
		$l["/var/opt"]=true;
		$l["/usr/share"]=true;
		$l["/opt"]=true;
		$l["/var/cache"]=true;
		$l["/var/local"]=true;
	}
	
	$l["/var/run"]=true;
	$l["/var/tmp"]=true;
	$l["/var/webmin"]=true;
	$l["/lost+found"]=true;
	return $l;
	if(!$l[$folder]){return false;}else{return true;}
	
	
}
	
	
	function edit_path($index,$path){
		$this->main_array["folders_list"][$index]=$path;
		while (list ($num, $ligne) = each ($this->main_array["folders_list"])){	
			$upd["ArticaFoldersSizeConfig"][]=$ligne;
		}
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$ldap->Ldap_modify($dn,$upd);
		$this->__applyconf();
	}
	
	function add_path($path){
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$upd["ArticaFoldersSizeConfig"]=$path;
		$ldap->Ldap_add_mod($dn,$upd);
		$this->__applyconf();
	}
	function delete_path($num){
		$ldap=new clladp();
		$dn="cn=artica,$ldap->suffix";
		$path=$this->main_array["folders_list"][$num];
		$upd["ArticaFoldersSizeConfig"]=$path;
		$ldap->Ldap_del_mod($dn,$upd);
		$this->__applyconf();
	}
	
	function __applyconf(){
		$sock=new sockets();
		$sock->getfile('DeleteFolderSizeMon');
		
	}
	
	
	function _builddefault(){
		$ldap=new clladp();
		$ldap->IFcn_notexists_CreateIt('artica');
		$dn="cn=artica,$ldap->suffix";
		$u[]='/var/log';
		$u[]='/opt/artica';
		$u[]='/var/spool';
		
		 while (list ($num, $ligne) = each ($u) ){
		 	$upd["ArticaFoldersSizeConfig"]=$ligne;
		 	$ldap->Ldap_add_mod($dn,$upd);
		 	
		 }
		$this->__LoadFoldersList(false);
	}
	
	function BuildSizes(){
		$sock=new sockets();
		$datas=$sock->getfile("SystemGetFolderSize");
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		
		
		
		
		if(!is_array($this->main_array["folders_list"])){return null;}
		reset($this->main_array["folders_list"]);
		while (list ($num, $ligne) = each ($this->main_array["folders_list"])){	
			$size=$ini->_params[$ligne]["Size"];
			$total=$total+$size;
			$this->main_array["folders_size"][$ligne]=$ini->_params[$ligne]["Size"];
		}
		reset($this->main_array["folders_list"]);
		$this->main_array["sum"]["total"]=$total;
		
		
	}
	
	
}