<?php
/**
 * RoundCube Calendar
 *
 * Interface for calendar backends
 *
 * @version 0.2 BETA 2
 * @author Lazlo Westerhof
 * @author Michael Duelli
 * @url http://rc-calendar.lazlo.me
 * @licence GNU GPL
 * @copyright (c) 2010 Lazlo Westerhof - Netherlands
 */
interface Backend
{
  /**
   * Add a single event to the database
   *
   * @param  integer Event identifier
   * @param  integer Event's start
   * @param  string  Event's summary
   * @param  string  Event's description
   * @param  string  Event's category
   * @param  integer Event allDay state
   * @access public
   */
  public function newEvent($start, $summary, $description, $category, $allDay);

  /**
   * Edit a single event
   *
   * @param  integer Event identifier
   * @param  string  Event's title
   * @param  string  Event's category
   * @access public
   */
  public function editEvent($id, $title, $description, $category);

  /**
   * Move a single event
   *
   * @param  integer Event identifier
   * @param  integer Event's new start
   * @param  integer Event's new end
   * @param  integer Event allDay state
   * @access public
   */
  public function moveEvent($id, $start, $end, $allDay);

  /**
   * Resize a single event
   *
   * @param  integer Event identifier
   * @param  integer Event's new start
   * @param  integer Event's new end
   * @access public
   */
  public function resizeEvent($id, $start, $end);
  
  /**
   * Remove a single event from the database
   * 
   * @param  integer Event identifier
   * @access public
   */
  public function removeEvent($id);

  /**
   * Get events from database and feeds
   *
   * @param  integer Start time events window
   * @param  integer End time events window
   * @return string  JSON encoded events array
   * @access public
   */
  public function getEvents($start, $end);
  
  /**
   * Import events from iCalendar format
   *
   * @param  array Associative events array
   * @access public
   */
  public function importEvents($events);
  
  /**
   * Export events to iCalendar format
   *
   * @param  integer Start time events window
   * @param  integer End time events window
   * @return string  Events in iCalendar format (http://tools.ietf.org/html/rfc5545)
   * @access public
   */
  public function exportEvents($start, $end);
}
?>