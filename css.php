<?php
if(!isset($_GET["template"])){die();}

$template=$_GET["template"];
$page=$_GET["page"];

foreach (glob("ressources/templates/$template/css/*.css") as $filename) {
			//$datas=@file_get_contents("$filename");
			//$datas=str_replace("\n", " ", $datas);
			$css[]=$datas;
		}
//header("Content-type: text/css");		
echo @implode("\n", $css);