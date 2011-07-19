<?php


$source="/home/dtouzeau/OCS_TEMP/PNG_Medical Healthcare Icons/Regular";



foreach (glob("$source/*",GLOB_ONLYDIR) as $dirname) {
	if(is_dir("$dirname/256x256")){
		mvpng("$dirname/256x256");
	}
}


function mvpng($dirname){
	$destination="/media/hd2/backup-home-dtouzeau/dtouzeau/Images/Database Application Icons/Regular/final/medical-regular";
	@mkdir($destination);
	foreach (glob("$dirname/*.png") as $filename) {
		if(copy($filename,$destination."/".basename($filename))){
				echo $filename."\n";
				@unlink($filename);
		}
	}
}
?>