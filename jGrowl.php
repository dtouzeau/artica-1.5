<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');

writelogs("Running jGrowl...","MAIN",__FUNCTION__,__FILE__,__LINE__);

if(is_file("ressources/logs/web/jgrowl.txt")){
	$tpl=new templates();
	$datas=@file_get_contents("ressources/logs/web/jgrowl.txt");
	$datas=str_replace("Warning: bad ps syntax, perhaps a bogus '-'? See http://procps.sf.net/faq.html","",$datas);
	writelogs("Echo jGrowl..." .strlen($data)." bytes","MAIN",__FUNCTION__,__FILE__,__LINE__);
	echo $tpl->_ENGINE_parse_body($datas);
	@unlink("ressources/logs/web/jgrowl.txt");
}