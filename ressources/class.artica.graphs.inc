<?php
	include_once (dirname(__FILE__) .'/jpgraph-3/src/jpgraph.php');
	include_once (dirname(__FILE__) .'/jpgraph-3/src/jpgraph_line.php');
	include_once (dirname(__FILE__) .'/jpgraph-3/src/jpgraph_pie.php');
	include_once (dirname(__FILE__) .'/jpgraph-3/src/jpgraph_pie3d.php');
	include_once (dirname(__FILE__) .'/class.templates.inc');
	
	
class artica_graphs{
	var $Area;
	var $ydata=array();
	var $xdata=array();
	var $ydata2=array();
	var $xdata2=array();	
	var $title;
	var $x_title;
	var $y_title;
	var $width=500;
	var $height=200;
	var $filename;
	var $file_ttl;
	var $Fillcolor="green@0.5";
	var $color="005447";
	var $ViewValues=true;
	var $imageMapContent=null;
	var $ImageMap=null;
	var $ImageMapId;
	var $RedAreas;
	var $margin0=false;
	var $PieExplode=10;
	var $PieLegendHide=false;
	var $LineLegend=null;
	var $LineLegend2=null;
	
	function artica_graphs($filename=null,$max_ttl=null){
		$this->filename=$filename;
		$this->file_ttl=$max_ttl;
		
		
	}
	
	function checkfile(){
	    if(!is_file($this->filename)){return true;}
		$time=file_get_time_min($this->filename);
			
		if($time>$this->file_ttl){
			if(posix_getuid()==0){echo "$this->filename -> $time must higer than $this->file_ttl (TRUE)\n";}
			writelogs("$this->filename -> $time must higer than $this->file_ttl (TRUE)",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return true;
		}
		if(posix_getuid()==0){echo "$this->filename -> $time must higer than $this->file_ttl (FALSE)\n";}
		writelogs("$this->filename -> $time must higer than $this->file_ttl (FALSE)",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return false;	
	}
	
	function AddArea($min,$max,$aFilled,$aColor){
		$this->Area=array($min,$max,$aFilled,$aColor);
		
	}
	
	
	function line_green(){
		$tpl=new templates();
		writelogs("$this->title: Start ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_file($this->filename)){@unlink($this->filename);}
		$title= $tpl->_ENGINE_parse_body($this->title);
		$YNAME=$tpl->_ENGINE_parse_body($this->y_title);
		$XNAME=$tpl->_ENGINE_parse_body($this->x_title);
		$xdata=$this->xdata;
		$ydata=$this->ydata;
		writelogs("$title: Y DATA: ".count($this->ydata)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);	
		writelogs("$title: X DATA: ".count($this->xdata)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$graph = new Graph($this->width,$this->height);
		try{
		$graph->img->SetColor("red@0.9");
		$graph->SetMarginColor('#FFFFFF');
		$graph->SetScale('textlin');
		$graph->title->Set($title);
		$graph->title->SetColor("#".$this->color);
		$graph->xaxis->title->Set($XNAME);
		$graph->xaxis->SetTickLabels($xdata);
		$graph->yaxis->title->Set($YNAME);
		$graph->yaxis->scale->SetGrace(10);
		if(!$this->margin0){
			$graph->SetMargin(55,20,60,20);
		}else{
			$graph->SetMargin(34,0,0,0);
		}
		$graph->xaxis->SetColor('black');
		$graph->yaxis->SetColor('black');
		$graph->xgrid->Show();
		
		
		$lineplot=new LinePlot($ydata);
		$lineplot->SetWeight(2);
		$lineplot->SetColor("#".$this->color);
		$lineplot->SetFillColor($this->Fillcolor);
		$lineplot->SetFillFromYMin();
		$lineplot->SetWeight (3 ); 
		$lineplot->SetFilled(true);
		$lineplot->SetFillFromYMin(true);
		
		if(is_array($this->RedAreas)){
			while (list ($index, $line) = each ($this->RedAreas) ){
				$lineplot->AddArea($line[0],$line[1],LP_AREA_FILLED,"#BE0000");
			}
			
		}
		
		$graph->Add($lineplot);
		JpGraphError::SetImageFlag(false);
		$graph->Stroke("$this->filename");

		$gdImgHandler = $graph->Stroke($this->filename);
		} catch ( JpGraphException $e ) {
			if(posix_getuid()==0){
				echo "$title: Fatal Error ". $e->getMessage()."\n";}
				writelogs("$title: Fatal Error " . $e->getMessage()."\n"
				,__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		writelogs("$title: Writing $this->filename ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		@chmod($this->filename,0755);
			
	}
	
	function line_green_double(){
		$tpl=new templates();
		writelogs("$this->title: Start ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_file($this->filename)){@unlink($this->filename);}
		$title= $tpl->_ENGINE_parse_body($this->title);
		$YNAME=$tpl->_ENGINE_parse_body($this->y_title);
		$XNAME=$tpl->_ENGINE_parse_body($this->x_title);
		$xdata=$this->xdata;
		$ydata=$this->ydata;
		
		$xdata2=$this->xdata2;
		$ydata2=$this->ydata2;
		
		writelogs("$title: Y DATA: ".count($this->ydata)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);	
		writelogs("$title: X DATA: ".count($this->xdata)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$graph = new Graph($this->width,$this->height);
		try{
		$graph->img->SetColor("red@0.9");
		$graph->SetMarginColor('#FFFFFF');
		$graph->SetScale('textlin');
		$graph->title->Set($title);
		$graph->title->SetColor("#".$this->color);
		$graph->xaxis->title->Set($XNAME);
		$graph->xaxis->SetTickLabels($xdata);
		$graph->yaxis->title->Set($YNAME);
		$graph->yaxis->scale->SetGrace(10);
		if(!$this->margin0){
			$graph->SetMargin(55,20,60,20);
		}else{
			$graph->SetMargin(34,0,0,0);
		}
		$graph->xaxis->SetColor('black');
		$graph->yaxis->SetColor('black');
		$graph->xgrid->Show();
		
		
		$lineplot=new LinePlot($ydata);
		$lineplot->SetWeight(2);
		$lineplot->SetColor("#".$this->color);
		$lineplot->SetFillColor($this->Fillcolor);
		$lineplot->SetFillFromYMin();
		$lineplot->SetWeight (3 ); 
		$lineplot->SetFilled(true);
		$lineplot->SetFillFromYMin(true);
		if($this->LineLegend<>null){$lineplot->SetLegend($this->LineLegend);}
		
		
		$lineplot2=new LinePlot($ydata2);
		$lineplot2->SetWeight(2);
		$lineplot2->SetColor("#FF1493");
		$lineplot2->SetFillColor("#FF87C8@0.4");
		$lineplot2->SetFillFromYMin();
		$lineplot2->SetWeight (3 ); 
		$lineplot2->SetFilled(true);
		$lineplot2->SetFillFromYMin(true);	
		if($this->LineLegend2<>null){$lineplot2->SetLegend($this->LineLegend2);}	
		
		if(is_array($this->RedAreas)){
			while (list ($index, $line) = each ($this->RedAreas) ){
				$lineplot->AddArea($line[0],$line[1],LP_AREA_FILLED,"#BE0000");
			}
			
		}
		
		$graph->Add($lineplot);
		$graph->Add($lineplot2);
		JpGraphError::SetImageFlag(false);
		$graph->Stroke("$this->filename");

		$gdImgHandler = $graph->Stroke($this->filename);
		} catch ( JpGraphException $e ) {
			if(posix_getuid()==0){
				echo "$title: Fatal Error ". $e->getMessage()."\n";}
				writelogs("$title: Fatal Error " . $e->getMessage()."\n"
				,__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		writelogs("$title: Writing $this->filename ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		@chmod($this->filename,0755);
			
	}	
	
function pie(){
	$tpl=new templates();
	$graph = new PieGraph($this->width,$this->height);
	$targetArray=array();
	$altArray=array();
	//$graph->SetShadow();
	
	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$title= $tpl->_ENGINE_parse_body($this->title);
	writelogs("$title:  ".count($this->xdata)." lines ".@implode(";", $this->xdata),__CLASS__.'/'.__FUNCTION__,__FILE__);
	krsort($this->xdata);
	krsort($this->ydata);
	
	
	$p1 = new PiePlot3D($this->xdata);
	try{	
		while (list ($num, $val) = each ($this->ydata) ){
			$SetLabels[]="{$this->xdata[$num]} $val ";
			$targetArray[]="$this->ImageMap".urlencode($val)."');";
			$altArray[]=$val;
		}
		
		
	reset($this->xdata);
	reset($this->ydata);

	
	
	//$p1->SetSliceColors(array(136,34,40,45,46,62,63,134,74,10,120,136,141,168,180,77,209,218,346,395,89,430));
	$p1->SetTheme("earth"); 
	if(!$this->ViewValues){
		$p1->SetLabels($SetLabels); 
		$p1->value->Show(false);
	}
	
	$p1->SetLegends($SetLabels); 
	$p1->SetEdge('black',1); 
	$p1->SetAngle(75); 
	//$p1->SetSize(0.5);
	$p1->SetCenter(0.5,0.44);
	
	
	//$p1->SetLabelMargin(2); 
	//$p1->SetLabelType(0);
	//$p1->SetCenter(0.4,0.5);
	$p1->ExplodeAll($this->PieExplode);
	if(!$this->PieLegendHide){
	$graph->legend->SetPos(0.5,0.95,'center','bottom');
	$graph->legend->SetShadow('#fafafa',0);
	$graph->legend->SetFrameWeight(1);
	$graph->legend->SetFillColor('#fafafa');
	$graph->legend->SetColumns(2);
	}else{
		$graph->legend->Hide();
	}
	if($this->ImageMap<>null){
		$p1->SetCSIMTargets($targetArray,$altArray);
	}
	
	$graph->Add($p1);
	
	
	$graph->SetFrame(false); 
	
	
	JpGraphError::SetImageFlag(false);
	@unlink($this->filename);
	$graph->Stroke("$this->filename");
	$gdImgHandler = $graph->Stroke($this->filename);
	if($this->ImageMap<>null){
		$this->imageMapContent=$graph ->GetHTMLImageMap (md5($this->filename));
		$this->ImageMapId=md5($this->filename);
	}
		} catch ( JpGraphException $e ) {
			if(posix_getuid()==0){echo "$title: Fatal Error ". $e->getMessage()."\n";}
			writelogs("$title: Fatal Error " . $e->getMessage()."\n"
			,__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		writelogs("$title: Writing $this->filename ",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	
	}
	
	
	
}
?>