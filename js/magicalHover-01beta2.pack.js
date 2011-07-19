/***
* # -- BEGIN LICENSE BLOCK ----------------------------------
* # magicalHover is a plugin for jQuery.
* # magicalHover can have a nice animation during the "rollover" on the images
* # Copyright ClashDesign(C) and magixcjQuery 2009  Gerits Aurelien.
* # This program is free software: you can redistribute it and/or modify
* # it under the terms of the GNU Affero General Public License as
* # published by the Free Software Foundation, either version 3 of the
* # License, or (at your option) any later version.
* # This program is distributed in the hope that it will be useful,
* # but WITHOUT ANY WARRANTY; without even the implied warranty of
* # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* # GNU Affero General Public License for more details.
* # You should have received a copy of the GNU Affero General Public License
* # along with this program.  If not, see <http://www.gnu.org/licenses/>.
* # -- END LICENSE BLOCK ------------------------------------
****/
/**
*
* Changelog Version 0.1 beta 2
*
*/
(function($){$.fn.magicalHover=function(b){var c=$.extend({speedView:200,speedRemove:400,altAnim:false,speedTitle:400,debug:false},b);var d=$.extend(c,b);function e(s){if(typeof console!="undefined"&&typeof console.debug!="undefined"){console.log(s)}else{alert(s)}}if(d.speedView==undefined||d.speedRemove==undefined||d.altAnim==undefined||d.speedTitle==undefined){e('speedView: '+d.speedView);e('speedRemove: '+d.speedRemove);e('altAnim: '+d.altAnim);e('speedTitle: '+d.speedTitle);return false}if(d.debug==undefined){e('speedView: '+d.speedView);e('speedRemove: '+d.speedRemove);e('altAnim: '+d.altAnim);e('speedTitle: '+d.speedTitle);return false}if(typeof d.speedView!="undefined"||typeof d.speedRemove!="undefined"||typeof d.altAnim!="undefined"||typeof d.speedTitle!="undefined"){if(d.debug==true){e('speedView: '+d.speedView);e('speedRemove: '+d.speedRemove);e('altAnim: '+d.altAnim);e('speedTitle: '+d.speedTitle)}$(this).hover(function(){$(this).css({'z-index':'10'});$(this).find('img').addClass("hover").stop().animate({marginTop:'-110px',marginLeft:'-110px',top:'50%',left:'50%',width:'174px',height:'174px',padding:'20px'},d.speedView);if(d.altAnim==true){var a=$(this).find("img").attr("alt");if(a.length!=0){$(this).prepend('<span class="title">'+a+'</span>');$('.title').animate({marginLeft:'-42px',marginTop:'90px'},d.speedTitle).css({'z-index':'10','position':'absolute','float':'left'})}}},function(){$(this).css({'z-index':'0'});$(this).find('img').removeClass("hover").stop().animate({marginTop:'0',marginLeft:'0',top:'0',left:'0',width:'100px',height:'100px',padding:'5px'},d.speedRemove);$(this).find('.title').remove()})}}})(jQuery);