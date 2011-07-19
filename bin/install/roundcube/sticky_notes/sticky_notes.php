<?php

/**
 * Sticky Notes
 *
 * @version 1.4 - 09.09.2010
 * @author Roland 'rosali' Liebl
 * @website http://myroundcube.googlecode.com
 * @licence GNU GPL
 *
 **/
 
/**
 *
 * Usage: http://mail4us.net/myroundcube/
 *
 **/ 

/**
 * Requirements: (*) jqueryui plugin @website http://myroundcube.googlecode.com or http://underwa.ter.net/roundcube-plugins
 *
 **/
 
/**
 *
 *  ToDo: Switch button if $count > 0 (need notes button merged with a red exclamation mark)
 *
 **/

class sticky_notes extends rcube_plugin
{
  private $sticky_notes = array();
  public $task = '?(?!login|logout).*';
  private $rcmail;

  function init()
  {
    $rcmail = rcmail::get_instance();
    $this->rcmail = $rcmail;
    
    if(file_exists("./plugins/sticky_notes/config/config.inc.php")) {
      $this->load_config('config/config.inc.php');
    } else {
      $this->load_config('config/config.inc.php.dist'); 
    }
    
    $this->add_texts('localization/', false);
 
    $this->register_action('plugin.sticky_notes', array($this, 'startup'));
    $this->register_action('plugin.sticky_notes_new_sticky_note', array($this, 'new_sticky_note'));
    $this->register_action('plugin.sticky_notes_edit_sticky_note', array($this, 'edit_sticky_note'));
    $this->register_action('plugin.sticky_notes_remove_sticky_note', array($this, 'remove_sticky_note'));
    $this->add_hook('template_object_sticky_notes_js', array($this, 'sticky_notes_js'));
       
    // add taskbar button
    $this->add_button(array(
      'name'    => 'sticky_notes',
      'class'   => 'button-sticky_notes',
      'label'   => 'sticky_notes.sticky_notes',
      'href'    => './?_task=dummy&_action=plugin.sticky_notes',
      'id'      => 'sticky_notes_button'
      ), 'taskbar');

    // add styles
    $skin = $rcmail->config->get('skin');
    $this->include_stylesheet('skins/' . $skin . '/sticky_notes.css');
    
  }
  
  private function q($str)
  {
    return $this->rcmail->db->quoteIdentifier($str);
  }
  
  private function table($str)
  {
    return $this->rcmail->db->quoteIdentifier(get_table_name($str));
  }
     
  function startup()
  {
    $rcmail = $this->rcmail;
    $plugins = array_flip($rcmail->config->get('plugins'));
    if(empty($plugins['jqueryui'])){
      die('jqueryui plugin is not installed. Get it <a href="http://underwa.ter.net/roundcube-plugins">here</a>.');
    }         
    $this->sticky_notes = $this->get_sticky_notes();
    $count = count($this->sticky_notes);      
    $this->include_script('sticky_notes.js');
    $rcmail->output->set_pagetitle($this->gettext('sticky_notes') . " ($count)");
    $rcmail->output->add_label(
      'sticky_notes.add_note'
    );
    $rcmail->output->send('sticky_notes.sticky_notes');
  }
  
  function sticky_notes_js($args)
  {
    $json = str_replace("\\n","\\r\\n",json_encode($this->sticky_notes));
    
    $content = '
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function() {
  var options = {
    notes: ' . $json . ',
    resizable: true,
    controls: true, 
    editCallback: edited,
    createCallback: created,
    deleteCallback: deleted,
    moveCallback: moved,					
    resizeCallback: resized
  };
  jQuery("#notes").stickyNotes(options);
});
document.getElementById("sticky_notes_button").href="#";
</script>    
';

    $args['content'] = $content;
    
    return $args;
  }

  function GMT_time($t)
  {
    return date('Y-m-d H:i:s',$t - date('Z'));
  }
  
  function new_sticky_note()
  {
    $rcmail = $this->rcmail;

    if (!empty($rcmail->user->ID)) {
      $text = get_input_value('_text', RCUBE_INPUT_POST);
      $pos_x = get_input_value('_pos_x', RCUBE_INPUT_POST);
      $pos_y = get_input_value('_pos_y', RCUBE_INPUT_POST);
      $width = get_input_value('_width', RCUBE_INPUT_POST);
      $height = get_input_value('_height', RCUBE_INPUT_POST);
      $nid = get_input_value('_nid', RCUBE_INPUT_POST);

      $query = $rcmail->db->query(
        "INSERT INTO " . $this->table('sticky_notes') . "
         (".$this->q('user_id').", ".$this->q('nid').", ".$this->q('pos_x').", ".$this->q('pos_y').", ".$this->q('width').", ".$this->q('height').", ".$this->q('text').", ".$this->q('timestamp').")
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
         $rcmail->user->ID,
         $nid,
         $pos_x,
         $pos_y,
         $width,
         $height,
         $text,
         $this->GMT_time(time())
      );              
    }
  }

  function edit_sticky_note()
  {
    $rcmail = $this->rcmail;

    if (!empty($rcmail->user->ID)) {
      $text = get_input_value('_text', RCUBE_INPUT_POST);
      $pos_x = get_input_value('_pos_x', RCUBE_INPUT_POST);
      $pos_y = get_input_value('_pos_y', RCUBE_INPUT_POST);
      $width = get_input_value('_width', RCUBE_INPUT_POST);
      $height = get_input_value('_height', RCUBE_INPUT_POST);
      $nid = get_input_value('_nid', RCUBE_INPUT_POST);

      $query = $rcmail->db->query(
        "UPDATE " . $this->table('sticky_notes') . " 
         SET ".$this->q('pos_x')."=?, ".$this->q('pos_y')."=?, ".$this->q('width')."=?, ".$this->q('height')."=?, ".$this->q('text')."=?, ".$this->q('timestamp')."=?
         WHERE ".$this->q('nid')."=?
         AND ".$this->q('user_id')."=?",
         $pos_x,
         $pos_y,
         max(100,$width),
         max(70,$height),
         $text,
         $this->GMT_time(time()),
         $nid,         
         $rcmail->user->ID
      );

      if($width < 100 || $height < 70){
        $rcmail->output->redirect(array('task' => 'dummy', 'action' => 'plugin.sticky_notes'));
      }      
    }
  }
  
  function remove_sticky_note()
  {
    $rcmail = $this->rcmail;

    if (!empty($rcmail->user->ID)) {
      $nid = get_input_value('_nid', RCUBE_INPUT_POST);

      $query = $rcmail->db->query(
      "DELETE FROM " . $this->table('sticky_notes') . "
       WHERE ".$this->q('nid')."=?
       AND ".$this->q('user_id')."=?",
       $nid,
       $rcmail->user->ID
      );
    }
  }

  function get_sticky_notes()
  {
    $rcmail = $this->rcmail;

    if (!empty($rcmail->user->ID)) {
      $result = $rcmail->db->query(
        "SELECT * FROM " . $this->table('sticky_notes') . " 
         WHERE ".$this->q('user_id')."=?",
         $rcmail->user->ID
       );

      $notes = array(); 
      while ($result && ($note = $rcmail->db->fetch_assoc($result))) {
        $notes[]=array( 
          'id'     => (int) $note['nid'],
          'text'   => (string) $note['text'],//wordwrap($note['text'], round(max(100,(int)$note['width'])/7.5), "<br>", true),
          'pos_x'  => (int) $note['pos_x'],
          'pos_y'  => (int) $note['pos_y'],
          'width'  => max(100,(int)$note['width']),
          'height' => max(70,(int)$note['height'])
        ); 
      }      

      return $notes;
      
    }
  }
}  
?>