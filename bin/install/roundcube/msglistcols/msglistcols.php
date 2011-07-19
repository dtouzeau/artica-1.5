<?php

/**
 * Message List Cols 
 *
 * Define Header Cols on a per user level
 *
 * @version 1.1 - 09.09.2010
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
 
class msglistcols extends rcube_plugin
{
    function init()
    {
      $this->add_texts('localization/', false);
      $dont_override = rcmail::get_instance()->config->get('dont_override', array());
       
      if (!in_array('list_cols', $dont_override)){
          $this->add_hook('preferences_list', array($this, 'settings_table'));
          $this->add_hook('preferences_save', array($this, 'save_prefs'));
      }
    }

    function settings_table($args)
    {
      if ($args['section'] == 'mailbox') {

        $a_list_cols = rcmail::get_instance()->config->get('list_cols');
        
        $args['blocks']['roworder']['name'] = Q($this->gettext('roworder','msglistcols'));

        for($i=0;$i<9;$i++){
        
          $field_id = 'rcmfd_list_col' . $i;
          $select_col = new html_select(array('name' => '_list_cols[]', 'id' => $field_id));
          $select_col->add(rcube_label('skip','msglistcols'), '');
          $select_col->add(rcube_label('subject'), 'subject');
          $select_col->add(rcube_label('from'), 'from');
          $select_col->add(rcube_label('to'), 'to');
          $select_col->add(rcube_label('cc'), 'cc');
          $select_col->add(rcube_label('replyto'), 'replyto');
          $select_col->add(rcube_label('date'), 'date');
          $select_col->add(rcube_label('size'), 'size');
          $select_col->add(rcube_label('flagged','msglistcols'), 'flag');
          $select_col->add(rcube_label('attachment','msglistcols'), 'attachment');
          
          $args['blocks']['roworder']['options']['listcol_' . $i]['title'] = Q($this->gettext('list_col_' . $i ,'msglistcols'));
          
          if(!empty($a_list_cols[$i]))
            $selected = $a_list_cols[$i];
          else
            $selected = "";
          $args['blocks']['roworder']['options']['listcol_' . $i]['content'] = $select_col->show($selected);

          unset($select_col);

        }
      }
      
      return $args;
      
    }

    function save_prefs($args){
        
      if(!empty($_POST['_list_cols'])){
        $a_list_cols_user = $_POST['_list_cols'];
                
        // sanitize: don't save empty values
        $i = -1;
        $a_save = array();
        foreach ($a_list_cols_user as $key => $value) {
          if (is_null($value) || $value=="") {
            unset($a_list_cols_user[$key]); 
          }
          else{
            $i++;
            $a_save[$i] = $value;
          } 
        }
        $a_list_cols_user = $a_save;
        if(count($a_list_cols_user) < 1)
          $a_list_cols_user = array('subject');
          
        $args['prefs']['list_cols'] = $a_list_cols_user;
        
      }

      return $args;
    }

}
?>