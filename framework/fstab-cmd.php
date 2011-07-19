<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");


if(isset($_GET["fstabmount"])){fstabmount();exit;}
if(isset($_GET["fstabumount"])){fstabumount();exit;}




function fstabmount(){
	
	$unix=new unix();
	
	$fstabmount=$_GET["fstabmount"];
	$dev=$unix->FSTAB_GETDEV($fstabmount);
	if($dev<>null){$ext=$unix->DISK_GET_TYPE($dev);}
	
	if($ext==null){$ext="auto";}
	if(!is_dir($fstabmount)){@mkdir($fstabmount);}
	$tmp=$unix->FILE_TEMP();
	
	
	if($ext=="ext4"){
		$kernel=$unix->KERNEL_VERSION_BIN();
		error_log("framework::".__FUNCTION__." kernel: $kernel");
		if($kernel<20629){
			$ext="ext4dev";
		}
	}
	
	$cmd="/bin/mount -t $ext $dev $fstabmount >$tmp 2>&1";
	error_log("framework::".__FUNCTION__." $cmd");
	shell_exec("/bin/mount -t $ext $dev $fstabmount >$tmp 2>&1");
	$results=@file_get_contents($tmp);
	@unlink($tmp);
	
	error_log("framework::".__FUNCTION__." mount point is \"$dev\" ($ext)=$results line ".__LINE__);
	if(strlen($results)>0){echo "<articadatascgi>$results</articadatascgi>";}
}

function fstabumount(){
	shell_exec("/bin/umount -l {$_GET["fstabumount"]}");
}
?>