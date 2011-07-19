//---------------------------------+
//  CARPE  S l i d e r         |               |
//  By Tom Hermansson Snickars     |
//  Copyright CARPE Design         |
//  http://carpe.ambiprospect.com/ |
//  Modified by Roshan Bhattarai   |
//	http://roshanbh.com.np         |
//  Contact for custom scripts     |
//  or implementation help.        |
//---------------------------------+

// Global vars. You don't need to make changes here .
// Changing the attributes in your (X)HTML file is enough.
var carpeDefaultSliderLength      = 100
var carpeSliderDefaultOrientation = 'horizontal'
var carpeSliderClassName          = 'carpe_slider'
var carpeSliderDisplayClassName   = 'carpe_slider_display'

// carpeGetElementsByClass: Cross-browser function that returns
// an array with all elements that have a class attribute that
// contains className
function carpeGetElementsByClass(className)
{
	var classElements = new Array()
	var els = document.getElementsByTagName("*")
	var elsLen = els.length
	var pattern = new RegExp("\\b" + className + "\\b")
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i]
			j++
		}
	}
	return classElements;
}
// carpeLeft: Cross-browser version of "element.style.left"
// Returns or sets the horizontal position of an element.
function carpeLeft(elmnt, pos)
{

	if (!(elmnt = document.getElementById(elmnt))) return 0;
	if (elmnt.style && (typeof(elmnt.style.left) == 'string')) {
		if (typeof(pos) == 'number') elmnt.style.left = pos + 'px';
		else {
			pos = parseInt(elmnt.style.left);
			if (isNaN(pos)) pos = 0;
		}
	}
	else if (elmnt.style && elmnt.style.pixelLeft) {
		if (typeof(pos) == 'number') elmnt.style.pixelLeft = pos;
		else pos = elmnt.style.pixelLeft;
	}
	return pos;
}
// carpeTop: Cross-browser version of "element.style.top"
// Returns or sets the vertical position of an element.
function carpeTop(elmnt, pos)
{
	if (!(elmnt = document.getElementById(elmnt))) return 0;
	if (elmnt.style && (typeof(elmnt.style.top) == 'string')) {
		if (typeof(pos) == 'number') elmnt.style.top = pos + 'px';
		else {
			pos = parseInt(elmnt.style.top);
			if (isNaN(pos)) pos = 0;
		}
	}
	else if (elmnt.style && elmnt.style.pixelTop) {
		if (typeof(pos) == 'number') elmnt.style.pixelTop = pos;
		else pos = elmnt.style.pixelTop;
	}
	return pos;
}
// moveSlider: Handles slider and display while dragging
function moveSlider(evnt)
{
	var evnt = (!evnt) ? window.event : evnt; // The mousemove event
	if (mouseover) { // Only if slider is dragged
		x = slider.startOffsetX + evnt.screenX // Horizontal mouse position relative to allowed slider positions
		y = slider.startOffsetY + evnt.screenY // Horizontal mouse position relative to allowed slider positions
		if (x > slider.xMax) x = slider.xMax // Limit horizontal movement
		if (x < 0) x = 0 // Limit horizontal movement
		if (y > slider.yMax) y = slider.yMax // Limit vertical movement
		if (y < 0) y = 0 // Limit vertical movement
		carpeLeft(slider.id, x)  // move slider to new horizontal position
		carpeTop(slider.id, y) // move slider to new vertical position
		sliderVal = x + y // pixel value of slider regardless of orientation
		sliderPos = (slider.distance / display.valuecount) * 
			Math.round(display.valuecount * sliderVal / slider.distance)
		v = Math.round((sliderPos * slider.scale + slider.from) * // calculate display value
			Math.pow(10, display.decimals)) / Math.pow(10, display.decimals)
		display.value = v // put the new value in the slider display element
		return false
	}
	return
}
// slide: Handles the start of a slider move.
function slide(evnt)
{
	if (!evnt) evnt = window.event; // Get the mouse event causing the slider activation.
	slider = (evnt.target) ? evnt.target : evnt.srcElement; // Get the activated slider element.
	dist = parseInt(slider.getAttribute('distance')) // The allowed slider movement in pixels.
	slider.distance = dist ? dist : carpeDefaultSliderLength // Deafault distance from global var.
	ori = slider.getAttribute('orientation') // Slider orientation: 'horizontal' or 'vertical'.
	orientation = ((ori == 'horizontal') || (ori == 'vertical')) ? ori : carpeSliderDefaultOrientation
		// Default orientation from global variable.
	displayId = slider.getAttribute('display') // ID of associated display element.
	display = document.getElementById(displayId) // Get the associated display element.
	display.sliderId = slider.id // Associate the display with the correct slider.
	dec = parseInt(display.getAttribute('decimals')) // Number of decimals to be displayed.
	display.decimals = dec ? dec : 0 // Default number of decimals: 0.
	val = parseInt(display.getAttribute('valuecount'))  // Allowed number of values in the interval.
	display.valuecount = val ? val : slider.distance + 1 // Default number of values: the sliding distance.
	from = parseFloat(display.getAttribute('from')) // Min/start value for the display.
	from = from ? from : 0 // Default min/start value: 0.
	to = parseFloat(display.getAttribute('to')) // Max value for the display.
	to = to ? to : slider.distance // Default number of values: the sliding distance.
	slider.scale = (to - from) / slider.distance // Slider-display scale [value-change per pixel of movement].
	if (orientation == 'vertical') { // Set limits and scale for vertical sliders.
		slider.from = to // Invert for vertical sliders. "Higher is more."
		slider.xMax = 0
		slider.yMax = slider.distance
		slider.scale = -slider.scale // Invert scale for vertical sliders. "Higher is more."
	}
	else { // Set limits for horizontal sliders.
		slider.from = from
		slider.xMax = slider.distance
		slider.yMax = 0
	}
	slider.startOffsetX = carpeLeft(slider.id) - evnt.screenX // Slider-mouse horizontal offset at start of slide.
	slider.startOffsetY = carpeTop(slider.id) - evnt.screenY // Slider-mouse vertical offset at start of slide.
	mouseover = true
	document.onmousemove = moveSlider // Start the action if the mouse is dragged.
	document.onmouseup = sliderMouseUp // Stop sliding.
	
	return false
}
// sliderMouseUp: Handles the mouseup event after moving a slider.
// Snaps the slider position to allowed/displayed value. 
function sliderMouseUp()
{
	if (mouseover) {
		v = (display.value) ? display.value : 0 // Find last display value.
		pos = (v - slider.from)/(slider.scale) // Calculate slider position (regardless of orientation).
		if (slider.yMax == 0) {
			pos = (pos > slider.xMax) ? slider.xMax : pos
			pos = (pos < 0) ? 0 : pos
			carpeLeft(slider.id, pos) // Snap horizontal slider to corresponding display position.
		}
		if (slider.xMax == 0) {
			pos = (pos > slider.yMax) ? slider.yMax : pos
			pos = (pos < 0) ? 0 : pos
			carpeTop(slider.id, pos) // Snap vertical slider to corresponding display position.
		}
		if (document.removeEventListener) { // Remove event listeners from 'document' (W3C).
			document.removeEventListener('mousemove', moveSlider, false)
			document.removeEventListener('mouseup', sliderMouseUp, false)
		}
		else if (document.detachEvent) { // Remove event listeners from 'document' (IE).
			document.detachEvent('onmousemove', moveSlider)
			document.detachEvent('onmouseup', sliderMouseUp)
		}
	}
	//Here is the function call for ajax to save values
	setSliderVal(document.getElementById('slider1').style.left)
	mouseover = false // Stop the sliding.
}
function focusDisplay(evnt)
{
	if (!evnt) evnt = window.event; // Get the mouse event causing the display activation.
	display = (evnt.target) ? evnt.target : evnt.srcElement; // Get the activated display element.
	lock = display.getAttribute('typelock') // Is the user allowed to type into the display?
	if (lock == 'on') {
		display.blur()
	}
	return
}
function StartSlider()
{
	sliders = carpeGetElementsByClass(carpeSliderClassName) // Find the horizontal sliders.
	for (i = 0; i < sliders.length; i++) {
		sliders[i].onmousedown = slide // Attach event listener.
	}
	displays = carpeGetElementsByClass(carpeSliderDisplayClassName) // Find the displays.
	for (i = 0; i < displays.length; i++) {
		displays[i].onfocus = focusDisplay // Attach event listener.
	}
}

