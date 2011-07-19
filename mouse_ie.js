function pointeurX( e ){
	
var Mouse_X; // Variable globale Position X de la Mouse
var Mouse_Y; // Variable globale Position Y de la Mouse
  var DocRef;    // Variable pour IE uniquement

  // L'événement est passée à la fonction
  // donc tous sauf IE…
  if( e){                     // Dans ce cas on obtient directement la position dans la page
    Mouse_X = e.pageX;
    Mouse_Y = e.pageY;
  }
  else{                      // Dans ce cas on obtient la position relative à la fenêtre d'affichage
    Mouse_X = event.clientX;
    Mouse_Y = event.clientY;
    if( document.documentElement && document.documentElement.clientWidth) // Donc DOCTYPE
      DocRef = document.documentElement;   // Dans ce cas c'est documentElement qui est réfèrence
    else
      DocRef = document.body;                    // Dans ce cas c'est body qui est réfèrence

    //-- On rajoute la position liée aux ScrollBars
    Mouse_X += DocRef.scrollLeft;
    Mouse_Y += DocRef.scrollTop;
  }
  
  return Mouse_X;
  
}

function pointeurY( e ){
	
var Mouse_X; // Variable globale Position X de la Mouse
var Mouse_Y; // Variable globale Position Y de la Mouse
  var DocRef;    // Variable pour IE uniquement

  // L'événement est passée à la fonction
  // donc tous sauf IE…
  if( e){                     // Dans ce cas on obtient directement la position dans la page
    Mouse_X = e.pageX;
    Mouse_Y = e.pageY;
  }
  else{                      // Dans ce cas on obtient la position relative à la fenêtre d'affichage
    Mouse_X = event.clientX;
    Mouse_Y = event.clientY;
    if( document.documentElement && document.documentElement.clientWidth) // Donc DOCTYPE
      DocRef = document.documentElement;   // Dans ce cas c'est documentElement qui est réfèrence
    else
      DocRef = document.body;                    // Dans ce cas c'est body qui est réfèrence

    //-- On rajoute la position liée aux ScrollBars
    Mouse_X += DocRef.scrollLeft;
    Mouse_Y += DocRef.scrollTop;
  }
  
  return Mouse_Y;
  
}