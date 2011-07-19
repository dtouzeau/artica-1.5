<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.rsync.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.lvm.org.inc');
include_once(dirname(__FILE__).'/ressources/class.safebox.inc');


$GLOBALS["ADDLOG"]="/var/log/artica-postfix/safebox.{$argv[2]}.debug";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
@unlink($GLOBALS["ADDLOG"]);
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


if($argv[1]=='--sync'){SyncSafeBoxes();die();}
if($argv[1]=='--init'){SafeBoxUSer($argv[2]);die();}
if($argv[1]=='--umount'){SafeBoxUmount($argv[2]);die();}
if($argv[1]=='--open'){crypt_open($argv[2]);die();}
if($argv[1]=='--fsck'){fsck($argv[2]);die();}


function crypt_open($uid){
	$unix=new unix();
	$safe=new safebox($uid);
	$mapper="/dev/mapper/$uid";	
	_CryptOpen($safe->crypted_filepath,$safe->CryptedHomeSize);
}


function SyncSafeBoxes(){
	$ldap=new clladp();
	$pattern="(&(objectclass=UserArticaClass)(CryptedHome=TRUE))";
	$dn="dc=organizations,$ldap->suffix";
	$filters=array("uid","CryptedHomePassword","homeDirectory");
	$hash=$ldap->Ldap_search($dn,$pattern,$filters);
	if($hash["count"]==0){return;}
	writelogs("{$hash["count"]} user(s) with safe box",__FUNCTION__,__FILE__,__LINE__);
	
	for($i=0;$i<$hash["count"];$i++){
		$cryptedhomepassword=$hash[$i]["cryptedhomepassword"][0];
		if($cryptedhomepassword==null){continue;}
		$uid=$hash[$i]["uid"][0];
		$homedirectory=$hash[$i]["homedirectory"][0];
		$dn=$hash[$i]["dn"];
		SafeBoxMount($uid,$dn,$homedirectory,$cryptedhomepassword,$ldap->suffix);
		}
}

function fsck($uid){
	if(!SafeBoxUmount($uid)){writelogs("Unable to umount $uid",__FUNCTION__,__FILE__,__LINE__);return ;}
	$unix=new unix();
	$safe=new safebox($uid);
	if($GLOBALS["CRYPT_PASSWORD"]==null){
		if($safe->CryptedHomePassword<>null){$GLOBALS["CRYPT_PASSWORD"]=$safe->CryptedHomePassword;}
	}else{
		writelogs("Unable to get password for $uid",__FUNCTION__,__FILE__,__LINE__);return ;
	}
	
	
	if($uid==null){writelogs("No uid set",__FUNCTION__,__FILE__,__LINE__);return ;}
	$mapper="/dev/mapper/$uid";
	$SafeBox=$safe->crypted_filepath;
	
	if(!_ConnectLoop($SafeBox)){
		writelogs("Failed",__FUNCTION__,__FILE__,__LINE__);
	}
	
	if(!_CryptOpen($SafeBox,$GLOBALS["CRYPT_PASSWORD"])){
		writelogs("Failed to open $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	}
	$cmd=$unix->find_program("cryptsetup")." resize $uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);
	
	$cmd=$unix->find_program("e2fsck")." -f /dev/mapper/$uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);	
	
	$cmd=$unix->find_program("resize2fs")." -p /dev/mapper/$uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);	
	mountSafeBox($SafeBox,$safe->homeDirectory);
			
}

function SafeBoxUmount($uid){
	$unix=new unix();
	$safe=new safebox($uid);
	$mapper="/dev/mapper/$uid";
	if($unix->DISK_MOUNTED($mapper)){
		exec($unix->find_program("umount")." -fl $mapper");
		if($unix->DISK_MOUNTED($mapper)){
			writelogs("unable to umount $mapper",__FUNCTION__,__FILE__,__LINE__);
			return;
		}
	}
	writelogs("Crypted file: $safe->crypted_filepath",__FUNCTION__,__FILE__,__LINE__);
	$loop=_findDevLoop($safe->crypted_filepath);
	
	writelogs("sync",__FUNCTION__,__FILE__,__LINE__);
	exec($unix->find_program("sync"),$results);
	logsArray($results,__FUNCTION__,__LINE__);
	exec($unix->find_program("cryptsetup")." luksClose $uid",$results);
	logsArray($results,__FUNCTION__,__LINE__);
	if($loop<>null){
		exec($unix->find_program("losetup")." -d $loop",$results);
		logsArray($results,__FUNCTION__,__LINE__);
	}
	return true;
}




function SafeBoxUSer($uid){
	
	$unix=new unix();
	$user=new safebox($uid);
	if($user->CryptedHomePassword==null){
		writelogs("CryptedHomePassword attribute is null",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$GLOBALS["CRYPT_PASSWORD"]=$user->CryptedHomePassword;
	
	$ou=$user->ou;
	$size=$user->CryptedHomeSize;
	if($size<1){$size=1;}
	if($ou==null){
		writelogs("Unable to find organization for $uid",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$lvm=new lvm_org($ou);
	$repository=trim($lvm->storage_enabled);
	if($repository==nul){$repository="/home";}	
	
	if($repository<>"/home"){
		$mounts=$unix->GetFSTabMountPoint($repository);
	}
	if(!is_array($mounts)){
		writelogs("Unable to find mount point",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if($mounts[0]==null){
		writelogs("Unable to find mount point",__FUNCTION__,__FILE__,__LINE__);
		return;		
	}
	
	$repository=$mounts[0]."/.SafeBoxes";
	
	@mkdir($repository,null,true);
	writelogs("Checking safebox in path=$repository (org:$ou)",__FUNCTION__,__FILE__,__LINE__);
	$SafeBox="$repository/$uid";

	if(!is_file($SafeBox)){
		writelogs("Create container $SafeBox",__FUNCTION__,__FILE__,__LINE__);
		$cmd=$unix->find_program("dd")." if=/dev/zero bs=1G count=$size of=$SafeBox";
		writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
		exec($cmd,$results);
		logsArray($results,__FUNCTION__,__LINE__);
		
	}
	
		$file_size=$unix->file_size($SafeBox)/1024;
		$file_size=$file_size/1000;
		$file_size=$file_size/1000;
		$file_size=round($file_size);
	writelogs("Container size=$file_size Go against $size Go",__FUNCTION__,__FILE__,__LINE__);
	
	
	if($file_size<$size){
		writelogs("Container $SafeBox must be upgraded to $size",__FUNCTION__,__FILE__,__LINE__);
		if(!SafeBoxUmount($uid)){
			writelogs("Unable to umount safebox for $uid",__FUNCTION__,__FILE__,__LINE__);
			
		}else{
			SafeBoxIncreaseSize($uid);
		}
	}
	
	
	if(mountSafeBox($SafeBox,$user->homeDirectory)){
		writelogs("$SafeBox is already mounted",__FUNCTION__,__FILE__,__LINE__);
	}	
	
	if(!mountLoop($SafeBox)){
		writelogs("mountLoop() failed for $SafeBox",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	writelogs("Testing crypted partition of $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	if(!_IscryptFile($SafeBox)){
		
		writelogs("File is not crypted",__FUNCTION__,__FILE__,__LINE__);
		
		if($user->CryptedHomePassword==null){
			writelogs("No password set in the server, waiting Artica Network Agent to crypt file and mount it",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		_CryptFile($SafeBox,$user->CryptedHomePassword);
		
	}
	
	
	writelogs("Check crypt open of $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	if(!_CryptOpen($SafeBox,$user->CryptedHomePassword)){
		writelogs("luksOpen:: Failed to open $SafeBox",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	writelogs("Check filesystem of $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	if(!_IsFormated($SafeBox)){
		writelogs("dumpe2fs:: Not formated",__FUNCTION__,__FILE__,__LINE__);
		if(!_formatSafeBox($SafeBox)){
			writelogs("Unable to format $SafeBox",__FUNCTION__,__FILE__,__LINE__);
		}
	}
	
	writelogs("Mounting $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	if(!mountSafeBox($SafeBox,$user->homeDirectory)){
		writelogs("Unable to mount $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	}
	writelogs("Success mounting $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	
}

function _formatSafeBox($file){
	$mapper="/dev/mapper/".basename($file);
	$unix=new unix();
	$ext4=$unix->find_program("mkfs.ext4");
	$ext3=$unix->find_program("mkfs.ext3");
	
	if(!is_file($ext4)){
		if(!is_file($ext3)){return false;}
		$ext4=$ext3;
	}
	
	if(!$unix->IsExt4()){$ext4=$ext3;}		
	
	$cmd="$ext4 $mapper";
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);
	return _IsFormated($file);
}

function mountSafeBox($file,$home){
	$mapper="/dev/mapper/".basename($file);
	$home_default="/home/".basename($file);
	
	if($home==null){$home=$home_default;}	
	$unix=new unix();
	if($unix->DISK_MOUNTED($mapper)){return true;}
	
	
	if(is_file($unix->find_program("mkfs.ext4"))){
		$ext="ext4";
	}else{
	 if(is_file($unix->find_program("mkfs.ext3"))){$ext="ext3";}
	}
	if(!$unix->IsExt4()){$ext="ext3";}		
	if($ext==null){return false;}
	
	if(!_ifDirEmpty($home)){$renamed="$home-SafeBack";}
	$f=@stat($mapper);
	
	if(count($f)<3){
		writelogs("could not stat $mapper",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	if($renamed<>null){
		writelogs("rename $home to $renamed",__FUNCTION__,__FILE__,__LINE__);
		if(is_dir($renamed)){
			exec($unix->find_program("mv")." $home/* $renamed/");
		}else{
			@rename($home,$renamed);
		}
		@mkdir($home,0755,true);
	}
	
	writelogs("mount $mapper ($ext) to $home",__FUNCTION__,__FILE__,__LINE__);
	if(!is_dir($home)){@mkdir($home,0755,true);}
	$cmd=$unix->find_program("mount")." -t $ext $mapper $home";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);
	
	
	if($unix->DISK_MOUNTED($mapper)){
		if($renamed<>null){
			writelogs("move old datas",__FUNCTION__,__FILE__,__LINE__);
			exec($unix->find_program("mv")." $renamed/* $home/");
			if(_ifDirEmpty($renamed)){rmdir($renamed);}
			if(!_ifDirEmpty($home_default)){exec($unix->find_program("mv")." $home_default/* $home/");}
			if(_ifDirEmpty($home_default)){@rmdir($home_default);}
		}
		return true;
	}
	
	return false;
	
	
}

function _ifDirEmpty($dir){
	$unix=new unix();
	if(!is_dir($dir)){return true;}
	
	$files=$unix->dirdir($dir);
	if(count($files)>0){
		writelogs("$dir is not empty",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$files=$unix->DirFiles($dir);
	if(count($files)>0){
		writelogs("$home is not empty",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	return true;
}


function _CryptOpen($file,$password){
	$unix=new unix();
	$loop=_findDevLoop($file);
	$ff=basename($file);	
	$tmp=$unix->FILE_TEMP();
	$cmd=$unix->find_program("echo")." \"$password\"|".$unix->find_program("cryptsetup")." -q luksOpen $loop $ff >$tmp 2>&1";
	//writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	writelogs("open $loop/".basename($file),__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd");
	$results=explode("\n",@file_get_contents($tmp));
	@unlink($tmp);
	while (list ($num, $ligne) = each ($results)){
		if(trim($ligne)==null){continue;}
		if(preg_match("#Command successful#",$ligne)){return true;}
		if(preg_match("#Device already exists#",$ligne)){return true;}
		if(preg_match("#key slot 0 unlocked#",$ligne)){return true;}
		writelogs($ligne,__FUNCTION__,__FILE__,__LINE__);
	}	
}

function _CryptFile($file,$password){
	$unix=new unix();
	$loop=_findDevLoop($file);	
	$cmd=$unix->find_program("echo")." \"$password\"|".$unix->find_program("cryptsetup")." luksFormat --batch-mode -c aes -h sha256 $loop";
	writelogs("Crypt $loop",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}

function _IsFormated($file){
	$ff=basename($file);
	$mapper="/dev/mapper/$ff";
	$unix=new unix();
	exec($unix->find_program("dumpe2fs")." -h $mapper",$results);
	while (list ($num, $ligne) = each ($results)){
		if(preg_match("#Filesystem UUID:\s+(.+)#",$ligne,$re)){
			writelogs("UUID: ".trim($re[1]),__FUNCTION__,__FILE__,__LINE__);
			return true;
		}
	}
	return false;
}


function mountLoop($file){
	$unix=new unix();
	if(!is_file($file)){
		writelogs("Unable to stat $file",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	
	$loop=_findDevLoop($file);
	if($loop==null){$loop=_ConnectLoop($file);}
	
	if($loop==null){
		writelogs("Unable to connect (loop)",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$GLOBALS["CURRENTLOOP"][$file]=$loop;
	writelogs("using connection $loop",__FUNCTION__,__FILE__,__LINE__);
	
	return true;
}


function _findDevLoop($path){
	if($GLOBALS["CURRENTLOOP"][$path]<>null){return $GLOBALS["CURRENTLOOP"][$path];}
	$path=str_replace("/","\/",$path);
	$path=str_replace(".","\.",$path);
	$unix=new unix();
	exec($unix->find_program("losetup")." -a",$results);
	while (list ($num, $ligne) = each ($results)){
		if(preg_match("#^(.+?):.+?$path#",$ligne,$re)){
			return $re[1];
		}
	}
}
function _ConnectLoop($file){
	$unix=new unix();
	$losetup=$unix->find_program("losetup");
	$cmd="$losetup -f";
	exec($cmd,$res);
	$loop=$res[0];
	writelogs("using $loop for this connexion",__FUNCTION__,__FILE__,__LINE__);
	exec("$losetup $loop $file",$results);
	logsArray($results,__FUNCTION__,__LINE__);	
	return $loop;
}
function logsArray($array,$function,$line){
	
	if(!is_array($array)){return ;}
	while (list ($num, $ligne) = each ($array)){
		writelogs("$ligne",$function,__FILE__,$line);
	}
}

function _IscryptFile($file){
	$unix=new unix();
	$loop=_findDevLoop($file);
	$cmd=$unix->find_program("cryptsetup")." luksDump $loop";
	exec($cmd,$results);
	while (list ($num, $ligne) = each ($results)){
		if(trim($ligne)==null){continue;}
		//echo $ligne."\n";
		if(preg_match("#UUID:\s+(.+)#",$ligne,$re)){
			writelogs("$file ($loop) crypted uuid: ".trim($re[1]),__FUNCTION__,__FILE__,__LINE__);
			return true;
		}else{
			//writelogs("#$ligne# FALSE",__FUNCTION__,__FILE__,__LINE__);
		}
	}
	writelogs("$file ($loop) not crypted",__FUNCTION__,__FILE__,__LINE__);
	return false;
	
}

function SafeBoxIncreaseSize($uid){
	$unix=new unix();
	$safe=new safebox($uid);
	$mapper="/dev/mapper/$uid";
	$SafeBox=$safe->crypted_filepath;
	$file_size=$unix->file_size($SafeBox)/1024;
	$file_size=$file_size/1000;
	$file_size=$file_size/1000;
	$file_size=round($file_size);
	$increase=$safe->CryptedHomeSize-$file_size;
	writelogs("$SafeBox ($uid) {$file_size}Go increase it to {$increase}Go",__FUNCTION__,__FILE__,__LINE__);
	if($increase<0){
		writelogs("Failed negative size",__FUNCTION__,__FILE__,__LINE__);	
		return;
	}
	$cmd=$unix->find_program("dd")." conv=notrunc oflag=append if=/dev/zero bs=1G count=$increase of=$SafeBox";
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);
	if(!mountLoop($SafeBox)){
		writelogs("Failed to mount loop",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	if(!_CryptOpen($SafeBox,$GLOBALS["CRYPT_PASSWORD"])){
		writelogs("Failed to open $SafeBox",__FUNCTION__,__FILE__,__LINE__);
	}
	$cmd=$unix->find_program("cryptsetup")." resize $uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);
	
	$cmd=$unix->find_program("e2fsck")." -f /dev/mapper/$uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);	
	
	$cmd=$unix->find_program("resize2fs")." -p /dev/mapper/$uid";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	logsArray($results,__FUNCTION__,__LINE__);	
	return true;
	
}



?>