<?php
include_once(dirname(__FILE__)."/class.autofs.inc");
class lvm_root{
	var $vglist=array();
	var $mountdirt="";
	
	function lvm_root(){
		$this->listgroup();
	}	
	
	private function listgroup(){
		$unix=new unix();
		$pvdisplay=$unix->find_program("pvdisplay");
		exec("$pvdisplay -C -a  --noheadings --units m --separator \"<sep>\" 2>&1",$results);
		while (list ($num, $line) = each ($results)){
			$line=trim($line);if($line==null){continue;}
			$tbl=explode("<sep>",$line);
			$tbl[5]=str_replace(",",".",$tbl[5]);
			$tbl[5]=str_replace("m","",$tbl[5]);
			$tbl[5]=round(intval($tbl[5]),0);
			$this->vglist[$tbl[1]]=$tbl[5];
			
		}
	}
	
	private function getlvs($vg){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$lvs=$unix->find_program("lvs");	
		exec("$lvs -a --noheadings --unit m --separator \"<sep>\"  $vg 2>&1",$results);
		$array=array();
		while (list ($num, $line) = each ($results)){
			$line=trim($line);if($line==null){continue;}
			$tbl=explode("<sep>",$line);
			$tbl[3]=str_replace(",",".",$tbl[3]);
			$tbl[3]=str_replace("m","",$tbl[3]);
			$tbl[3]=round(intval($tbl[3]),0);		
			if($debug){echo "getlvs() -> \"$vg\" {$tbl[0]} ({$tbl[3]}M)\n";}	
			$array[$tbl[0]]=$tbl[3];
			
		}
		return $array;
	}
	
	
	public function DeleteLVS($vg,$lvs,$mountdir){
		$this->mountdirt=$mountdir;
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$umount=$unix->find_program("umount");
		$dev="/dev/$vg/$lvs";
		if($debug){echo "DeleteLVS() -> umount $dev\n";}
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		if($debug){echo "$umount -l $dev\n";}
		$UUID=$this->Getuuid($vg,$lvs);
		if($UUID<>null){
			$auto=new autofs();
			$auto->uuid=$UUID;
			$auto->by_uuid_removemedia($this->mountdirt,"auto");
		}
		
		$lvremove=$unix->find_program("lvremove");
		$dev="/dev/$vg/$lvs";
		$cmd="$lvremove -f $dev 2>&1";
		exec($cmd,$results);		
		if($debug){while (list ($num, $line) = each ($results)){echo "DeleteLVS() -> $line\n";}}
		$lvs_list=$this->getlvs($vg);
		if(!is_numeric($lvs_list[$lvs])){return true;}
	}
	
	
	private function lvcreate($vg,$lvs,$sizeMB){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$lvcreate=$unix->find_program("lvcreate");
		$cmd="$lvcreate -n \"$lvs\" -L {$sizeMB}m \"$vg\" 2>&1";
		if($debug){echo "$cmd\n";}
		exec($cmd,$results);
		if($debug){while (list ($num, $line) = each ($results)){echo "lvcreate() -> $line\n";}}
		$lvs_list=$this->getlvs($vg);
		if(!is_numeric($lvs_list[$lvs])){
			if($debug){echo "lvcreate() -> $lvs \"{$lvs_list[$lvs]}MB\" failed\n";}
			return false;
		}
		return true;
		
	}
	
	public function CreateLVS($vg,$lvs,$sizeMB,$mountdir){
		$debug=$GLOBALS["VERBOSE"];
		$this->mountdirt=$mountdir;
		$vgsizeFree=$this->vglist[$vg];
		$lvs_list=$this->getlvs($vg);
		if(!is_numeric($vgsizeFree)){$vgsizeFree=0;}
		if($debug){echo "CreateLVS() -> $vg {$vgsizeFree}M free\n";}
		if($vgsizeFree<$sizeMB){if($debug){echo "CreateLVS() -> $vg no space left\n";}return;}
		if($debug){echo "CreateLVS() -> $lvs \"{$lvs_list[$lvs]}MB\" required {$sizeMB}MB\n";}
		if(!is_numeric($lvs_list[$lvs])){
			if($debug){echo "CreateLVS() -> $lvs \"{$lvs_list[$lvs]}MB\" create it\n";}
			if(!$this->lvcreate($vg,$lvs,$sizeMB)){if($debug){echo "CreateLVS() -> unable to create LV $lvs\n";}return;}
		}else{
			if($lvs_list[$lvs]<$sizeMB){
				if($debug){echo "CreateLVS() -> $lvs \"{$lvs_list[$lvs]}MB\" expand it to $sizeMB\n";}
				$this->lvresize($vg,$lvs,$sizeMB);
			}
		}
		
		if(!$this->ifFileSystem($vg,$lvs)){
			if($debug){echo "CreateLVS() -> No filesystem, create it...\n";}
			if(!$this->mke2fs($vg,$lvs)){if($debug){echo "CreateLVS() -> unable to create filesystem on /dev/$vg/$lvs\n";}return;}
		}
		if($debug){echo "CreateLVS() -> filesystem, created...\n";}
		if(!$this->ConnectToAutofs($vg,$lvs)){
			if($debug){echo "CreateLVS() -> AutoFS failed...\n";}
			return false;
		}
		
		return true;
	}
	
	private function lvresize($vg,$lvs,$size){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$e2fsck=$unix->find_program("e2fsck");
		$lvresize=$unix->find_program("lvresize");
		$resize2fs=$unix->find_program("resize2fs");
		$umount=$unix->find_program("umount");
		$dev="/dev/$vg/$lvs";
		if($debug){echo "lvresize() -> umount $dev\n";}
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		if($debug){echo "$umount -l $dev \n";}
		exec("$e2fsck -f /dev/$vg/$lvs 2>&1",$results);
		exec("$lvresize -L {$size}m /dev/$vg/$lvs 2>&1",$results);
		exec("$resize2fs /dev/$vg/$lvs 2>&1",$results);
		exec("/etc/init.d/autofs reload 2>&1",$results);
		if($debug){while (list ($num, $line) = each ($results)){echo "lvresize() -> $line\n";}}
	}
	
	
	private function Getuuid($vg,$lvs){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$dev="/dev/$vg/$lvs";
		$tune2fs=$unix->find_program("tune2fs");
		$cmd="$tune2fs -l $dev 2>&1";
		exec($cmd,$results);
		$array=array();	
		if($debug){echo "Getuuid($vg,$lvs) -> $cmd ". count($results)." rows\n";}	
		while (list ($num, $line) = each ($results)){
			if(preg_match("#UUID:\s+(.+)#i",$line,$re)){
				if($debug){echo "Getuuid($vg,$lvs) -> ". trim($re[1])."\n";}	
				return trim($re[1]);
			}
		}
		
	}	
	
	private function ifFileSystem($vg,$lvs){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$dev="/dev/$vg/$lvs";
		$tune2fs=$unix->find_program("tune2fs");
		$cmd="$tune2fs -l $dev 2>&1";
		exec($cmd,$results);
		$array=array();	
		if($debug){echo "ifFileSystem($vg,$lvs) -> $cmd ". count($results)." rows\n";}	
		while (list ($num, $line) = each ($results)){
			
			if(preg_match("#Filesystem magic number:\s+(.+)#i",$line,$re)){
				if($debug){echo "ifFileSystem($vg,$lvs) ->  Filesystem magic number = {$re[1]}\n";}
				return true;
			}
			
		}
		if($debug){echo "ifFileSystem($vg,$lvs) FALSE\n";}
		return false;
		
	}
	
	private function mke2fs($vg,$lvs){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$mkfs_ext4=$unix->find_program("mkfs.ext4");
		if(!is_file($mkfs_ext4)){$mkfs_ext4=$unix->find_program("mkfs.ext3");}	
		$dev="/dev/$vg/$lvs";
		$cmd="$mkfs_ext4 -q $dev 2>&1";
		exec($cmd,$results);
		if($debug){echo "mke2fs($vg,$lvs) -> $cmd ". count($results)." rows\n";}	
		if($debug){while (list ($num, $line) = each ($results)){echo "mke2fs() -> $line\n";}}
		if($this->ifFileSystem($vg,$lvs)){return true;}
	}

	private function ConnectToAutofs($vg,$lvs){
		$debug=$GLOBALS["VERBOSE"];
		$UUID=$this->Getuuid($vg,$lvs);
		if($UUID==null){if($debug){echo "ConnectToAutofs($vg,$lvs) -> unable to stat UUID\n";}return;}
		$auto=new autofs();
		$auto->uuid=$UUID;
		if($auto->by_uuid_addmedia($this->mountdirt,"auto")){
			shell_exec("/etc/init.d/autofs reload");
			return true;
		}		
		
	}
		
	
}


?>