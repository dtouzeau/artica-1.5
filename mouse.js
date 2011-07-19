/**
 * @author touzeau
 */
// capture les evenements sous Netscape Navigator
if (document.layers) {
  document.captureEvents(Event.MOUSEDOWN);
  document.captureEvents(Event.MOUSEUP);
  document.captureEvents(Event.MOUSEMOVE);
}

// --- Fonctions ---

// retourne vrai si le dernier clic de souris concerne le bouton droit
function boutonDroit(e) {
  if (window.event)
    return (window.event.button==2);
  else
    return (e.which==3);
} // fin boutonDroit(e)

// retourne vrai si le dernier clic de souris concerne le bouton gauche
function boutonGauche(e) {
  if (window.event)
    return (window.event.button==1);
  else {
    if (e.type=="mousemove")
      return (false);
    else
      return (e.which==1);
  }
} // fin boutonGauche(e)

// retourne vrai si le dernier clic de souris concerne le bouton du milieu
function boutonMilieu(e) {
  if (window.event)
    return ((window.event.button==3) || (window.event.button==4));
  else
    return (e.which==2);
} // fin boutonMilieu(e)

// retourne la position horizontale a l'ecran du pointeur de la souris
function pointeurEcranX(e) {
  if (window.event)
    return (window.event.screenX);
  else
    return(e.screenX);
} // fin pointeurEcranX(e)

// retourne la position verticale a l'ecran du pointeur de la souris
function pointeurEcranY(e) {
  if (window.event)
    return (window.event.screenY);
  else
    return(e.screenY);
} // fin pointeurEcranY(e)

// retourne la position horizontale sur la page du pointeur de la souris
function pointeurX(e) {
  if (window.event)
    return (window.event.clientX);
  else
    return(e.pageX);
} // fin pointeurX(e)

// retourne la position verticale sur la page du pointeur de la souris
function pointeurY(e) {
  if (window.event)
    return (window.event.clientY);
  else
    return(e.pageY);
} // fin pointeurY(e)