// jQuery File Tree Plugin
//
// Version 1.01.0-aza1
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Modified by Carl Fürstenberg
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           fakeTopRoot    - show an fake top root, for situations where you won't have a direct single root in database
//           fakeTopRootText - text to show as the fake root, default is a single dot
//           script         - location of the serverside AJAX file to use; default = jqueryFileTree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//           fileCallback   - Callback when a file is choosen
//           dirExpandCallback - callback when a directory is expanded
//           dirCollapseCallback - callback when a directory is collapsed, return of false avoids collapsing
//           moveCallback   - callback when entity is moved
//           readyCallback  - callback to be fired after initial setup after initial ajax call
//           spinnerImage   - image to be used as spinner, should be an horizontal image with subimages laied out
//           spinnerWidth   - width of separate spinners
//           spinnerHeight  - height of separate spinners
//           spinnerSpeed   - speed of spinner
//           dragAndDrop    - enable drag and frop functionallity, requires jquery.event.drag.js and jquery.event.drop.js
//
//
// History:
//
// 1.01-0aza1 - added callbacks, dragndrop, fakeroot and updated spinner, fix i18n probs + some more stuff
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// jQuery File Tree is licensed under a Creative Commons License and is copyrighted (C)2008 by Cory S.N. LaViska.
// For details, visit http://creativecommons.org/licenses/by/3.0/us/
//
if(jQuery) (function($){

		$.extend($.fn, {
				fileTree: function(o) {
					// Defaults
					if( !o ) var o = {};
					if( o.root == undefined ) o.root = '/';
					if( o.fakeTopRoot == undefined ) o.fakeTopRoot = false;
					if( o.fakeTopRootText == undefined ) o.fakeTopRootText = '.';
					if( o.script == undefined ) o.script = 'jqueryFileTree.php';
					if( o.folderEvent == undefined ) o.folderEvent = 'click';
					if( o.expandSpeed == undefined ) o.expandSpeed= 500;
					if( o.hoverTimeout == undefined ) o.hoverTimeout= 500;
					if( o.collapseSpeed == undefined ) o.collapseSpeed= 500;
					if( o.expandEasing == undefined ) o.expandEasing = null;
					if( o.collapseEasing == undefined ) o.collapseEasing = null;
					if( o.multiFolder == undefined ) o.multiFolder = true;
					if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';
					if( o.fileCallback == undefined ) o.fileCallback = function(file) {  };
					if( o.dirExpandCallback == undefined ) o.dirExpandCallback = function(dir) {  };
					if( o.dirCollapseCallback == undefined ) o.dirCollapseCallback = function(dir) {  };
					if( o.moveCallback == undefined ) o.moveCallback = function(from, to, directory) {  };
					if( o.readyCallback == undefined ) o.readyCallback = function() {  };
					if( o.spinnerImage == undefined ) o.spinnerImage = 'spinner.png' ;
					if( o.spinnerWidth == undefined ) o.spinnerWidth = 16;
					if( o.spinnerHeight == undefined ) o.spinnerHeight = 16;
					if( o.spinnerSpeed == undefined ) o.spinnerSpeed = 25;
					if( o.dragAndDrop == undefined ) o.dragAndDrop = true;

					$(this).each( function() {

							function showTree(c, t, n) {
								var frame = 1;
								var frames = 1;
								spinner = $('<div />').addClass('spinner');
								
								spinner.height(o.spinnerHeight);
								spinner.width(o.spinnerWidth);

								$(c).addClass('wait');
								$(c).prepend(spinner);
								spinner.css("background-image","url("+o.spinnerImage+")");
								spinner.css("background-position","0px 0px");
								spinner.css("background-repeat","no-repeat");
								img = new Image();
								img.src = o.spinnerImage;
								img.onload = function() {
									frames = img.width/o.spinnerWidth;
								};
								function spinnerRedraw() {
									// If we've reached the last frame, loop back around
									if(frame >= frames) {
										frame = 1;
									}

									// Set the background-position for this frame
									pos = "-"+(frame*o.spinnerWidth)+"px 0px";

									spinner.css("background-position",pos);

									// Increment the frame count
									frame++;
								}
								animation = setInterval(spinnerRedraw,1000/o.spinnerSpeed);

								$(".jqueryFileTree.start").remove();
								$.ajax({
										url: o.script,
										type: 'POST',
										data: { dir: t },
										dataType: 'json',
										timeout: 8000,
										success: function(data) {
											if( data.error ) {
												alert( data.html );
												return;
											}
											$(c).find('.start').html('');
											root = $(c).removeClass('wait');
											clearInterval(animation);
											spinner.remove();
											root.append(data.html);
											if( o.root == t ) {
												$(c).find('UL:hidden').show();
											} else {
												$(c).find('UL:hidden').slideDown({duration: o.expandSpeed, easing: o.expandEasing});
											}
											bindTree(c);
											if( n ) n(c);
											o.readyCallback(root);
										}
									});
							}

							function bindTree(t) {
								var oldrel = "";
								$(t).find('LI A').bind(o.folderEvent, function() {
										if( $(this).parent().hasClass('directory') ) {
											if( $(this).parent().hasClass('collapsed') ) {
												// Expand

												if( !o.multiFolder ) {
													$(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
													$(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
												}
												$(this).parent().find('UL').remove(); // cleanup
												showTree( $(this).parent(), $(this).attr('rel') );
												$(this).parent().removeClass('collapsed').addClass('expanded');
												if( oldrel != $(this).attr('rel') ) {
													o.dirExpandCallback($(this).attr('rel'));
												}
											} else {
												if( ! o.dirCollapseCallback($(this).attr('rel')) ) {
													return false;
												}
												// Collapse
												$(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
												$(this).parent().removeClass('expanded').addClass('collapsed');
											}
										} else if( oldrel != $(this).attr('rel') ) {
											o.fileCallback($(this).attr('rel'));
										}
										oldrel = $(this).attr('rel');
										return false;
									});
								if( o.dragAndDrop ) {
									all = $(t).find('LI');
									all.bind( "dragstart", 
										function( event ){
											if( ! $(event.target).is('a') ) {
												return false;
											}
											$.dropManage(); 
											// ref the "dragged" element, make a copy
											var $drag = $( this ), $proxy = $drag.clone();
											// modify the "dragged" source element
											$drag.addClass("outline");
											// insert and return the "proxy" element                
											return $proxy.appendTo( document.body ).addClass("ghost");
										});
									all.bind( "drag", function( event ){
											// update the "proxy" element position
											$( event.dragProxy ).css({ left: event.offsetX, top: event.offsetY });
										});
									all.bind( "dragend", function( event ){
											// remove the "proxy" element
											$( event.dragProxy ).fadeOut( "normal", function(){
													$( this ).remove();
												});
											// if there is no drop AND the target was previously dropped
											if ( !event.dropTarget && $(this).parent().is(".drop") ){
											}
											// restore to a normal state
											$( this ).removeClass("outline");      

										});

									dirs = $(t).find('LI.directory > A');

									dirs.bind( "dropstart", function( event ){

											// don't drop in itself or children of self
											if($(event.dragTarget).hasClass('directory') && 
												$(this).parents('li.directory').filter( 
													function(){
														return $(this).children('a').attr('rel') == $(event.dragTarget).children('a').attr('rel') 
													} 
												).length > 0 ) {
												return false;
											}
											if( $(this).parent().hasClass('collapsed') ) {
												$(this).oneTime( o.hoverTimeout, 'expand', function() {
														// Expand
														if( !o.multiFolder ) {
															$(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
															$(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
														}
														$(this).parent().find('UL').remove(); // cleanup
														showTree( $(this).parent(), $(this).attr('rel') );
														$(this).parent().removeClass('collapsed').addClass('expanded');
													});
											}

											// activate the "drop" target element
											$( this ).parent().addClass("active");
											$.dropManage(); 
										});
									dirs.bind( "drop", function( event ){


											o.moveCallback($(event.dragTarget).children('a:first').attr('rel'),  $(this).attr('rel'), $(event.dragTarget).hasClass('directory') ? true : false );
											if( $(this).parent().hasClass('collapsed') ) {
												// Expand
												if( !o.multiFolder ) {
													$(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
													$(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
												}
												$(this).parent().find('UL').remove(); // cleanup
												showTree( $(this).parent(), $(this).attr('rel'), function(target) {

														$( target ).children('ul:first').append( event.dragTarget );
													});
												$(this).parent().removeClass('collapsed').addClass('expanded');
											} else {
												// if there was a drop, move some data...
												$( this ).parent().children('ul:first').append( event.dragTarget );
											}
											// output details of the action...
										});
									dirs.bind( "dropend", function( event ){
											$(this).stopTime('expand');
											// deactivate the "drop" target element
											$( this ).parent().removeClass("active");
										});
								}
								// Prevent A from triggering the # on non-click events
								if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });
							}
							// For a fake root, we use an simple div
							if( o.fakeTopRoot ) {
								$(this).empty();
								$(this).append(
									$('<div />').addClass("jqueryFileTreeFakeRoot").append(
										o.fakeTopRootText 
									)
								).append(
									$('<div />').addClass("jqueryFileTreeRealRoot").append( 
										$('<ul />').addClass("jqueryFileTree start").append(
											$('<li />').addClass("wait").append(o.loadMessage)
										)
									)
								);

								if( o.dragAndDrop ) {
									fakeroot = $(this).find('.jqueryFileTreeFakeRoot');
									fakeroot.bind( "dropstart", function( event ){
											if( $(event.dragTarget).parent().parent().hasClass('jqueryFileTreeRealRoot') ) {
												return false;
											}

											// activate the "drop" target element
											$( this ).addClass("active");
											$.dropManage(); 
										});
									fakeroot.bind( "drop", function( event ){


											o.moveCallback($(event.dragTarget).children('a:first').attr('rel'), null, $(event.dragTarget).hasClass('directory') ? true : false );
											// if there was a drop, move some data...
											$( this ).parent().children('.jqueryFileTreeRealRoot').children('ul.jqueryFileTree').append( event.dragTarget );
											// output details of the action...
										});
									fakeroot.bind( "dropend", function( event ){
											// deactivate the "drop" target element
											$( this ).removeClass("active");
										});
								}
								// Get the initial file list
								showTree( $(this).children(".jqueryFileTreeRealRoot"), o.root );
							} else {
								$(this).empty();
								$(this).append(
									$('<ul />').addClass("jqueryFileTree start").append(
										$('<li />').addClass("wait").append(o.loadMessage)
									)
								);
								showTree( $(this), o.root );
							}
						});
				}
			});

	})(jQuery);
