<?php
/**
 * RoundCube Calendar
 *
 * Plugin to add a calendar to RoundCube.
 *
 * @version 0.2 BETA 2
 * @author Lazlo Westerhof
 * @url http://rc-calendar.lazlo.me
 * @licence GNU GPL
 * @copyright (c) 2010 Lazlo Westerhof - Netherlands
 */

class calendar extends rcube_plugin
{
  public $task = '?(?!login|logout).*';

  public $backend = null;

  function init() {
    $rcmail = rcmail::get_instance();
    
    if(file_exists("./plugins/calendar/config.inc.php")) {
      $this->load_config('config/config.inc.php');
    } else {
      $this->load_config('config/config.inc.php.dist'); 
    }
    
    $backend_type = $rcmail->config->get('backend', 'database');
    require('program/backend/' . $backend_type . '.php');
    
    if($backend_type === "google") {
      $this->backend = new Google($rcmail, 
                                  $rcmail->config->get('username'), 
                                  $rcmail->config->get('password'));
    } else {
      $this->backend = new Database($rcmail);
    }
    
    $this->add_texts('localization/', true);
    
    $this->register_action('plugin.calendar', array($this, 'startup'));
    $this->register_action('plugin.getSettings', array($this, 'getSettings'));
    
    $this->add_hook('user_preferences', array($this, 'settingsTable'));
    $this->add_hook('save_preferences', array($this, 'saveSettings'));

    //backend actions
    $this->register_action('plugin.newEvent', array($this, 'newEvent'));
    $this->register_action('plugin.editEvent', array($this, 'editEvent'));
    $this->register_action('plugin.moveEvent', array($this, 'moveEvent'));
    $this->register_action('plugin.resizeEvent', array($this, 'resizeEvent'));
    $this->register_action('plugin.removeEvent', array($this, 'removeEvent'));
    $this->register_action('plugin.getEvents', array($this, 'getEvents'));
    $this->register_action('plugin.exportEvents', array($this, 'exportEvents'));
    
    // add taskbar button
    $this->add_button(array(
      'name'    => 'calendar',
      'class'   => 'button-calendar',
      'label'   => 'calendar.calendar',
      'href'    => './?_task=dummy&_action=plugin.calendar',
      ), 'taskbar');

    // add styles
    $skin = $rcmail->config->get('skin');
    if(!file_exists('skins/' . $skin . '/calendar.css')) {
      $skin = "default";
    }
    $this->include_stylesheet('skins/' . $skin . '/calendar.css');
  }

  function startup() {
    $rcmail = rcmail::get_instance();

    $rcmail->output->set_pagetitle($this->gettext('calendar'));

    $skin = $rcmail->config->get('skin');
    if(!file_exists('skins/' . $skin . '/jquery-ui.css') || !file_exists('skins/' . $skin . '/fullcalendar.css')) {
      $skin = "default";
    }
    $this->include_stylesheet('skins/' . $skin . '/jquery-ui.css');
    $this->include_stylesheet('skins/' . $skin . '/fullcalendar.css');
    
    $this->register_handler('plugin.category_css', array($this, 'generateCSS'));
    $this->register_handler('plugin.category_html', array($this, 'generateHTML'));

    $this->include_script('program/js/jquery-ui.js');
    $this->include_script('program/js/jquery-qtip.js');
    $this->include_script('program/js/fullcalendar.js');
    $this->include_script('program/js/calendar.js');
    
    $rcmail->output->send('calendar.calendar');
  }
  
  function newEvent() {
    $start = $this->toGMT(get_input_value('_start', RCUBE_INPUT_POST));
    $summary = trim(get_input_value('_summary', RCUBE_INPUT_POST));
    $description = trim(get_input_value('_description', RCUBE_INPUT_POST));
    $category = trim(get_input_value('_category', RCUBE_INPUT_POST));
    $allDay = get_input_value('_allDay', RCUBE_INPUT_POST);
    $allDay = ($allDay === "true")?1:0;
    
    $this->backend->newEvent($start, $summary, $description, $category, $allDay);
   
    $rcmail = rcmail::get_instance();
    $rcmail->output->command('plugin.reloadCalendar', array());
  }

  function editEvent() {
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $summary = trim(get_input_value('_summary', RCUBE_INPUT_POST));
    $description = trim(get_input_value('_description', RCUBE_INPUT_POST));
    $category = trim(get_input_value('_category', RCUBE_INPUT_POST));

    $this->backend->editEvent($id, $summary, $description, $category);
  }
  
  function moveEvent() {
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $start = $this->toGMT(get_input_value('_start', RCUBE_INPUT_POST));
    $end = $this->toGMT(get_input_value('_end', RCUBE_INPUT_POST));
    $allDay = get_input_value('_allDay', RCUBE_INPUT_POST);
    $allDay = ($allDay === "true")?1:0;
    
    $this->backend->moveEvent($id, $start, $end, $allDay);
    $rcmail = rcmail::get_instance();
    $rcmail->output->command('plugin.reloadCalendar', array());
  }
  
  function resizeEvent() {
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $start = $this->toGMT(get_input_value('_start', RCUBE_INPUT_POST));
    $end = $this->toGMT(get_input_value('_end', RCUBE_INPUT_POST));
    
    $this->backend->resizeEvent($id, $start, $end);
  }
  
  function removeEvent() {
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
      
    $this->backend->removeEvent($id);
  }
  
  function getEvents() {
    $start = $this->toGMT(get_input_value('_start', RCUBE_INPUT_POST));
    $end = $this->toGMT(get_input_value('_end', RCUBE_INPUT_POST));
    
    echo $this->backend->getEvents($start, $end);
    exit;
  }
  
  function exportEvents() {
    $start = $this->toGMT(get_input_value('_start', RCUBE_INPUT_POST));
    $end = $this->toGMT(get_input_value('_end', RCUBE_INPUT_POST));
    
    header("Content-Type: text/calendar");
    header("Content-Disposition: inline; filename=calendar.ics");
    
    echo $this->backend->exportEvents($start, $end);
    exit;
  }

  function getSettings() {
    $rcmail = rcmail::get_instance();

    $settings = array();
    // configuration
    $settings['default_view'] = $rcmail->config->get('default_view', "agendaWeek");
    $settings['time_format'] = $rcmail->config->get('time_format', "HH:mm");
    $settings['timeslots'] = $rcmail->config->get('timeslots', 2);
    $settings['first_day'] = $rcmail->config->get('first_day', 1);
    
    $settings['first_hour'] = $rcmail->config->get('first_hour', 6);

    // localisation
    $settings['days'] = array(
      rcube_label('sunday'),   rcube_label('monday'),
      rcube_label('tuesday'),  rcube_label('wednesday'),
      rcube_label('thursday'), rcube_label('friday'),
      rcube_label('saturday')
    );
    $settings['days_short'] = array(
      rcube_label('sun'), rcube_label('mon'),
      rcube_label('tue'), rcube_label('wed'),
      rcube_label('thu'), rcube_label('fri'),
      rcube_label('sat')
    );
    $settings['months'] = array(
      $rcmail->gettext('longjan'), $rcmail->gettext('longfeb'),
      $rcmail->gettext('longmar'), $rcmail->gettext('longapr'),
      $rcmail->gettext('longmay'), $rcmail->gettext('longjun'),
      $rcmail->gettext('longjul'), $rcmail->gettext('longaug'),
      $rcmail->gettext('longsep'), $rcmail->gettext('longoct'),
      $rcmail->gettext('longnov'), $rcmail->gettext('longdec')
    );
    $settings['months_short'] = array(
      $rcmail->gettext('jan'), $rcmail->gettext('feb'),
      $rcmail->gettext('mar'), $rcmail->gettext('apr'),
      $rcmail->gettext('may'), $rcmail->gettext('jun'),
      $rcmail->gettext('jul'), $rcmail->gettext('aug'),
      $rcmail->gettext('sep'), $rcmail->gettext('oct'),
      $rcmail->gettext('nov'), $rcmail->gettext('dec')
    );
    $settings['today'] = rcube_label('today');

    $rcmail->output->command('plugin.getSettings', array('settings' => $settings));
  }
  
  function settingsTable($args) {
    if ($args['section'] == 'server') {
      $rcmail = rcmail::get_instance();   
      
      $args['blocks']['calendar']['name'] = $this->gettext('calendar');
 
      $default_view = $rcmail->config->get('default_view', "HH:mm");    
      $field_id = 'rcmfd_default_view';
      $select = new html_select(array('name' => '_default_view', 'id' => $field_id));
      $select->add($this->gettext('day'), "agendaDay");
      $select->add($this->gettext('week'), "agendaWeek");
      $select->add($this->gettext('month'), "month");
      $args['blocks']['calendar']['options']['default_view'] = array(
        'title' => html::label($field_id, Q($this->gettext('default_view'))),
        'content' => $select->show($rcmail->config->get('default_view')),
      );
      
      $time_format = $rcmail->config->get('time_format', "HH:mm");    
      $field_id = 'rcmfd_time_format';
      $choices = array('HH:mm', 'H:mm', 'h:mmt');    
      $select = new html_select(array('name' => '_time_format', 'id' => $field_id));
      $select->add($choices);      
      $args['blocks']['calendar']['options']['time_format'] = array(
        'title' => html::label($field_id, Q($this->gettext('time_format'))),
        'content' => $select->show($rcmail->config->get('time_format')),
      );
      
      $timeslots = $rcmail->config->get('timeslots', 2);    
      $field_id = 'rcmfd_timeslot';
      $choices = array('1', '2', '3', '4', '6');    
      $select = new html_select(array('name' => '_timeslots', 'id' => $field_id));
      $select->add($choices);      
      $args['blocks']['calendar']['options']['timeslots'] = array(
        'title' => html::label($field_id, Q($this->gettext('timeslots'))),
        'content' => $select->show($rcmail->config->get('timeslots')),
      );
      
      $first_day = $rcmail->config->get('first_day', 1);    
      $field_id = 'rcmfd_timeslot';   
      $select = new html_select(array('name' => '_first_day', 'id' => $field_id));
      $select->add(rcube_label('sunday'), '0');
      $select->add(rcube_label('monday'), '1');
      $select->add(rcube_label('tuesday'), '2');
      $select->add(rcube_label('wednesday'), '3');
      $select->add(rcube_label('thursday'), '4');
      $select->add(rcube_label('friday'), '5');
      $select->add(rcube_label('saturday'), '6');
      $args['blocks']['calendar']['options']['first_day'] = array(
        'title' => html::label($field_id, Q($this->gettext('first_day'))),
        'content' => $select->show($rcmail->config->get('first_day')),
      );
    }
    return $args;
  }
  
  function saveSettings($args) {
    if ($args['section'] == 'server') {    
      $rcmail = rcmail::get_instance();
      $args['prefs']['default_view'] = get_input_value('_default_view', RCUBE_INPUT_POST);
      $args['prefs']['time_format'] = get_input_value('_time_format', RCUBE_INPUT_POST);
      $args['prefs']['timeslots'] = get_input_value('_timeslots', RCUBE_INPUT_POST);
      $args['prefs']['first_day'] = get_input_value('_first_day', RCUBE_INPUT_POST);
    }
    
    return $args;
  }
  
  function toGMT($time) {
    return date('Y-m-d H:i:s',$time - date('Z'));
  }
  
  function generateCSS() {
    $rcmail = rcmail::get_instance();
    $categories = $rcmail->config->get('categories');    

    $css = "";
    if(!empty($categories)) {
      $css .= "<style type=\"text/css\">\n";
      foreach ($categories as $class => $color) {
        $css .= "." . $class . ",\n";
        $css .= ".fc-agenda ." . $class . " .fc-event-time,\n";
        $css .= "." . $class . " a {\n";
        $css .= "background-color: #" . $color . ";\n";
        $css .= "border-color: #" . $color . ";\n";
        $css .= "}\n";
      }
      $css .= "</style>";
    }
    return($css);
  }

  function generateHTML() {
    $rcmail = rcmail::get_instance();
    $categories = $rcmail->config->get('categories');    

    $select = "<select name=\"category\">\n";
    $select .= "<option value=\"\"></option>\n";
    foreach ($categories as $class => $color) {
      $select .= "<option value=\"" . $class . "\">" . $class . "</option>\n";
    }
    $select .= "</select>";

    return($select);
  }
}  
?>