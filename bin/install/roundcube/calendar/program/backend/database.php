<?php
/**
 * RoundCube Calendar
 *
 * Database backend
 *
 * @version 0.2 BETA 2
 * @author Lazlo Westerhof
 * @author Michael Duelli
 * @url http://rc-calendar.lazlo.me
 * @licence GNU GPL
 * @copyright (c) 2010 Lazlo Westerhof - Netherlands
 */
require_once('backend.php');

class Database implements Backend 
{
  private $rcmail;
  
  public function __construct($rcmail) {
    $this->rcmail = $rcmail;
  }
  
  public function newEvent($start, $summary, $description, $category, $allDay) {
    if (!empty($this->rcmail->user->ID)) {
      $query = $this->rcmail->db->query(
        "INSERT INTO events
         (user_id, start, end, summary, description, category, all_day)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        $this->rcmail->user->ID,
        $start,
        $start,
        $summary,
        $description, 
        $category,               
        $allDay
      );
      $this->rcmail->db->insert_id('events');
    }
  }

  public function editEvent($id, $summary, $description, $category) {
    if (!empty($this->rcmail->user->ID)) {
      $query = $this->rcmail->db->query(
        "UPDATE events 
         SET summary = ?, description = ?, category = ?
         WHERE event_id = ?
         AND user_id = ?",
        $summary,
        $description,
        $category,
        $id,
        $this->rcmail->user->ID
      );
    }
  }

  public function moveEvent($id, $start, $end, $allDay) {
    if (!empty($this->rcmail->user->ID)) {
      $query = $this->rcmail->db->query(
        "UPDATE events 
         SET start = ?, end = ?, all_day = ?
         WHERE event_id = ?
         AND user_id = ?",
        $start,
        $end,
        $allDay,
        $id,
        $this->rcmail->user->ID
      );
    }
  }
  
  public function resizeEvent($id, $start, $end) {
    if (!empty($this->rcmail->user->ID)) {
      $query = $this->rcmail->db->query(
        "UPDATE events 
         SET start = ?, end = ?
         WHERE event_id = ?
         AND user_id = ?",
        $start,
        $end,
        $id,
        $this->rcmail->user->ID
      );
    }
  }

  public function removeEvent($id) {
    if (!empty($this->rcmail->user->ID)) {
      $query = $this->rcmail->db->query(
        "DELETE FROM events
         WHERE event_id=?
         AND user_id=?",
         $id,
         $this->rcmail->user->ID
      );
    }
  }
  
  public function getEvents($start, $end) {
    if (!empty($this->rcmail->user->ID)) {
      
      if ($this->rcmail->config->get('timezone') === "auto") {
        $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
      } else {
        $tz = $this->rcmail->config->get('timezone');
        if($this->rcmail->config->get('dst_active')) {
          $tz++;
        }
      }

      $result = $this->rcmail->db->query(
        "SELECT * FROM events 
         WHERE user_id=?",
         $this->rcmail->user->ID
       );

      $events = array(); 
      while ($result && ($event = $this->rcmail->db->fetch_assoc($result))) {
        $event['start'] = strtotime($event['start']) + ($tz * 3600);
        $event['end'] = strtotime($event['end']) + ($tz * 3600);
        $events[]=array( 
          'id'    => $event['event_id'], 
          'start' => date('c', $event['start']), 
          'end'   => date('c', $event['end']), 
          'title' => $event['summary'], 
          'description'  => $event['description'],
          'className'  => $event['category'],
          'allDay'=> ($event['all_day'] == 1)?true:false,
        ); 
      }

      return json_encode($events);
    }
  }
    
  public function importEvents($events) {
    //TODO
  }
  
  public function exportEvents($start, $end) {
    if (!empty($this->rcmail->user->ID)) {
      
      if ($this->rcmail->config->get('timezone') === "auto") {
        $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
      } else {
        $tz = $this->rcmail->config->get('timezone');
        if($this->rcmail->config->get('dst_active')) {
          $tz++;
        }
      }
      
      $result = $this->rcmail->db->query(
        "SELECT * FROM events 
         WHERE user_id=?",
         $this->rcmail->user->ID
       );

      $ical = "BEGIN:VCALENDAR\n";
      $ical .= "VERSION:2.0\n";
      $ical .= "PRODID:-//RoundCube Webmail//NONSGML Calendar//EN\n";
      while ($result && ($event = $this->rcmail->db->fetch_assoc($result))) {
        $start = strtotime($event['start']);
        $end = strtotime($event['end']);
        $ical .= "BEGIN:VEVENT\n";
        $ical .= "DTSTART:" . date('Ymd\THis\Z',$start) . "\n";
        if($start != $end) {
          $ical .= "DTEND:" . date('Ymd\THis\Z',$end) . "\n";
        }
        $ical .= "SUMMARY:" . $event['summary'] . "\n";
        $ical .= "DESCRIPTION:" . $event['description'] . "\n";
        if(!empty($event['category'])) {
          $ical .= "CATEGORIES:" . strtoupper($event['category']) . "\n";
        }
        $ical .= "END:VEVENT\n";
      }
      $ical .= "END:VCALENDAR";

      return $ical;
    }
  }
}
?>