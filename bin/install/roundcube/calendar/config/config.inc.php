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
 *
 **/

// backend type (database)
$rcmail_config['backend'] = "database";

// default calendar view (agendaDay, agendaWeek, month)
$rcmail_config['default_view'] = "agendaWeek";

// time format (HH:mm, H:mm, h:mmt)
$rcmail_config['time_format'] = "HH:mm";

// timeslots per hour (1, 2, 3, 4, 6)
$rcmail_config['timeslots'] = 2;

// first day of the week (0-6)
$rcmail_config['first_day'] = 1;

// first hour of the calendar (0-23)
$rcmail_config['first_hour'] = 6;

// event categories
$rcmail_config['categories'] = array('Personal' => 'c0c0c0', 
                                         'Work' => 'ff0000',
                                       'Family' => '00ff00',
                                      'Holiday' => 'ff6600');
?>