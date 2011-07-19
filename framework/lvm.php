<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");

if(isset($_GET["pvdisplay-dev"])){pvdisplay_dev();exit;}
if(isset($_GET["lvmdisk-free"])){lvmdiskscan_free();exit;}
if(isset($_GET["convert-disk"])){convert_disk();exit;}
if(isset($_GET["vgcreate"])){vgcreate();exit;}
if(isset($_GET["vgdisplay"])){vgdisplay();exit;}
if(isset($_GET["lvcreate"])){lvcreate();exit;}
if(isset($_GET["lvdisplay"])){lvdisplay();exit;}
if(isset($_GET["mke2fs"])){mke2fs();exit;}
if(isset($_GET["lvremove"])){lvremove();exit;}
if(isset($_GET["lvresize"])){lvresize();exit;}
if(isset($_GET["loop-del"])){loopdel();exit;}
if(isset($_GET["loopcheck"])){loopcheck();exit;}




while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);


function pvdisplay_dev(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$pvdisplay=$unix->find_program("pvdisplay");
	$cmd="$pvdisplay -C -a  --noheadings --separator \"<sep>\" $dev 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$datas=trim(@implode(" ",$results));
	$tbl=explode("<sep>","$datas");

	$array[$dev]=array("GROUP"=>$tbl[1],"SIZE"=>$tbl[4],"FREE"=>$tbl[5]);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";	
	}
function lvmdiskscan_free(){
	$unix=new unix();
	$lvmdiskscan=$unix->find_program("lvmdiskscan");
	$cmd="$lvmdiskscan 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$mounts=LoadMounted();
	while (list ($num, $path) = each ($results)){
		if(preg_match("#LVM physical#i",$path)){continue;}
		
		if(preg_match("#(.+?)\s+\[(.+?)\]#",$path,$re)){
			$dev=trim($re[1]);
			if($dev=="/dev/root"){continue;}
			if(preg_match("#\/dev\/ram[0-9]+#",$dev)){continue;}
			if($mounts[$dev]){continue;}
			$array[trim($re[1])]=trim($re[2]);
		}else{
			writelogs_framework("No match $path",__FUNCTION__,__FILE__,__LINE__);
		}
	}
writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
	}
	
function LoadMounted(){
	$fs=explode("\n",@file_get_contents("/proc/mounts"));
	while (list ($num, $path) = each ($fs)){
		if(preg_match("#^(.+?)\s+.+?#",$path,$re)){$array[trim($re[1])]=true;}
		
	}
	return $array;
}
function convert_disk(){
	$unix=new unix();
	$pvcreate=$unix->find_program("pvcreate");
	$cmd="$pvcreate {$_GET["dev"]} 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}$ff[]=$line;}	
	echo "<articadatascgi>". base64_encode(trim(@implode("\n",$ff)))."</articadatascgi>";	
}
function vgcreate(){
	$unix=new unix();
	$gpname=$_GET["gpname"];
	$vgcreate=$unix->find_program("vgcreate");
	$cmd="$vgcreate \"$gpname\" {$_GET["dev"]} 2>&1";
	exec($cmd,$results);
	while (list ($num, $line) = each ($results)){if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}$ff[]=$line;}
	
	
	writelogs_framework("$cmd ". count($ff)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(trim(@implode("\n",$ff)))."</articadatascgi>";		
	
}
function vgdisplay(){
	$unix=new unix();
	$gpname=$_GET["gpname"];
	$vgdisplay=$unix->find_program("vgdisplay");
	$lvs=$unix->find_program("lvs");
	$cmd="$vgdisplay -C --noheadings --separator \";\" --units k \"$gpname\" 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	$tbl=explode(";",@implode("",$results));
	/* 
	0-> reewebs;
	1-> 1;
	2->0;
	3->0;
	4->wz--n-;
	5->50,00g;
	6->50,00g*/
	$results=array();
	$cmd="$lvs --noheadings --separator \";\" --units k \"$gpname\" 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	while (list ($num, $line) = each ($results)){
		$line=trim($line);
		if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}
		writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);
		if(trim($line)==null){continue;}
		$f=explode(";",$line);
		// vol1;freewebs;-wi-a-;8388,61K;;;;;;
		$vs[$f[0]]=array("SIZE"=>$f[3],"CURRENT_SIZE"=>x_lvdisplay_size($gpname,$f[0]));
		
		
	}
	
	
	$array=array("SIZE"=>$tbl[5],"FREE"=>$tbl[6],"VS"=>$vs);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
	
}

function lvcreate(){
	//lvm.php?lvcreate=yes&lvname=&$lvname&lvsize=$lvsize&dev=".urlencode($_GET["dev"])."&gpname=".urlencode($vg))
	$unix=new unix();
	$gpname=$_GET["gpname"];
	$lvname=$_GET["lvname"];
	$lvsize=$_GET["lvsize"];
	$lvcreate=$unix->find_program("lvcreate");
	$cmd="$lvcreate -n \"$lvname\" -L {$lvsize}m \"$gpname\" 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}$ff[]=$line;}
	
	
	echo "<articadatascgi>". base64_encode(trim(@implode("\n",$ff)))."</articadatascgi>";
	
}


function lvresize(){
		$unix=new unix();
		$e2fsck=$unix->find_program("e2fsck");
		$lvresize=$unix->find_program("lvresize");
		$resize2fs=$unix->find_program("resize2fs");
		$umount=$unix->find_program("umount");
		$vg=$_GET["vg"];
		$size=$_GET["size"];
		$lvs=$_GET["lvs"];
		$dev="/dev/$vg/$lvs";
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		exec("$e2fsck -f /dev/$vg/$lvs 2>&1",$results);
		exec("$lvresize -L {$size}m /dev/$vg/$lvs 2>&1",$results);
		exec("$resize2fs /dev/$vg/$lvs 2>&1",$results);
		exec("/etc/init.d/autofs reload 2>&1",$results);
		while (list ($num, $line) = each ($results)){if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}$ff[]=$line;}	
		echo "<articadatascgi>". base64_encode(@implode("\n",$ff))."</articadatascgi>";		
	}


function lvdisplay(){
	$unix=new unix();
	$lvdisplay=$unix->find_program("lvdisplay");
	$vg=$_GET["lvdisplay"];
	$cmd="lvdisplay --units k {$_GET["lvdisplay"]} 2>&1";
	exec($cmd,$results);
	$array=array();	
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#LV Name\s+(.+)#",$line,$re)){
			$lvname=trim($re[1]);
			$array[$lvname]["INFOS"]=x_lvdisplay($lvname);
			$array[$lvname]["CURRENT_SIZE"]=x_lvdisplay_size($vg,basename($lvname));
			
			continue;
		}
		if(preg_match("#VG Name\s+(.+)#",$line,$re)){
			$array[$lvname]["VG"]=trim($re[1]);
			
			continue;
		}
		
		if(preg_match("#LV UUID\s+(.+)#",$line,$re)){
			$array[$lvname]["UUID"]=trim($re[1]);
			continue;
		}

		if(preg_match("#LV Size\s+([0-9\.,]+)#",$line,$re)){
			$array[$lvname]["SIZE"]=trim($re[1]);
			continue;
		}			
		
		if(preg_match("#Physical volume\s+([0-9\.,]+)#",$line,$re)){
			$array[$lvname]["DEV"]=trim($re[1]);
			continue;
		}
	}

	

	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
	
}
function x_lvdisplay_size($vg,$lv){
	if($lv==null){
		writelogs_framework("$vg: LV is null",__FUNCTION__,__FILE__,__LINE__);
		return array();
	}
	$unix=new unix();
	$df=find_program("df");
	$mapper="/dev/mapper/$vg-$lv";
	$cmd="$df -B K $mapper 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#([0-9]+)K\s+([0-9]+)K\s+([0-9]+)K\s+([0-9]+)%\s+\/#",$line,$re)){
			return array("USED"=>$re[2],"FREE"=>$re[3],"POURC"=>$re[4]);
		}
		
	}	
	return array();
	
}

function x_lvdisplay($dev){
	$unix=new unix();
	$tune2fs=$unix->find_program("tune2fs");
	$cmd="$tune2fs -l $dev 2>&1";
	exec($cmd,$results);
	$array=array();	
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Filesystem UUID:\s+(.+)#i",$line,$re)){$array["UUID"]=$re[1];continue;}
		if(preg_match("#Filesystem magic number:\s+(.+)#i",$line,$re)){$array["MAGIC_NUMBER"]=$re[1];continue;}
		if(preg_match("#Filesystem OS type:\s+(.+)#i",$line,$re)){$array["OS"]=$re[1];continue;}
		
	}
	
	return $array;
	
}

function mke2fs(){
	$unix=new unix();
	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];	
	$mkfs_ext4=$unix->find_program("mkfs.ext4");
	if(!is_file($mkfs_ext4)){
		$mkfs_ext4=$unix->find_program("mkfs.ext3");
	}	
	
	if(!$unix->IsExt4()){
		$mkfs_ext4=$unix->find_program("mkfs.ext3");
	}
	
	$dev="/dev/$vg/$lvs";
	$cmd="$mkfs_ext4 -q $dev 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){
		writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);
	}	
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function lvremove(){

	$unix=new unix();
	$lvs=$_GET["lvs"];
	$vg=$_GET["vg"];		
	$umount=$unix->find_program("umount");
	$dev="/dev/$vg/$lvs";
	$cmd="$umount -l $dev 2>&1";	
	exec($cmd,$results);
	exec($cmd,$results);
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	
	$lvremove=$unix->find_program("lvremove");
	$dev="/dev/$vg/$lvs";
	$cmd="$lvremove -f $dev 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	while (list ($num, $line) = each ($results)){writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);}	
	reset($results);
	while (list ($num, $line) = each ($results)){if(preg_match("#File descriptor\s+[0-9]+#",$line)){continue;}$ff[]=$line;}	
	echo "<articadatascgi>". base64_encode(@implode("\n",$ff))."</articadatascgi>";		
}

function loopdel(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$cmd="$php /usr/share/artica-postfix/exec.loopdisks.php --remove \"{$_GET["loop-del"]}\" 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";		
	
}

function loopcheck(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.loopdisks.php >/dev/null &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}





