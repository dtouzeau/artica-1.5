<?php

class os_system{
	var $meta_array=array();
	
	
function memory() {
    $results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['swap'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['devswap'] = array();
    if ($this->rfts('/proc/meminfo', $bufr)) {
      $bufe = explode("\n", $bufr);
      foreach($bufe as $buf) {
        if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['total'] = $ar_buf[1];
        } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['free'] = $ar_buf[1];
        } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['cached'] = $ar_buf[1];
        } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['buffers'] = $ar_buf[1];
        }
      }
      $results['ram']['used'] = $results['ram']['total']-$results['ram']['free'];
      $results['ram']['percent'] = round(($results['ram']['used']*100) /$results['ram']['total']);
      // values for splitting memory usage
      if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
        $results['ram']['app'] = $results['ram']['used']-$results['ram']['cached']-$results['ram']['buffers'];
        $results['ram']['app_percent'] = round(($results['ram']['app']*100) /$results['ram']['total']);
        $results['ram']['buffers_percent'] = round(($results['ram']['buffers']*100) /$results['ram']['total']);
        $results['ram']['cached_percent'] = round(($results['ram']['cached']*100) /$results['ram']['total']);
      }
      if ($this->rfts('/proc/swaps', $bufr)) {
        $swaps = explode("\n", $bufr);
        for ($i = 1;$i < (sizeof($swaps));$i++) {
          if (trim($swaps[$i]) != "") {
            $ar_buf = preg_split('/\s+/', $swaps[$i], 6);
            $results['devswap'][$i-1] = array();
            $results['devswap'][$i-1]['dev'] = $ar_buf[0];
            $results['devswap'][$i-1]['total'] = $ar_buf[2];
            $results['devswap'][$i-1]['used'] = $ar_buf[3];
            $results['devswap'][$i-1]['free'] = ($results['devswap'][$i-1]['total']-$results['devswap'][$i-1]['used']);
            $results['devswap'][$i-1]['percent'] = round(($ar_buf[3]*100) /$ar_buf[2]);
            $results['swap']['total']+= $ar_buf[2];
            $results['swap']['used']+= $ar_buf[3];
            $results['swap']['free'] = $results['swap']['total']-$results['swap']['used'];
            $results['swap']['percent'] = ceil(($results['swap']['used']*100) /(($results['swap']['total'] <= 0) ? 1 : $results['swap']['total']));
          }
        }
      }
    }
    return $results;
  }
  
  	function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true) {
  $strFile = "";
  $intCurLine = 1;
  if (file_exists($strFileName)) {
    if ($fd = fopen($strFileName, 'r')) {
      while (!feof($fd)) {
        $strFile.= fgets($fd, $intBytes);
        if ($intLines <= $intCurLine && $intLines != 0) {
          break;
        } else {
          $intCurLine++;
        }
      }
      fclose($fd);
      $strRet = $strFile;
    } else {
      if ($booErrorRep) {
        
      }
      return false;
    }
  } else {
    if ($booErrorRep) {
      
    }
    return false;
  }
  return true;
}

public function cpu_info() {
    $results = array("cpus" => 0);
    if ($this->rfts('/proc/cpuinfo', $bufr)) {
      $bufe = explode("\n", $bufr);
      $results = array('cpus' => 0, 'bogomips' => 0);
      $ar_buf = array();
      foreach($bufe as $buf) {
        $arrBuff = preg_split('/\s+:\s+/', trim($buf));
        if (count($arrBuff) == 2) {
          $key = $arrBuff[0];
          $value = $arrBuff[1];
          // All of the tags here are highly architecture dependant.
          // the only way I could reconstruct them for machines I don't
          // have is to browse the kernel source.  So if your arch isn't
          // supported, tell me you want it written in.
          switch ($key) {
            case 'model name':
              $results['model'] = $value;
            break;
            case 'cpu MHz':
              $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
            case 'cycle frequency [Hz]': // For Alpha arch - 2.2.x
              $results['cpuspeed'] = sprintf('%.2f', $value/1000000);
            break;
            case 'clock': // For PPC arch (damn borked POS)
              $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
            case 'cpu': // For PPC arch (damn borked POS)
              $results['model'] = $value;
            break;
            case 'L2 cache': // More for PPC
              $results['cache'] = $value;
            break;
            case 'revision': // For PPC arch (damn borked POS)
              $results['model'].= ' ( rev: ' . $value . ')';
            break;
            case 'cpu model': // For Alpha arch - 2.2.x
              $results['model'].= ' (' . $value . ')';
            break;
            case 'cache size':
              $results['cache'] = $value;
            break;
            case 'bogomips':
              $results['bogomips']+= $value;
            break;
            case 'BogoMIPS': // For alpha arch - 2.2.x
              $results['bogomips']+= $value;
            break;
            case 'BogoMips': // For sparc arch
              $results['bogomips']+= $value;
            break;
            case 'cpus detected': // For Alpha arch - 2.2.x
              $results['cpus']+= $value;
            break;
            case 'system type': // Alpha arch - 2.2.x
              $results['model'].= ', ' . $value . ' ';
            break;
            case 'platform string': // Alpha arch - 2.2.x
              $results['model'].= ' (' . $value . ')';
            break;
            case 'processor':
              $results['cpus']+= 1;
            break;
            case 'Cpu0ClkTck': // Linux sparc64
              $results['cpuspeed'] = sprintf('%.2f', hexdec($value) /1000000);
            break;
            case 'Cpu0Bogo': // Linux sparc64 & sparc32
              $results['bogomips'] = $value;
            break;
            case 'ncpus probed': // Linux sparc64 & sparc32
              $results['cpus'] = $value;
            break;
          }
        }
      }
      // sparc64 specific code follows
      // This adds the ability to display the cache that a CPU has
      // Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
      // Modified by Tom Weustink <freshy98@gmx.net> in 2004
      $sparclist = array('SUNW,UltraSPARC@0,0', 'SUNW,UltraSPARC-II@0,0', 'SUNW,UltraSPARC@1c,0', 'SUNW,UltraSPARC-IIi@1c,0', 'SUNW,UltraSPARC-II@1c,0', 'SUNW,UltraSPARC-IIe@0,0');
      foreach($sparclist as $name) {
        if ($this->rfts('/proc/openprom/' . $name . '/ecache-size', $buf, 1, 32, false)) {
          $results['cache'] = base_convert($buf, 16, 10) /1024 . ' KB';
        }
      }
      // sparc64 specific code ends
      // XScale detection code
      if ($results['cpus'] == 0) {
        foreach($bufe as $buf) {
          $fields = preg_split('/\s*:\s*/', trim($buf), 2);
          if (sizeof($fields) == 2) {
            list($key, $value) = $fields;
            switch ($key) {
              case 'Processor':
                $results['cpus']+= 1;
                $results['model'] = $value;
              break;
              case 'BogoMIPS': //BogoMIPS are not BogoMIPS on this CPU, it's the speed, no BogoMIPS available
                $results['cpuspeed'] = $value;
              break;
              case 'I size':
                $results['cache'] = $value;
              break;
              case 'D size':
                $results['cache']+= $value;
              break;
            }
          }
        }
        $results['cache'] = $results['cache']/1024 . " KB";
      }
    }
    $keys = array_keys($results);
    $keys2be = array('model', 'cpuspeed', 'cache', 'bogomips', 'cpus');
    while ($ar_buf = each($keys2be)) {
      if (!in_array($ar_buf[1], $keys)) {
        $results[$ar_buf[1]] = 'N.A.';
      }
    }
    if ($this->rfts('/proc/acpi/thermal_zone/THRM/temperature', $buf, 1, 4096, false)) {
      $results['temp'] = substr($buf, 25, 2);
    }
    return $results;
  }
  
  function realMemory(){
  		$hash_mem=array();
  		$datas=shell_exec(dirname(__FILE__)."/mem.pl");
  		if(preg_match('#T=([0-9]+) U=([0-9]+)#',$datas,$re)){
  			$ram_total=$re[1];
  			$ram_used=$re[2];
  			}
		$pourc=($ram_used*100)/$ram_total;
  		$pourc = round($pourc);
  		
  		$hash_mem["ram"]["percent"]=$pourc;
  		$hash_mem["ram"]["used"]=$ram_used;
  		$hash_mem["ram"]["total"]=$ram_total;
  		
  		return $hash_mem;
  		
  	
  }
  
  function swap(){
  	$datas=explode("\n",shell_exec('free -o'));
  	if(!is_array($datas)){return array();}
  	while (list ($num, $val) = each ($datas) ){
  		if(preg_match("#Swap:\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#",$val,$re)){
  			$total=$re[1];
  			$used=$re[2];
  			if($total==0){return array(0,0,0);}
  			$pourc=round(($used/$total)*100);
  			return array($pourc,$used,$total);
  		}
  		
  	}
  	
  	
  }
  
function html_load_usage($loadc=0,$tiny=0){
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	$users=new usersMenus();
	$cpunum=intval($users->CPU_NUMBER);
	$array_load=sys_getloadavg();
	$org_load=$array_load[2];
	$load=intval($org_load);
	//middle =$cpunum on va dire que 100% ($cpunum*2) + orange =0,75*$cpunum
	$max_vert_fonce=$cpunum;
	$max_vert_tfonce=$cpunum+1;
	$max_orange=$cpunum*0.75;
	$max_over=$cpunum*2;
	$purc1=$load/$cpunum;
	$pourc=round($purc1*100,2);
	$color="#5DD13D";
	if($load>=$max_orange){
		$color="#F59C44";
	}
	
	if($load>$max_vert_fonce){
		$color="#C5792D";
	}

	if($load>$max_vert_tfonce){
		$color="#83501F";
	}	
	

	
	if($load>=$max_over){
		$color="#640000";
		$text="<br>".texttooltip("{overloaded}","{overloaded}","Loadjs('overloaded.php')",null,0,"font-size:9px;font-weight:bold;color:red");
	}

	$this->meta_array["LOAD"]["CPU_NUMBER"]=$cpunum;
	$this->meta_array["LOAD"]["POURC"]=$pourc;
	$this->meta_array["LOAD"]["COLOR"]=$color;
	$this->meta_array["LOAD"]["MAXOVER"]=$max_over;
	$this->meta_array["LOAD"]["ORG_LOAD"]=$org_load;

	if($pourc>100){$pourc=100;}
	
	
if($tiny==1){
return "
<tr>
	<td width=1% nowrap><strong style='font-size:12px'>{load_avg}:</strong></td>
</tr>
<tr>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
			<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
				<span style='color:white;font-size:11px;font-weight:bold'>$pourc%</span>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td width=1% nowrap><strong>{load}: $org_load&nbsp;[$cpunum cpu(s)]$text</strong></td>
</tr>";		
	
}	

return "
<tr>
	<td width=1% nowrap class=legend nowrap>{load_avg}:</strong></td>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
			<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
				<span style='color:white;font-size:11px;font-weight:bold'>$pourc%</span>
			</div>
		</div>
	</td>
	<td width=1% nowrap><strong>{load}: $org_load&nbsp;[$cpunum cpu(s)]$text</strong></td>
</tr>";	
	
	 
	
	
}

function MinimalStatus(){
	$hash_mem=$this->realMemory();
	$this->html_load_usage();
	$mem_used_p=$hash_mem["ram"]["percent"];
	$mem_used_kb=FormatBytes($hash_mem["ram"]["used"]);
	$total=FormatBytes($hash_mem["ram"]["total"]);
	$swapar=$this->swap();
	
if(is_array($swapar)){
		$swap_color="rgb(93, 209, 61)";
		$swap_text="<br><span style='font-size:9px'>swap: {$swapar[0]}% {used}</span>";
		if($swapar[0]>30){
			$swap_color="#F59C44";
		}
		if($swapar[0]>50){
			$swap_color="#D32D2D";
		}	
		
		$swap="<div style=\"border: 1px solid $swap_color; width: 100px; background-color: white; padding-left: 0px; margin-top: 3px;\" ". CellRollOver($swap_js).">
				<div style=\"width: {$swapar[0]}px; text-align: center; color: white; padding-top: 3px; padding-bottom: 3px; background-color:$swap_color;\"> </div>
		</div>";
	}	
	
	$color="#5DD13D";
	if($mem_used_p>70){$color="#F59C44";}
	if($mem_used_p>79){$color="#D32D2D";}
	
	$html="
<table style='width:100%' class=form>
	<tr>
	<td width=1% nowrap><strong style='font-size:12px'>{memory_usage}:</strong></td>
	</tr>
	<tr>
		<td align='left'>
			<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
				<div style='width:{$mem_used_p}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'><strong>{$mem_used_p}%</strong></div>
			</div>
			$swap
		</td>
	</tr>
		<td width=1% nowrap><strong>$mem_used_kb/$total$swap_text</strong></td>
	</tR>	
	
	</tr>
	<tr><td>&nbsp;</td>
	". $this->html_load_usage(0,1)."
</table>";
		
	return $html;
	
}
  

function html_Memory_usage(){
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	$hash_mem=$this->realMemory();
	$this->html_load_usage();
	$mem_used_p=$hash_mem["ram"]["percent"];
	$mem_used_kb=FormatBytes($hash_mem["ram"]["used"]);
	$total=FormatBytes($hash_mem["ram"]["total"]);
	$INODES_DEV=array();
	$this->meta_array["MEM"]["MEM_USED_POURC"]=$mem_used_p;
	$this->meta_array["MEM"]["MEM_USED_KB"]=$hash_mem["ram"]["used"];
	$this->meta_array["MEM"]["TOTAL"]=$hash_mem["ram"]["total"];
	
	
	$sock=new sockets();
	$hdparms=unserialize(base64_decode($sock->GET_INFO("HdparmInfos")));
	
	$users=new usersMenus();
	$Disks=$users->disks_size;
	$inodes=$users->disks_inodes;
	
	if($inodes<>null){
		
		$tbl=explode(";",$inodes);
		
		while (list ($num, $val) = each ($tbl) ){
			$val=trim($val);
			if(trim($val)==null){continue;}
			$values=explode(":",$val);
			$INODES_DEV[$values[0]]=array("POURC"=>$values[4],"OCC"=>$values[2],"SIZE"=>$values[1]);
		}
		
	}
	
	
	
	
	if($Disks<>null){
		
		$tbl=explode(";",$Disks);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$values=explode(",",$val);
				if(is_array($values)){
					$dc=$dc+1;
					$disk=$values[0];
					$disk=str_replace("mapper/","",$disk);
					$disk_name=$disk;
					$size=$values[1];
					$occ=$values[2];
					$disp=$values[3];
					$pourc=$values[4];
					$label=base64_decode($sock->getFrameWork("cmd.php?disk-get-label=/dev/$disk_name"));
									
					
					if(!$array_dup[$disk]){$array_dup[$disk]=true;}else{continue;}
					if(preg_match("#([a-z\_\-]+)[0-9]+$#",$disk_name,$rt)){$disk_name=$rt[1];}
					
					$this->meta_array["DISKS"]["$disk_name"]["LABEL"]=$label;
					$this->meta_array["DISKS"]["$disk_name"]["SIZE"]=$size;
					$this->meta_array["DISKS"]["$disk_name"]["OCC"]=$occ;
					$this->meta_array["DISKS"]["$disk_name"]["DISP"]=$disp;
					$this->meta_array["DISKS"]["$disk_name"]["POURC"]=$pourc;
					
					
					if($hdparms[$disk_name]<>null){
						$hdparms_info=intval($hdparms[$disk_name]);
						$hdparms_color="#5DD13D";
						
						$this->meta_array["DISKS"]["$disk_name"]["hdparms_info"]=$hdparms_info;
						
						if($hdparms_info>100){$hdparms_text_add="&nbsp;|{$hdparms_info}Mbs/s";$hdparms_info=100;}
						if($hdparms_info<70){$hdparms_color="#F59C44";}
						if($hdparms_info<31){$hdparms_color="#D32D2D";}
						 $pouc_hdparm_text=texttooltip("{performance}:$hdparms_info%$hdparms_text_add","<strong>{performance}:$hdparms_info%</strong><hr><br>{hdparm_infos_text}",null,null,0,"font-size:9px");
						 $this->meta_array["DISKS"]["$disk_name"]["HDPARM_COLOR"]=$hdparms_color;
							$hdparm="
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $hdparms_color;margin-top:3px'>
								<div style='width:{$hdparms_info}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$hdparms_color'>
									
								</div>
							</div>
							";						
					}
					
					if(isset($INODES_DEV[$disk]["POURC"])){
						$pourc_inodes=intval($INODES_DEV[$disk]["POURC"]);
						$pourc_inodes_text=$pourc_inodes."%";
						
						$color="#5DD13D";
						if($pourc_inodes>40){$color="#F59C44";}
						if($pourc_inodes>90){$color="#D32D2D";}
							
						//$this->meta_array["DISKS"]["$disk_name"]["pouc_disk_io"]=$pouc_disk_io;
						//$this->meta_array["DISKS"]["$disk_name"]["pouc_disk_io_color"]=$color;
							
							$pouc_disk_inodes_text="<div style='font-size:9px;margin-top:1px'>% {files} {use}:$pourc_inodes%</div>";
							$inodesstat="
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
								<div style='width:{$pourc_inodes}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									
								</div>
							</div>
							";				
					}					
					
					
					if($users->IOSTAT_INSTALLED){
						
						$pouc_disk_io=$users->IOSTAT_ARRAY[trim($disk)];
						
						if($pouc_disk_io<>null){
							$pouc_disk_io=intval($pouc_disk_io);
							$pouc_disk_io_text=$pouc_disk_io."%";
							$color="#5DD13D";
							if($pouc_disk_io>40){$color="#F59C44";}
							if($pouc_disk_io>90){$color="#D32D2D";}
							
							$this->meta_array["DISKS"]["$disk_name"]["pouc_disk_io"]=$pouc_disk_io;
							$this->meta_array["DISKS"]["$disk_name"]["pouc_disk_io_color"]=$color;
							
							$pouc_disk_io_text="<br><span style='font-size:9px'>% i/o {use}:$pouc_disk_io%</span>";
							$iostat="
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color;margin-top:3px'>
								<div style='width:{$pouc_disk_io}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									
								</div>
							</div>
							";
						}
					}
					$color="#5DD13D";
					if($pourc>70){$color="#F59C44";}
					if($pourc>90){$color="#D32D2D";}
					$disk_text=$disk;
					if($label<>null){$disk_text="$label ($disk)";}
					$this->meta_array["DISKS"]["$disk_name"]["COLOR"]=$color;
					$dd=$dd."
					<tr>
						<td nowrap align=right width=90px valign='top'><strong>$disk_text:</strong></td>
						
						<td>
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
								<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									<strong>{$pourc}%</strong>
								</div>
							</div>
							$inodesstat
							$iostat
							$hdparm
						</td>
						<td width=1% nowrap style='font-weight:bold'><div style='margin-top:10px;font-weight:bold'>{$occ}G/{$size}G&nbsp;</div>$pouc_disk_inodes_text$pouc_disk_io_text$pouc_hdparm_text</strong></td>
						
					</tr>";
				}
			}
			
		}
	}
	
	$color="#5DD13D";
	if($mem_used_p>70){$color="#F59C44";}
	if($mem_used_p>79){$color="#D32D2D";}
	$this->meta_array["MEM"]["COLOR"]=$color;
					
	if($users->DSTAT_INSTALLED){
		$mem_js="Loadjs('dstat.memory.php')";
		$mem_cpu="Loadjs('dstat.cpu.php')";
		$sock=new sockets();
		$lastcpu=trim($sock->getfile('dstatlastcpu'));
		if(preg_match('#.+?\|\s+([0-9]+)\s+#',$lastcpu,$re)){
			if($re[1]>90){
				$cpu_color="#D32D2D";
			}else{
				$cpu_color="#5DD13D";
			}
			$cpu="<tr ". CellRollOver($mem_cpu).">
				<td width=1% nowrap class=legend nowrap>{cpu_usage}:</strong></td>
				<td align='left'>
					<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $cpu_color'>
						<div style='width:{$re[1]}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$cpu_color'><strong>{$re[1]}%</strong></div>
					</div>
				</td>
				<td width=1% nowrap><strong style='color:$cpu_color'>{$re[1]}%</strong></td>
				</tr>";
		}
		
		
	}else{
		$mem_js="LoadTaskManager();";
	}
	
	$swap_js="Loadjs('swap.status.php');";
	$swapar=$this->swap();
	if(is_array($swapar)){
		$swap_color="rgb(93, 209, 61)";
		$swap_text="<br><span style='font-size:9px'>swap: {$swapar[0]}% {used}</span>";
		if($swapar[0]>30){
			$swap_color="#F59C44";
		}
		if($swapar[0]>50){
			$swap_color="#D32D2D";
		}	
		$this->meta_array["SWAP_POURC"]=$swapar[0];
		$this->meta_array["SWAP_COLOR"]=$swap_color;
		
		$swap="<div style=\"border: 1px solid $swap_color; width: 100px; background-color: white; padding-left: 0px; margin-top: 3px;\" ". CellRollOver($swap_js).">
				<div style=\"width: {$swapar[0]}px; text-align: center; color: white; padding-top: 3px; padding-bottom: 3px; background-color:$swap_color;\"> </div>
		</div>";
	}
	
	$bandwith=@file_get_contents(dirname(__FILE__)."/logs/web/bandwith-mon.txt");
	if($bandwith>0){
		$this->meta_array["BANDWITH"]=$bandwith;
		$convert=FormatBytes($bandwith);
		if($bandwith>10000){$bandwith=10000;}
		$bandwith_color="#5DD13D";
		if($bandwith<5000){$bandwith_color="#F59C44";}
		if($bandwith<2000){$bandwith_color="#D32D2D";}
		$pourc=$bandwith/100;
		$this->meta_array["BANDWITH_POURC"]=$pourc;
		$this->meta_array["BANDWITH_COLOR"]=$bandwith_color;
		
		$bandwith_js="Loadjs('bandwith.stats.php')";
		
		$bb="<tr ". CellRollOver($bandwith_js).">
				<td width=1% nowrap class=legend nowrap><strong style='font-size:10px'>{bandwith}:</strong></strong></td>
				<td align='left'>
					<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $bandwith_color'>
						<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$bandwith_color'><strong>$convert/s</strong></div>
					</div>
				</td>
				<td width=1% nowrap><strong style='color:$bandwith_color'>$convert/s</strong></td>
				</tr>";
		
		
	}
	
	
	
	$html="<table style='width:100%'>
	<tr ". CellRollOver().">
	<td width=1% nowrap class=legend nowrap>{memory_usage}:</strong></td>
	<td align='left'>
		<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color' ". CellRollOver($mem_js).">
			<div style='width:{$mem_used_p}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'><strong>{$mem_used_p}%</strong></div>
		</div>
		$swap
	</td>
	<td width=1% nowrap><strong>$mem_used_kb/$total$swap_text</strong></td>
	</tr>
	". $this->html_load_usage()."
	$cpu
	$dd
	$bb
	</table>
	";	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function disk_type_array(){
	return array("1"=>"DOS 12-bit FAT","9"=>"AIX bootable","80"=>"Old MINIX","c7"=>"Syrinx",
   				"2"=>"XENIX root","a"=>"OPUS","81"=>"Linux/MINIX","db"=>"CP/M",
   				"3"=>"XENIX usr","40"=>"Venix 80286","82"=>"Linux swap","e1"=>"DOS access",
   				"4"=>"DOS 16-bit <32M", "51"=>"Novell?","83"=>"Linux native","e3"=>"DOS R/O",
   				"5"=>"Extended","52"=>"Microport","93"=>"Amoeba","f2"=>"DOS secondary",
   				"6"=>"DOS 16-bit >=32M","63"=>"GNU HURD","94"=>"Amoeba BBT","ff"=>"BBT",
   				"7"=>"OS/2 HPFS","64"=>"Novell","b7"=>"BSDI fs","8"=>"AIX","75"=>"PC/IX","b8"=>"BSDI swap");
 }
 
 
 function usb_parse_array($array,$internal_hd=true){
 		include_once(dirname(__FILE__) . "/class.samba.inc");
 		$samba=new samba();
 	
		$path=trim($array["PATH"]);
		$LABEL=trim($array["LABEL"]);
		$TYPE=trim($array["TYPE"]);
		$SEC_TYPE=trim($array["SEC_TYPE"]);
		$mounted=trim($array["mounted"]);
		$UUID=$array["UUID"];
		$ID_MODEL=$array["ID_MODEL"];
		$imgs="usb-64.png";
		if($mounted=='/'){return null;}
		
		if(!is_array($_GLOBAL["disks_list"])){
			if(is_file(dirname(__FILE__).'/usb.scan.inc')){
				include dirname(__FILE__).'/usb.scan.inc';
				if(is_array($_GLOBAL["disks_list"]["$path"])){
					$ID_MODEL=$_GLOBAL["disks_list"]["$path"]["ID_MODEL"];
				}
			}	
		}
		
		
if(preg_match("#(.+?)[0-9]+$#",$path,$ri)){
if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"])){
		if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"]["PARTITIONS"]))
		$imgs="usb-disk-64.png";
	}
}
if($vendor<>null){
		$text_vendor="		
		<tr>
			<td width=1% valign='top' class=legend nowrap>{vendor}:</strong></td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$vendor</code></td>
		</tr>
		";};
		
		if(strlen($manufacturer)>2){
		$text_manufacturer="
		<tr>
			<td width=1% valign='top' class=legend nowrap>{manufacturer}:</td>
			<td width=99% valign='top' align='left'><code style='font-size:11px'>$manufacturer</code></td>
		</tr>	
		";};		
		
	
		
 		if(preg_match("#(.+?);(.+?);(.+?);([0-9]+)%#",$array["SIZE"],$re)){
	 		$size=$re[1];
	 		$pourc=$re[4];
			}
			
		if($LABEL==null){if($path<>null){$title="$path";}}else{$title="$LABEL";}
		
 	if(($mounted==null) && ($size==null)){
		$error=true;
		if($TYPE==null){$TYPE=$array["ID_FS_TYPE"];}	
		$title_mounted="<span style='color:red;font-size:9px'>{error_not_mounted}</span>";
			$umount="
				<tr>
				<td align='right' >" . imgtootltip('fw_bold.gif','{mount}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				<td style='font-size:12px'>". texttooltip('{mount}','{mount_explain}',"Loadjs('usb.index.php?mount=yes&uuid=$UUID&mounted=$path&type=$TYPE')")."</td>
				</tr>";				
		
	}			
			
 		if(strlen($mounted)>30){$mounted=texttooltip(substr($mounted,0,27)."...",$mounted,null,null,1,'font-size:11px');}
		$folder_name=$samba->GetShareName("/media/$UUID");
		if($folder_name<>null){$imgs="usb-share-64.png";}
		$html="
		<table style='width:247px;margin:3px;border:1px dotted #CCCCCC'>
			<tr ". CellRollOver("Loadjs('usb.index.php?uuid-infos=$UUID');").">
			<td valign='top'>
				<table style='width=100%'>
					<tr>
						<td valign='top' width=1%>" . imgtootltip($imgs,"{parameters}","Loadjs('usb.browse.php?uuid=$UUID');")."</td>
						<td valign='top'>
									<span style='font-weight:bold;font-size:14px;'>$title</span>
									<table style='width:100%'>
										<tr>
										<td valign='top'>
											$title_mounted
											<span style='font-size:11px;text-transform:italic'>$ID_MODEL</span>
											<br>
											<code>{mounted}:$mounted</code>
										</td>
									</tr>
									</table>
						
							</td>
					</tr>		
				</table>
			</td>
		</tr>
		</table>
		";
 	return $html;
			

 }



	
}

?>