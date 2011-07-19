<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	if(isset($_GET["rmdirp"])){rmdirp();exit;}
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if($usersmenus->AllowAddGroup==false){die();}
	
	
	
	
	
	$branch_id=$_POST["branch_id"];
	
if($branch_id=="root"){
	json_root();exit;
}else{json_root($branch_id);}
	
	
	

function json_root($path=null){
	
	$sock=new sockets();
	if($path==null){
		$datas=$sock->getfile('dirdir:/');}
	else{
		$datas=$sock->getfile('dirdir:'.$path);	
	}
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	while (list($num,$val)=each($tbl)){
		if(trim($val)<>null){
			$newpath="$path/$val";
			$img='folder.gif';
			$pop="'onopenpopulate' : YahooTreeFoldersPopulate,
			'openlink' : 'yahoo.tree.populate.php?p={$_POST["p"]}',
			'onclick' : YahooTreeClick,
			'canhavechildren' : true,
			'ondblclick' : YahooSelectedFolders";
			
			if(Folders_interdis($newpath)){
				$img='lock.gif';
				$pop=null;
			}
			
			$arr[]="{
    			'id':'$newpath',
    			'txt':'$val',
    			'img':'$img',
				'imgopen':'folderopen.gif', 
				'imgclose':'folder.gif', 
				$pop
				},";
		}
		
	}
	if(!is_array($arr)){return null;}
	$res=implode("\n",$arr);
	if(substr($res,strlen($res)-1,1)==','){
		$res=substr($res,0,strlen($res)-1);
	}
	
	echo "[$res]";
}

function rmdirp(){
	$user=new usersMenus();
	if(!$user->AsSambaAdministrator){return null;}
	if(Folder_to_not_remove($_GET["rmdirp"])){return null;}
	$sock=new sockets();
	$sock->getfile("rmdirp:{$_GET["rmdirp"]}");
	}

function Folders_interdis($folder){
	
	
	$l["/sys"]=true;
	$l["/initrd"]=true;
	$l["/dev"]=true;
	$l["/etc"]=true;
	$l["/boot"]=true;
	$l["/opt"]=true;
	$l["/var/lib"]=true;
	$l["/sbin"]=true;
	$l["/lib"]=true;
	$l["/bin"]=true;
	$l["/usr/libexec"]=true;
	$l["/usr/sbin"]=true;
	$l["/usr/bin"]=true;	
	$l["/usr/include"]=true;	
	$l["/usr/local"]=true;	
	$l["/usr/share"]=true;		
	$l["/usr/src"]=true;		
	$l["/usr/usr"]=true;
	$l["/usr/X11R6"]=true;
	$l["/usr/lib"]=true;
	$l["/usr/lib64"]=true;
	$l["/usr/src"]=true;	
	$l["/srv"]=true;
	$l["/var/log"]=true;
	$l["/var/log"]=true;
	$l["/var/cache"]=true;
	$l["/var/db"]=true;
	$l["/var/lib"]=true;
	$l["/var/local"]=true;
	$l["/var/lock"]=true;
	$l["/var/mail"]=true;
	$l["/var/milter-greylist"]=true;
	$l["/var/opt"]=true;
	$l["/var/run"]=true;
	$l["/var/spool"]=true;
	$l["/var/tmp"]=true;
	$l["/var/webmin"]=true;
	$l["/lost+found"]=true;
	
	if(!$l[$folder]){return false;}else{return true;}
	
	
}

function Folder_to_not_remove($folder){
	if(Folders_interdis($folder)){return true;}
	
	$l["/home"]=true;
	if(!$l[$folder]){return false;}else{return true;}
}


?>