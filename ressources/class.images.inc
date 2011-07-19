<?php


class images{
	var $path;
	var $imgPath="/opt/artica/share/www/jpegPhoto";
	var $mustInstall=false;
	var $missing_module='';
	var $array_type=array(1=>"gif",2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'bmp',7=>'tiff',8=>'tiff',9=>'jpc',10=>'jp2',11=>'jpx');
	
	function images($path){
		$this->path=$path;
		if(!function_exists('imagecreatefrompng')){
			writelogs("FATAL ERROR imagecreatefrompng does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->missing_module='imagecreatefrompng';
			$this->mustInstall=True;
			}
		
		
		
		
	}
	
	
	
	function thumbnail($img_max_width, $img_max_height,$target_path) {
  		if($this->mustInstall){return null;}
  		$file = realpath($this->path); 
  		$dir = dirname($this->path).'/'; 
  		$img_infos = getimagesize($file); 
  		
  		if(!is_array($img_infos)){
  			
  			writelogs("FATAL ERROR This is not an image file....",__FUNCTION__.'/'.__CLASS__,__FILE__);
  			writelogs("The file uploaded is \"$file\" in directory \"$dir\"",__FUNCTION__.'/'.__CLASS__,__FILE__);
  			writelogs("Tried to create thumbnail image_max_width=$img_max_height, img_max_height=$img_max_height target_path=$target_path",
  			__FUNCTION__.'/'.__CLASS__,__FILE__);
  			writelogs("No Thumbnail can be created assume the original picture..",__FUNCTION__.'/'.__CLASS__,__FILE__);
  			
  			$inf=@gd_info();
  			if(!is_array($inf)){
  				writelogs("It seems that GD library is not installed, please install it to have full feature",__FUNCTION__.'/'.__CLASS__,__FILE__);
  			}else{
  				while (list ($num, $ligne) = each ($inf) ){
  					writelogs("$num: $ligne",__FUNCTION__.'/'.__CLASS__,__FILE__);
  				}
  			}
  			
  			copy($this->path,$target_path);
  			return true;
  		}
  		
  		$img_width = $img_infos[0]; 
  		$img_height = $img_infos[1]; 
  		$img_type = $img_infos[2];
        $img_thumb_name=$target_path;
        
        
  
  if ($img_max_width > $img_width) {$img_max_width = $img_width;}
  
  if ($img_max_height > $img_height) {$img_max_height = $img_height;}
  	
    $img_thumb_fact_width_height = $img_max_width / $img_max_height;
  	$img_fact_width_height = $img_width / $img_height;
  
  
  if ($img_thumb_fact_width_height < $img_fact_width_height) {
    $img_thumb_width  = $img_max_width; 
    $img_thumb_height = $img_thumb_width / $img_fact_width_height; 
  } else {
    $img_thumb_height = $img_max_height;  
    $img_thumb_width  = $img_thumb_height * $img_fact_width_height; 
  }
  
  
   if (is_file($img_thumb_name) ) {return $img_thumb_name;}    
 
  writelogs("$img_thumb_name does not exists, create thumbnail, type=$img_type",__CLASS__.'/'.__FUNCTION__,__FILE__);
  switch ($img_type) {
  	case 1:
  		return $this->createthumb($this->path, $target_path,$img_max_width,$img_max_height,true, true, false);
  		break;
  	case 2:
	  $img = imagecreatefromjpeg($file);
      $img_ext = '.jpg'; // Extension de l'image
      break;
    case 3:
     return $this->createthumb($this->path, $target_path,$img_max_width,$img_max_height,true, true, false);
  	 break;
      
  }
 
  
  if($img_ext==".gif"){
  	
  	
  }
  
  
  
  writelogs("img_thumb_width=$img_thumb_width, img_thumb_height=$img_thumb_height,img_height=$img_height",__CLASS__.'/'.__FUNCTION__,__FILE__);
  $img_thumb = imagecreatetruecolor($img_thumb_width, $img_thumb_height);
  if(!$img_thumb){
  	writelogs("imagecreatetruecolor return false, assume the original picture...",__CLASS__.'/'.__FUNCTION__,__FILE__);
  	copy($this->path,$target_path);
  	return true;
  }
 if(!@imagecopyresized($img_thumb, $img, 0, 0, 0, 0, $img_thumb_width,
                                                 $img_thumb_height,
                                                 $img_width,
                                                 $img_height)){
                                                 	writelogs("imagecopyresized return false, assume the original picture...",__CLASS__.'/'.__FUNCTION__,__FILE__);
  													copy($this->path,$target_path);
  													return true;
                                                 }


  $img_thumb_name = $target_path;
  
  switch($img_type){
  	case 1:
  		imagegif($img_thumb, $img_thumb_name);
  		break;
  	
  	
    case 2:
      imagejpeg($img_thumb, $img_thumb_name);
      break;
    case 3:
      imagepng($img_thumb, $img_thumb_name); 
  }
  if(file_exists($img_thumb_name)){
  	return true;
  }

	return false;
}


function createthumb($name, $newname, $new_w, $new_h, $border=false, $transparency=true, $base64=false) {
    if(file_exists($newname)){return true;}
    if(!file_exists($name)){
    	writelogs("$name does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
        return false;
    }

    
		$img_infos = getimagesize($name); 
  		$img_type = $img_infos[2];
		$img_width = $img_infos[0]; 
  		$img_height = $img_infos[1];   		
  		
		$ext=$this->array_type[$img_type];
		writelogs("$name $ext width=$img_width height=$img_height",__CLASS__.'/'.__FUNCTION__,__FILE__);
    	
		

    if($ext=="jpeg" || $ext=="jpg"){
        $img = @imagecreatefromjpeg($name);
    } elseif($ext=="png"){
        $img = @imagecreatefrompng($name);
    } elseif($ext=="gif") {
        $img = @imagecreatefromgif($name);
    }
    if(!$img){
    	writelogs("imagecreatefrom* return false",__CLASS__.'/'.__FUNCTION__,__FILE__);
    	return false;
    }
        
    $old_x = imageSX($img);
    $old_y = imageSY($img);
    if($old_x < $new_w && $old_y < $new_h) {
        $thumb_w = $old_x;
        $thumb_h = $old_y;
    } elseif ($old_x > $old_y) {
        $thumb_w = $new_w;
        $thumb_h = floor(($old_y*($new_h/$old_x)));
    } elseif ($old_x < $old_y) {
        $thumb_w = floor($old_x*($new_w/$old_y));
        $thumb_h = $new_h;
    } elseif ($old_x == $old_y) {
        $thumb_w = $new_w;
        $thumb_h = $new_h;
    }
    $thumb_w = ($thumb_w<1) ? 1 : $thumb_w;
    $thumb_h = ($thumb_h<1) ? 1 : $thumb_h;
    $new_img = ImageCreateTrueColor($thumb_w, $thumb_h);
   
    if($transparency) {
    	writelogs("There is a transparency on this picture",__CLASS__.'/'.__FUNCTION__,__FILE__);
        if($ext=="png") {
            imagealphablending($new_img, false);
            $colorTransparent = imagecolorallocatealpha($new_img, 0, 0, 0, 127);
            imagefill($new_img, 0, 0, $colorTransparent);
            imagesavealpha($new_img, true);
        } elseif($ext=="gif") {
            $trnprt_indx = imagecolortransparent($img);
            if ($trnprt_indx >= 0) {
                //its transparent
                $trnprt_color = imagecolorsforindex($img, $trnprt_indx);
                $trnprt_indx = imagecolorallocate($new_img, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                imagefill($new_img, 0, 0, $trnprt_indx);
                imagecolortransparent($new_img, $trnprt_indx);
            }
        }
    } else {
        Imagefill($new_img, 0, 0, imagecolorallocate($new_img, 255, 255, 255));
    }
   
    imagecopyresampled($new_img, $img, 0,0,0,0, $thumb_w, $thumb_h, $old_x, $old_y);
    if($border) {
        $black = imagecolorallocate($new_img, 0, 0, 0);
        imagerectangle($new_img,0,0, $thumb_w, $thumb_h, $black);
    }
    
    writelogs("Create image $newname, width=$thumb_w, height=$thumb_h",__CLASS__.'/'.__FUNCTION__,__FILE__);
    
    if($base64) {
        ob_start();
        imagepng($new_img);
        $img = ob_get_contents();
        ob_end_clean();
        $return = base64_encode($img);
    } else {
        if($ext=="jpeg" || $ext=="jpg"){
            imagejpeg($new_img, $newname);
            $return = true;
        } elseif($ext=="png"){
            imagepng($new_img, $newname);
            $return = true;
        } elseif($ext=="gif") {
            imagegif($new_img, $newname);
            $return = true;
        }
    }
    imagedestroy($new_img);
    imagedestroy($img);
    return $return;
}


	
	
	
	
	
}