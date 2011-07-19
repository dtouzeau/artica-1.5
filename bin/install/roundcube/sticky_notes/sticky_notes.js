var edited = function(note) {
  sticky_notes_query(note,'plugin.sticky_notes_edit_sticky_note');
}
var created = function(note) {
  sticky_notes_query(note,'plugin.sticky_notes_new_sticky_note');
}
var deleted = function(note) {
  sticky_notes_query(note,'plugin.sticky_notes_remove_sticky_note');
}
var moved = function(note) {
  edited(note);
}	
var resized = function(note) {
  // min size max size
  edited(note);
}					

function sticky_notes_query(note,action) {
  if(note.text == "" && action == 'plugin.sticky_notes_edit_sticky_note')
    action = 'plugin.sticky_notes_remove_sticky_note';
  if(!note.text){
    var created = new Date();
    note.text=created.toLocaleString() + ":\r\n";  
  }
  note.text = note.text.replace(/<br>/gi,"\r\n");
  rcmail.http_post(action, '_text='+note.text+'&_pos_x='+note.pos_x+'&_pos_y='+note.pos_y+'&_width='+note.width+'&_height='+note.height+'&_nid='+note.id);
}