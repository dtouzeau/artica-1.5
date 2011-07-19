<?php

/**
  * @version 1.8.2
  * @author Cor Bosman (roundcube@wa.ter.net)
  */
  
class jqueryui extends rcube_plugin
{
  
  public function init() 
  {    
    $this->add_hook('render_page', array($this, 'add_jqueryui')); 
  }
  
  function add_jqueryui($args)
  {
    $rcmail = rcmail::get_instance();
    $this->load_config();
    
    $skin_path = $this->local_skin_path();
    
    // jquery UI
    $this->include_script('js/jquery-ui-1.8.2.custom.min.js');
  
    // jquery UI stylesheet
    $this->include_stylesheet("$skin_path/css/smoothness/jquery-ui-1.8.2.custom.css"); 
    
    // jquery UI localization
    $jquery_ui_i18n = $rcmail->config->get('jquery_ui_i18n', array());
    if(count($jquery_ui_i18n) > 0) {
      $lang_l = str_replace('_', '-', substr($_SESSION['language'],0,5));
      $lang_s = substr($_SESSION['language'],0,2);
      foreach($jquery_ui_i18n as $package) {
        if(file_exists("plugins/jqueryui/js/i18n/jquery.ui.$package-$lang_l.js")) {
          $this->include_script("js/i18n/jquery.ui.$package-$lang_l.js");
        } elseif(file_exists("plugins/jqueryui/js/i18n/jquery.ui.$package-$lang_s.js")) {
          $this->include_script("js/i18n/jquery.ui.$package-$lang_s.js");
        }
      }
    }
  }
}
?>
