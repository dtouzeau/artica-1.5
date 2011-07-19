<?php

class SimpleCrypt{

   var $key;


   /*----------------------------------------------------------------------
  Archivo: eccryption.php
  Autor: Francisco Echarte [ patxi@eslomas.com ]
  Fecha: 2001-07-25

  Clases: Crypter
  
  Objetivo:
  Clase que ofrece funciones para la encriptaci�n y desencriptaci�n

  Observaciones:
  Basado en una observaci�n vista en la p�gina de md5 en php.net
  Al constructor hay que pasarle la palabra utilizada para la encriptaci�n.
  
  Modificaciones:


  LICENCIA
  ========

     Copyright (c) 2001 Francisco Echarte <patxi@eslomas.com>
     This software is released under the GNU Public License
     Please see http://www.gnu.org/copyleft/lgpl.txt for licensing details!

    * 
    *      Entrada: $clave => clave que va a utilizar el crypter
     Salida : nada
     Efecto : es el constructor de la clase.
     ----------------------------------------------------------------------*/
   function SimpleCrypt($clave=null){
   		if(trim($clave)==null){$clave=date("Ymd");}
      $this->key = $clave;
   }

   /*----------------------------------------------------------------------
     Entrada: $clave => clave que va a utilizar el crypter
     Salida : nada
     Efecto : actualiza la clave
     ----------------------------------------------------------------------*/
   function setKey($clave){
      $this->key = $clave;
   }
   
   function keyED($txt) { 
      $encrypt_key = md5($this->key); 
      $ctr=0; 
      $tmp = ""; 
      for ($i=0;$i<strlen($txt);$i++) { 
         if ($ctr==strlen($encrypt_key)) $ctr=0; 
         $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1); 
         $ctr++; 
      } 
      return $tmp; 
   } 
   
   function encrypt($txt){ 
      srand((double)microtime()*1000000); 
      $encrypt_key = md5(rand(0,32000)); 
      $ctr=0; 
      $tmp = ""; 
      for ($i=0;$i<strlen($txt);$i++){ 
         if ($ctr==strlen($encrypt_key)) $ctr=0; 
         $tmp.= substr($encrypt_key,$ctr,1) . 
             (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1)); 
         $ctr++; 
      } 
      return base64_encode($this->keyED($tmp)); 
   } 

   function decrypt($txt) { 
      $txt = $this->keyED(base64_decode($txt)); 
      $tmp = ""; 
      for ($i=0;$i<strlen($txt);$i++){ 
         $md5 = substr($txt,$i,1); 
         $i++; 
         $tmp.= (substr($txt,$i,1) ^ $md5); 
      } 
      return $tmp; 
   } 

}

?>