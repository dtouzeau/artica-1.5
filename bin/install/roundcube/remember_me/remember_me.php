<?php

/**
 * remember_me plugin
 *
 *
 * @version 1.6 - 18.09.2010
 * @author valsily0 / Roland 'rosali' Liebl
 * @website http://myroundcube.googlecode.com
 * @licence GNU GPL
 *
 **/
 
/**
 *
 * Usage: http://mail4us.net/myroundcube/
 *
 **/

class remember_me extends rcube_plugin {
  
  function init() {

    $this->add_hook('template_object_loginform', array($this,'rememberme_loginform'));
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('authenticate', array($this, 'authenticate'));
    $this->add_hook('login_after', array($this, 'login_after'));
    $this->add_hook('login_failed', array($this, 'login_failed'));
    $this->add_hook('session_destroy', array($this, 'logout'));
  }

  function rememberme_loginform($before) {
  
    $this->add_texts('localization/');
    $this->include_stylesheet('skins/'.$this->api->output->config['skin'].'/remember_me.css');
    $checked = "";
    if($_COOKIE['rememberme_checked'] == 1)
      $checked = 'checked="checked"';
    $b = $before['content'];
    $b = str_ireplace ('</tbody>',
      '<tr><td class="title"><label for="rcmrememberme">' . $this->gettext('rememberme','remember_me') . '</label></td><td><input id="rcmrememberme" ' . $checked . ' name="_rememberme" value="1" type="checkbox" /></td>
      </tr></tbody>',$b);
    $before['content']=$b;;	
    return ($before);
  }

  function startup($args) {
   
    if ($args['task'] == 'settings')
      return $args; // do not login on pwtools request

    if(isset($_SESSION['temp'])&&
      !empty($_COOKIE['rememberme_user']) &&
      !empty($_COOKIE['rememberme_pass']) &&
      !empty($_COOKIE['rememberme_host']) &&
      !empty($_COOKIE['rememberme_timezone'])
      ){
      $user = $this->decode($_COOKIE['rememberme_user']);
      $pass = $this->decode($_COOKIE['rememberme_pass']);
      $host = $this->decode($_COOKIE['rememberme_host']);
      $timezone = $_COOKIE['rememberme_timezone'];

      if($user != "" && $pass != "" && $host != "" && $timezone != ""){
        $args['action'] = 'login';
      }
    }
   
    return $args;
  }

  function authenticate($args) {
    if(!empty($_COOKIE['rememberme_user']) &&
        !empty($_COOKIE['rememberme_pass']) && 
        !empty($_COOKIE['rememberme_host']) &&
        !empty($_COOKIE['rememberme_timezone'])
      ){
      $user = $this->decode($_COOKIE['rememberme_user']);
      $pass = $this->decode($_COOKIE['rememberme_pass']);
      $host = $this->decode($_COOKIE['rememberme_host']);
      $timezone = $_COOKIE['rememberme_timezone'];

      if($user != "" && $pass != "" && $host != "" && $timezone != ""){
        $args['user']= $user;
        $args['pass']= $pass;
        $args['host']= $host;

        $_REQUEST['_timezone'] = $timezone;

        //Detect DST change
        if(date('I',time()) != date('I',time()-86400)){
          rcmail::setcookie ('rememberme_user','',time()-3600);
          rcmail::setcookie ('rememberme_pass','',time()-3600);
          rcmail::setcookie ('rememberme_host','',time()-3600);
          rcmail::setcookie ('rememberme_timezone','',time()-3600);
        }
        else{
          //Update cookie time
          rcmail::setcookie ('rememberme_user',$this->encode($user),time()+60*60*24*365);
          rcmail::setcookie ('rememberme_pass',$this->encode($pass),time()+60*60*24*365);
          rcmail::setcookie ('rememberme_host',$this->encode($host),time()+60*60*24*365);
          rcmail::setcookie ('rememberme_checked',1,time()+60*60*24*365);
          rcmail::setcookie ('rememberme_timezone',$timezone,time()+60*60*24*365);
        }
      }
    }
    return $args;
  }

  function login_after($args) {
    if (($_POST['_rememberme'] == 1) && !empty($_POST['_user']) &&  !empty($_POST['_pass'])) {
       rcmail::setcookie ('rememberme_user',$this->encode(trim($_POST['_user'])),time()+60*60*24*365);
       rcmail::setcookie ('rememberme_pass',$this->encode(trim($_POST['_pass'])),time()+60*60*24*365);
       $timezone = $_POST['_timezone'];
       if(empty($timezone) || $timezone == '_default_')
         $timezone = date('Z') / 3600;
       if(!empty($_POST['_host']))
         $host = trim($_POST['_host']);
       else{
         $prefix = "";
         if(!empty($_SESSION['imap_ssl'])){
           $prefix = "ssl://";
         }
         $suffix = "";
         if($_SESSION['imap_port'] != 143){
           $suffix = ":".$_SESSION['imap_port'];
         }
         $host = $prefix.$_SESSION['imap_host'].$suffix;
       }
       rcmail::setcookie ('rememberme_host',$this->encode($host),time()+60*60*24*365);
       rcmail::setcookie ('rememberme_checked',1,time()+60*60*24*365);
       rcmail::setcookie ('rememberme_timezone',$timezone,time()+60*60*24*365);
    }
    return $args;
  }

  function login_failed($args) {
    rcmail::setcookie ('rememberme_user','',time()-3600);
    rcmail::setcookie ('rememberme_pass','',time()-3600);
    rcmail::setcookie ('rememberme_host','',time()-3600);
    rcmail::setcookie ('rememberme_timezone','',time()-3600);
    return $args;
  }
  
  function logout($args) {
    $this->add_texts('localization/');
    $rcmail = rcmail::get_instance();
    if($rcmail->task == "logout" && isset($_COOKIE['rememberme_user']) && isset($_COOKIE['rememberme_pass'])){
      if(!isset($_POST['_remember_me'])){
        if(!isset($_GET['_remember_me'])){
          $rcmail->output->send("remember_me.remember_me");
        }
        else{
          $rcmail->output->show_message("remember_me.close");
          $rcmail->output->send("remember_me.redirect");
          exit;
        }
      }
      else{
        rcmail::setcookie ('rememberme_user','',time()-3600);
        rcmail::setcookie ('rememberme_pass','',time()-3600);
        rcmail::setcookie ('rememberme_host','',time()-3600);
        rcmail::setcookie ('rememberme_checked','',time()-3600);
        rcmail::setcookie ('rememberme_timezone','',time()-3600);
        unset($_COOKIE['rememberme_checked']);
        header('Location: ./?_task=logout');
        exit;
      }
    }
    return $args;      
  } 

  private function encode ($a) {
    if($a != ""){
      $rcmail = rcmail::get_instance();
      return $rcmail->encrypt($a);
    }
    else
      return "";
  
  }
  
  private function decode ($a) {
    if($a != ""){
      $rcmail = rcmail::get_instance();
      return $rcmail->decrypt($a);
    }
    else
      return "";

  }

}
?>