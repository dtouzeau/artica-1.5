<? 

class msg_DecodeMessage{ 
    var $header; 
    var $body; 
    var $fullmessage; 
    var $auto_decode = true; 
    var $attachment_path = "attachments"; 
    var $choose_best = true; 
    var $best_format = "text/html"; 
     
    function InitHeaderAndBody($header, $body) { 
        $this->header = $header; 
        $this->body = $body; 
        $this->fullmessage = chop($header)."\t\n\t\n".ltrim($body); 
    } 
    function Body() { 
        return trim($this->body); 
    } 
    function InitMessage($msg) { 
        $i = 0; 
        $m = ""; 
        $messagebody = ""; 
        $line = explode("\n",trim($msg));     
        for ($j=0;$j<count($line);$j++) {             
            if (chop($line[$j]) == ""  AND $i < 1) : 
                $this->header = $m; 
                $i++; 
            endif; 
            if ($i > 0) 
                $messagebody .= $line[$j]."\n";     
            $m .= $line[$j]."\n"; 
        } 
        $this->body = $messagebody; 
        $this->fullmessage = $msg; 
    } 

    function Headers($field="") { 
        if ($field == "") : 
            return $this->header; 
        else : 
            $hd = ""; 
            $field = $field.":"; 
            $start = 0; 
            $j=0; 
            $header = eregi_replace("\r", "\n", $this->header); 
            $p = explode("\n", $header); 
            do { 
                for ($i=$start;$i<count($p);$i++) { 
                    if (ereg("^($field)", $p[$i]))  : 
                          $position = $i; 
                          $hd .= ereg_replace("$field", "",$p[$i]); 
                          break; 
                      endif; 
                } 
                if (ereg("^($field)", $p[$i]))  :          
             
                    for ($i=$position+1;$i<count($p);$i++) { 
                        $tok = strtok($p[$i], " "); 
                        if (ereg(":$", $tok) AND (!(eregi("^($field)", $tok))))   
                            break; 
                        $hd .= ereg_replace("$field", "",$p[$i]); 
                    } 
                    $start=$i+1;     
                endif;     
            } while ($j++ < count($p)); 
        return $hd; 
        endif;         
    } 

    function ContentType() { 
        $c = $this->Headers("Content-Type"); 
        $ct = ereg_replace("[[:space:]]", "", $c); 
        if (!(ereg(";", $ct))) : 
            $content["type"] = trim($ct); 
        else :         
            $p = explode (";", $ct); 
            for ($i=0;$i<count($p);$i++) { 
                if (eregi("^(text)", $p[$i])) : 
                    $content["type"] = $p[$i]; 
                elseif (eregi("^(multipart)", $p[$i])) : 
                    $content["type"] = $p[$i]; 
                elseif (eregi("^(application)", $p[$i])) : 
                    $content["type"] = $p[$i]; 
                elseif (eregi("^(message)", $p[$i])) : 
                    $content["type"] = $p[$i];     
                elseif (eregi("^(image)", $p[$i])) : 
                    $content["type"] = $p[$i]; 
                elseif (eregi("^(audio)", $p[$i])) : 
                    $content["type"] = $p[$i]; 
                elseif (eregi("^(charset)", $p[$i])) : 
                    $content["charset"] = eregi_replace("(charset=)|(\")", "", $p[$i]); 
                elseif (eregi("^(report-type)", $p[$i])) : 
                    $content["report-type"] = eregi_replace("(report-type=)|(\")", "", $p[$i]);             
                elseif (eregi("^(type)", $p[$i])) : 
                    $content["subtype"] = eregi_replace("(type=)|(\")", "", $p[$i]);                 
                elseif (eregi("^(boundary)", $p[$i])) : 
                    $content["boundary"] = eregi_replace("(boundary=)|(\")", "", $p[$i]);                 
                elseif (eregi("^(name)", $p[$i])) : 
                    $content["name"] = eregi_replace("(name=)|(\")", "", $p[$i]);                 
                elseif (eregi("^(access-type)", $p[$i])) : 
                    $content["access-type"] = eregi_replace("(access-type=)|(\")", "", $p[$i]);             
                elseif (eregi("^(site)", $p[$i])) : 
                    $content["site"] = eregi_replace("(site=)|(\")", "", $p[$i]);                 
                elseif (eregi("^(directory)", $p[$i])) : 
                    $content["directory"] = eregi_replace("(directory=)|(\")", "", $p[$i]);                 
                elseif (eregi("^(mode)", $p[$i])) : 
                    $content["mode"] = eregi_replace("(mode=)|(\")", "", $p[$i]);                 
                endif;         
            }     
        endif; 
        return $content; 
    } 
    function ContentDisposition() { 
        $c = $this->Headers("Content-Disposition"); 
        $c = ereg_replace("[[:space:]]", "", $c); 
        if (!(ereg(";", $c))) : 
            $cd["type"] = $c; 
        else : 
            $p = explode(";", $c); 
            for ($i=0;$i<count($p);$i++) { 
                if (eregi("^(inline)", $p[$i])) :         
         
                    $cd["type"] = $p[$i]; 
                elseif (eregi("^(attachment)", $p[$i])) : 
                    $cd["type"] = $p[$i]; 
                elseif(eregi("^(filename)", $p[$i])) : 
                    $cd["filename"] = eregi_replace("(filename=)|(\")", "", $p[$i]);                 
                endif; 
            } 
        endif; 
        return $cd; 
    } 
    function my_array_shift(&$array) { 
        reset($array); 
        $key = key($array); 
        $val = current($array); 
        unset($array[$key]); 
        return $val; 
    } 
    function my_array_compact(&$array) { 
        while (list($key, $val) = each($array)) : 
            if (chop($val) == '') 
                unset($array[$key]); 
        endwhile; 
    } 
    function my_in_array($value, $array) { 
        while (list($key, $val) = each($array)) : 
            if (strcmp($value, $val) == 0) 
                return true; 
        endwhile; 
        return false; 
    } 
    function Result() { 
        $is_multipart_alternative = false; 
        $is_multipart_related = false; 
        $found_best = false; 
        do { 
            $next_message = ""; 
            do { 
                $next_multipart = ""; 
                $content = $this->ContentType(); 
                $cd = $this->ContentDisposition();     
     
                if ( eregi("^(multipart)", $content["type"]) ) : 
                    if ( eregi("multipart/alternative", $content["type"]) ) : 
                        $is_multipart_alternative = true; 
                    endif;     
                    if ( eregi("multipart/related", $content["type"]) ) : 
                        $is_multipart_related = true; 
                    endif;             
                 
                    $boundary = "--".$content["boundary"]; 
                    $p = explode($boundary, $this->body); 
                    for ($i=0;$i<count($p);$i++) { 
                        $this->InitMessage($p[$i]); 
                        $content = $this->ContentType(); 
                        $this->ContentDisposition(); 
                                 
                        if ($is_multipart_related AND (chop($this->Headers("Content-ID")) != '')) : 
                            $cont["id"] = ereg_replace("[<>]","", $this->Headers("Content-ID")); 
                            $cont["name"] = $content["name"]; 
                            $contentid[] = $cont; 
                            unset($cont); 
                        endif; 
                        if (eregi("multipart", $content["type"])) : 
                            $multiparts[] = $p[$i]; 
                        elseif (eregi("message", $content["type"])) :                     
                            $messages[] = $p[$i]; 
                        elseif ($this->choose_best AND eregi("text/plain", $content["type"]) AND 
$is_multipart_alternative  AND !($found_best)) : 
                            $best = $p[$i]; 
                        elseif ($this->choose_best 
AND eregi($this->best_format, $content["type"]) AND $is_multipart_alternative ) : 
                            if (eregi("[[:alpha:]]", chop($p[$i]))) :    $best = $p[$i]; 
                                                        $found_best = true;     
                            endif; 
                        elseif (chop($content["type"]) != '' AND chop($this->body) !='') : 
                            $parts[] = $p[$i]; 
                        endif;     
                        #echo "<pre>($i)###".htmlspecialchars($this->header)."</pre>--###<hr>";     
                    } 
                    if (chop($best) != '') : 
                        $parts[] = $best; 
                    endif; 
                else : 
                    if (eregi("(message)", $content["type"])) : 
                        $messages[] = $this->fullmessage; 
                    elseif (chop($this->body) != '') :     
     
                        $parts[] = $this->fullmessage; 
     
                    endif;             
                                 
         
                endif;             
                unset($is_multipart_alternative); 
                unset($best); 
                unset($found_best);         
                if (count($multiparts) > 0) : 
                    $next_multipart = $this->my_array_shift($multiparts); 
                    $this->InitMessage($next_multipart); 
                endif; 
            } while ($next_multipart != ""); 
                if (chop($parts) != '') : 
     
                    for ($i=0;$i<count($parts);$i++) {; 
                        $this->InitMessage($parts[$i]);         
                        $ct = $this->ContentType(); 
                        $cd = $this->ContentDisposition();     
                        if (eregi("text/html", $ct["type"]) AND count($contentid > 0)) : 
                            for ($k=0;$k<count($contentid);$k++) { 
                             
    $filelocation = $this->attachment_path."/".$contentid[$k]["name"]; 
                                $cid = $contentid[$k]["id"]; 
                                $cid = ereg_replace("[[:space:]]", "", $cid); 
                                $this->body = str_replace("cid:", "", $this->body); 
                                $this->body = str_replace($cid, $filelocation, $this->body); 
                            } 
                        endif; 
                        if ($this->auto_decode 
                            AND eregi("attachment", $cd["type"]) 
                            OR eregi("base64", $this->Headers("Content-Transfer-Encoding")) 
                            ) : 
                             
    $filename = chop($ct["name"]) ? $ct["name"] : $cd["filename"]; 
                                if (eregi("base64", $this->Headers("Content-Transfer-Encoding"))) : 
                                 
    $file = base64_decode($this->body); 
                                elseif(eregi("quoted-printable", $this->Headers("Content-Transfer- 
Encoding"))) : 
                                 
    $file = quoted_printable_decode($this->body); 
                                 
    $file = ereg_replace("(=\n)", "", $this->body); 
                                 
    $file = $this->body; 
                                elseif(eregi("7bit", $this->Headers("Content-Transfer-Encoding"))) : 
                                 
    $file = $this->body; 
                                endif; 
                             
    $filepath = $this->attachment_path."/".$filename; 
                                @unlink($filepath); 
                                if (chop($filename != '')) : 
                                 
    $fp = @fopen($filepath, "a") OR die("Cannot open file \"$filepath\""); 
                                 
    fwrite($fp, $file); 
                                 
    fclose($fp); 
                                 
    if (eregi("attachment", $cd["type"]) OR eregi("inline", $cd["type"])) : 
                                 
        #echo "\n<p><a href=\"$filepath\">$filename</a><p>"; 
                                 
        $decoded_part["attachments"] = $filename; 
                                 
    endif; 
                                endif; 
                        endif; 
                        if (eregi("^(text)", $ct["type"] ) 
                            AND !(eregi("text/html", $ct["type"] )) 
                            AND !(eregi("attachment", $cd["type"] )) 
                            OR (chop($ct["type"]) == "") 
                           ) : 
                            $decoded_part["body"]["type"] = $ct["type"]; 
                            $decoded_part["body"]["body"] = $this->body; 
                        elseif (eregi("text/html", $ct["type"] ) AND !(eregi("attachment", $cd["type"] ))) : 
                            $decoded_part["body"]["type"] = $ct["type"]; 
                            $decoded_part["body"]["body"] = $this->body; 
                            #echo "<pre>($parts_count)###".htmlspecialchars($ct["type"])."</pre>--###<hr>";     
         
                        endif; 
                        $dp[] = $decoded_part; 
                        unset($decoded_part); 
                    } 
                 
                endif; 
                $message[] = $dp; 
                unset($dp);         
                unset($is_multpart_related); 
                unset($contentid);         
                unset($parts);     
                if (count($messages) > 0) : 
                    $this->my_array_compact($messages); 
                    $next_message = $this->my_array_shift($messages); 
                    $this->InitMessage($next_message); 
                    $this->InitMessage($this->body); 
                endif;                 
         
        } while ($next_message != ""); 
        return $message; 
    } 

}; 

?>