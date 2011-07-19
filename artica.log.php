<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.main_cf.inc');

if(isset($_GET["logs"])){echo Logs();exit;}

$html="
<p>{text}</p>
<div class=logs id='log_area'></div>
<script>Load_artica_log();artica_StartTimer();</script>
";



$tpl=new templates('{title}',$html);
echo $tpl->web_page;




function Logs(){
	$tpl=new templates();
	$datas=$tpl->_readfile('ressources/logs/artica-web.log');
	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$tbl=array_reverse ($tbl, TRUE);
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match('#WARNING:#',$val)){$color='color:red';}else{$color=null;}
		if(preg_match('#INFOS:#',$val)){$color='color:blue';}else{$color=null;}
		echo "<div style='border-bottom:1px solid #CCCCCC;margin-bottom:4px;$color;font-size:10px'>$val</div>";
		
	}
	
	
	
}
?>