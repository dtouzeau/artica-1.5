// -------------------------//
// Auteur : Morillon Alain
// 25/11/2005
// Version V.1.0
// -------------------------//
var ns6=(document.getElementById)? true:false;
var ie4=(document.all)? true:false;
var ie5=false;
if(ie4){
   if((navigator.userAgent.indexOf('MSIE 5')> 0)||(navigator.userAgent.indexOf('MSIE 6')> 0)){
      ie5=true;
   } if(ns6){
      ns6=false;
   }
}
// Var globales
var LastId=0;
// Tableaux contenant les elements du drag and drop
var DragElemOrig = new Array;
var DragElem = new Array;
var DragHashElem = new Array;
// La position de X de la ligne mediane de 't1'
var PositionXdrop=-1;
// Decalage du scroll de 't1'
var decalageX=-1;
var decalageY=-1;


// Creation d'une division dans 't1' 
// texte -> le label de la division 
// where -> 'drag' or 'drop'
// id    -> id de la division 
function am_create_drag(event,texte,where,id)
{
var idunique;
var div = document.createElement("div");
var contener=document.getElementById('t1');
var ccsmove;
if (ns6) {
   contener.setAttribute("onmouseup",'');
   contener.setAttribute("onmousedown",'');
   div.setAttribute("onmousedown",'am_setmoving(event,\''+id+'\')');
} else {
   contener.onmousedown='';
   contener.onmouseup='';
   // Pour eviter la selection avec IE
   contener.onselectstart=function(){return false};
   div.onmousedown=function(){am_setmoving(event,id)};
}
contener.appendChild(div);
div.setAttribute("class","move");
div.id=id;
div.className="move";
if (document.getElementById("move")) {;
   am_copy_style(document.getElementById("move"),div);
   document.getElementById("move").style.visibility='hidden';
}
div.style.display='inline';
div.style.visibility='visible';
div.style.position='absolute';
div.style.cursor='move';
if (!div.style.border) div.style.border='1px solid #80f070';
if (!div.style.fontSize) div.style.fontSize='12px';
if (!div.style.backgroundColor) div.style.backgroundColor='#fffff0';
if (where == 'drop') {
    div.style.left=PositionXdrop;
} else {
    div.style.left=0;
}
if (texte) {
   var t = document.createTextNode(texte);
   div.appendChild(t);
}
y=(DragHashElem[id].nitem)*(parseInt(div.offsetHeight)+1);
x=parseInt(div.style.left);
div.style.top=y
div.style.width=parseInt(div.offsetWidth);
div.style.height=parseInt(div.offsetHeight);
DragHashElem[id].x=x;
DragHashElem[id].y=y;
return(div.id);
}

// Copy de style de l'objet source vers l'objet destination
function am_copy_style (source,destination)
{
var attr=source.getAttribute("style");
if (ns6) {
   destination.setAttribute("style",attr);
} else {
   for ( i in attr) {
      type = typeof i;
      v=i.indexOf('-');
      if (type == 'string' && i && v<0) {
         var shell = 'destination.style.'+i+'=source.style.'+i;
         if (eval('source.style.'+i)) {
            eval(shell);
         }
      }
   }
}
}

// Trie de la position en Y du tableau DragElem a partir de la position en y de chaque element
function am_sort_y(a,b)
{
return((a.y*500-a.x/10)-(b.y*500-b.x/10));
}

// Cette operation permet de repositionner les div(s) de 't1' à partir de leur position en Y
function am_orderdiv (type_traite,startx)
{
var taby=DragElem.sort(am_sort_y);
var x,y,i,div;
y=0
x=startx;
am_delete_options(type_traite);
for (i=0;i<taby.length;i++) {
    if (taby[i].where==type_traite) { 
       am_new_option(taby[i].id,type_traite,taby[i].value);
       div=document.getElementById(taby[i].id);
       div.style.left=x;
       div.style.top=y;
       DragHashElem[taby[i].id].x=x;
       DragHashElem[taby[i].id].y=y;
       y=y+parseInt(div.offsetHeight-1);
    }
}
}

// Arret du mouvement de l'objet apres l'evenement mouseup
function am_resetmoving (event)
{
if (!LastId) {
   return;
}
var id=LastId;
am_orderdiv('drag',0);
am_orderdiv('drop',PositionXdrop);
document.getElementById(id).style.zIndex=0;
LastId=0;
}

// Demarrage du mouvement de l'objet apres l'evenement mousedown
function am_setmoving (event,v)
{
if (v) LastId=v;
var contener=document.getElementById('t1');
if (document.addEventListener) {
   contener.addEventListener("mousemove", am_moving, true);
   contener.addEventListener("mouseup", am_resetmoving, true);
} else {
   document.getElementById('t1').onmousemove = am_moving;
   document.getElementById('t1').onmouseup = am_resetmoving;
}
document.getElementById(LastId).style.zIndex=1;
}


// Mouvement de l'objet div et calcul de son deplacement dans 't1'
function am_moving (evt)
{
var id;
if (LastId == 0) {
   return;
}
if (ns6) {
   event=evt;
} else {
   evt=window.event;
}
id=LastId;
var x=evt.screenX
var y=evt.screenY
var rx=evt.offsetX;
var ry=evt.offsetY;
var contener=document.getElementById('t1');
var drag=document.getElementById(id);
if (drag==null) {
        alert('Bug!');
        return;
}
var gapX=parseInt(contener.style.left);
var gapY=parseInt(contener.style.top);
var sizeX=parseInt(contener.style.width);
var sizeY=parseInt(contener.style.height);
var sizedragX=parseInt(drag.style.width);
var sizedragY=parseInt(drag.style.height);
var gapXdrag=parseInt(drag.style.left);
var gapYdrag=parseInt(drag.style.top);
x=gapX=parseInt(evt.clientX)+ parseInt(document.body.scrollLeft)+contener.scrollLeft-gapX-sizedragX/2-decalageX;
y=gapY=parseInt(evt.clientY)+ parseInt(document.body.scrollTop)+contener.scrollTop-gapY-sizedragY/2-decalageY;

if (x<0) {return;} if (y<0) {return;} if (y>sizeY+contener.scrollTop-parseInt(drag.style.height)) {return;} if (x>sizeX+contener.scrollLeft-parseInt(drag.style.width)) {return;}
if ( x<sizeX/2) {
   if (x > (sizeX-sizedragX)/2) {
      x=PositionXdrop;
   } else {
      if (x > (sizeX/2 -sizedragX)) {
         x=sizeX/2-sizedragX;
      }
   }
}
var v=document.getElementById('separatator');
v.style.height=contener.scrollTop+sizeY;
if ( x<sizeX/2) {
   DragHashElem[id].where='drag';
} else {
   DragHashElem[id].where='drop';
}
drag.style.top=y;
drag.style.left=x;
DragHashElem[id].x=x;
DragHashElem[id].y=y;
}

// Retourne la position en X de l'objet obj
function am_findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

// Retourne la position en Y de l'objet obj
function am_findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

// Ma signature si vous souhaitez l'oter 
function am_signature ()
{
var o=document.createElement("div");
var a=document.createElement("a");
a.setAttribute("href","http://av.schneider.free.fr/alain/index.html");
var t = document.createTextNode("Alain.M");
o.setAttribute("style","position: absolute; font-size: 8px; color: #ec801d; text-align: right; vertical-align: bottom;");
a.setAttribute("style","font-size: 8px; color: #ec801d;");
o.style.position='absolute';
o.style.fontSize='8px';
o.style.color='#ec801d';
a.style.fontSize='8px';
a.style.color='#ec801d';
o.style.textAlign='right';
o.style.verticalAlign='bottom';
a.appendChild(t); o.appendChild(a); 
o.style.top=parseInt(document.getElementById('t1').style.height)-20;
o.style.left=parseInt(document.getElementById('t1').style.width)-30;
document.getElementById('t1').appendChild(o);
}

// Initialisation de la div 't1' 
function _initDragEtDrop (event)
{
// Positionnement des paramètres obligatoires
if (!document.getElementById('t1').style.position) document.getElementById('t1').style.position='relative';
if (!document.getElementById('t1').style.top) document.getElementById('t1').style.top='0px';
if (!document.getElementById('t1').style.left) document.getElementById('t1').style.left='0px';
if (!document.getElementById('t1').style.width) document.getElementById('t1').style.width='300px';
if (!document.getElementById('t1').style.height) document.getElementById('t1').style.height='250px';
am_signature();
decalageX=am_findPosX(document.getElementById('t1'));
decalageY=am_findPosY(document.getElementById('t1'));
decalageX-=parseInt(document.getElementById('t1').style.left);
decalageY-=parseInt(document.getElementById('t1').style.top);
// On ajoute 2 pour passer de l'autre cote de la barre mediane
PositionXdrop=parseInt(document.getElementById('t1').style.width)/2+1+2;
// Création de la ligne mediane
var d=document.createElement("div");
d.setAttribute("id",'separatator');
d.style.position='absolute';
d.style.left=parseInt(document.getElementById('t1').style.width)/2-4;
d.style.top='0px';
d.style.width='0px';
d.style.borderLeft='8px groove #c080c0';
d.style.height=document.getElementById('t1').style.height;
d.style.marginTop='0px';
d.style.zIndex=0;
t=document.getElementById('t1');
t.appendChild(d);
// Les select drag and drop doivent etre a choix multiple
document.getElementsByName("drag")[0].multiple=true;
document.getElementsByName("drop")[0].multiple=true;
if (!ns6) {
   event=window.event;
}
// Initialiation des tableaux DragHashElem DragElem DragElemOrig
var id,texte;
var n,i;
var select=document.getElementsByName("drop")[0];
var o=select.options;
for (n=0;o.length>0;) {
   id='drop'+n;
   if (o[0].label && o[0].label.length>0) {
      texte=o[0].label;
   } else {
      texte=o[0].value;
   }
   DragElem[n]= new am_ElemDragorDrop(id,'drop',o[0].value,texte,-1,-1,n);
   DragElemOrig[n]=DragHashElem[id]=DragElem[n];
   n++;
   select.remove(0);
}
select=document.getElementsByName("drag")[0];
var o=select.options;
for (i=0;o.length>0;i++) {
   id='drag'+i;
   if (o[0].label && o[0].label.length>0) {
      texte=o[0].label;
   } else {
      texte=o[0].value;
   }
   DragElem[n]= new am_ElemDragorDrop(id,'drag',o[0].value,texte,-1,-1,i);
   DragElemOrig[n]=DragHashElem[id]=DragElem[n];
   n++;
   select.remove(0);
}
// Creation des div et options du drag et drop
am_InitDragAndDrop(event,DragElem);
}

// Creation des div et options du drag et drop
function am_InitDragAndDrop(event,tab_elem)
{
var i;
var label;
am_delete_options('drag');
am_delete_options('drop');
for (i=0;i<tab_elem.length;i++) {
    if (tab_elem[i].type_orig == 'drag') {
       am_create_drag(event,tab_elem[i].texte,tab_elem[i].type_orig,tab_elem[i].id);
       am_new_option(tab_elem[i].id,'drag',tab_elem[i].value);
    } else {
       am_create_drag(event,tab_elem[i].texte,tab_elem[i].type_orig,tab_elem[i].id);
       am_new_option(tab_elem[i].id,'drop',tab_elem[i].value);
    }
}
}

// Reset du Drag and Drop en reconstruisant DragElem à son état initiale
function ResetDragAndDrop (event)
{
var i,v;
if (LastId!=0) return;
if (!ns6) {
   event=window.event;
}
for (i=0;i<DragElem.length;i++) {
    v=document.getElementById(DragElem[i].id);
    document.getElementById('t1').removeChild(v);
    DragElem[i].where=DragElem[i].type_orig;
    DragElem[i].x=1;
    DragElem[i].y=0;
}
am_InitDragAndDrop(event,DragElemOrig);
}

// Propriete de l'objet qui se deplace
function am_ElemDragorDrop(id,type_orig,value,texte,x,y,nitem)
{
     // id
     this.id = id;
     // son indice
     this.nitem = nitem;
     // son emplacement a l'etat initial 'drag' or 'drop'
     this.type_orig = type_orig;
     // label et texte
     this.value = value;
     this.texte = texte;
     // Sa position dans 't1'
     this.x = x;
     this.y = y;
     // son emplacement
     this.where = type_orig;
}
   
// Suppression des options d'un select
function am_delete_options(insel)
{
var select=document.getElementsByName(insel)[0];
var i;
var o;
o=select.options;
while (o.length>0) {
   select.remove(0);
}
}

// Insertion d'option dans un select
function am_new_option(id,insel,value)
{
var select=document.getElementsByName(insel)[0];
var i;
var idoption='opt:'+id;
if (!id) {
}
var newo=document.createElement("option");
newo.value=value;
var t = document.createTextNode(value);
newo.setAttribute("id",idoption);
newo.appendChild(t);
select.appendChild(newo);
newo.selected=true;
newo.setAttribute("selected",true);
}

// Future
function am_findClassName(className)
{
var is,ir,s;
for (is=0;document.styleSheets[is]!=null;is++) {
   for (ir=0;ir<document.styleSheets[is].cssRules.length;ir++) { 
       s=document.styleSheets[is].cssRules[ir];
       if (s.selectorText==className) {
          return(s);
       }
   }
}
return('');
}


