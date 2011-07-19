////
// customize DropZone
////
var KitpagesDropzone = Class.create();
 
KitpagesDropzone.prototype = Object.extend (
    Rico.Dropzone.prototype,
{
    accept: function(draggableObjects) {
        var htmlElement = this.getHTMLElement();
        if ( htmlElement == null )
            return;
        n = draggableObjects.length;
        for ( var i = 0 ; i < n ; i++ ) {
            var theGUI = draggableObjects[i].getDroppedGUI();
            if ( 
RicoUtil.getElementsComputedStyle( theGUI, "position" ) == 
            "absolute" ) {
                theGUI.style.position = "static";
                theGUI.style.top = "";
                theGUI.style.top = "";
            }
            htmlElement.value = theGUI.innerHTML;
        }
    }
});
 
////
// customize Draggable
////
KitpagesDraggable = Class.create();
 
KitpagesDraggable.prototype = Object.extend (
    Rico.Draggable.prototype,
{
    getSingleObjectDragGUI: function() {
        var el = this.htmlElement;
        var div = document.createElement("div");
        div.className = 'draggable';
        new Insertion.Top( div, el.innerHTML);
        return div;
   }
});
 
////
// onload configuration
////
Event.observe(window, 'load', initRicoDnd, false);
function initRicoDnd() {
    // enregistre les éléments "glissables"
    dndMgr.registerDraggable( 
                new KitpagesDraggable('test-rico-dnd','drag1') );
    dndMgr.registerDraggable( 
                new KitpagesDraggable('test-rico-dnd','drag2') );
    // enregistre les zones cibles
    var dropList = document.getElementsByClassName("kitDropZone");
    dropList.each(function(el) {
        dndMgr.registerDropZone( new KitpagesDropzone(el) );
    });
}