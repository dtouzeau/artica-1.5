<?php

class cups{
	
	
	function cups(){
		
		
	}
	
	
	function ScanPrinters(){
		$sock=new sockets();
		$datas=$sock->getfile('lpinfo');
		$tbl=explode("\n",$datas);
		
		while (list ($num, $ligne) = each ($tbl) ){
			if(preg_match("#direct\s+(usb):\/\/(.+)#",$ligne,$re)){
				$arr[]=array(
						"TYPE"=>"{$re[1]}","PATH"=>$re[2]);
				}
			}
		return $arr;

		}
		
		
	function DriversNumber(){
		if(!is_file(dirname(__FILE__).'/scan.printers.drivers.inc')){
			return 0;
		}	
		
		include dirname(__FILE__).'/scan.printers.drivers.inc';

		while (list ($num, $ligne) = each ($GLOBAL_PRINTERS)){
			$vendors[$num]=$num;
			$count=$count+count($ligne);
			}
		return $count;
	}
	
	function DriversSearch($pattern){
		if(!is_file(dirname(__FILE__).'/scan.printers.drivers.inc')){
			return array();
		}	
		$pattern=trim($pattern);
		if($pattern==null){$pattern="A*";}
		include dirname(__FILE__).'/scan.printers.drivers.inc';
		
		if(strpos($pattern,'*')==0){$pattern=$pattern.'*';}
		$pattern=str_replace("*",'.*?',$pattern);
		$pattern=str_replace(" ",'.*?',$pattern);
		
		writelogs("Search pattner \"$pattern\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$count=0;
		while (list ($num, $ligne) = each ($GLOBAL_PRINTERS)){
			$vendor=$num;
			while (list ($printer, $lang) = each ($ligne)){
				while (list ($language, $driver_path) = each ($lang)){
					if(preg_match("#$pattern#i","$vendor $printer $language")){
						$count=$count+1;
						if($count>100){break;}
						$res["$printer ($language)"]=array("VENDOR"=>$vendor,"PRINTER"=>"$printer","DRIVER"=>$driver_path);
					}
				}
			}
		}
		if(!is_array($res)){return array();}
		ksort($res);
		return $res;
	}	
	
	
}

?>