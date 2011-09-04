<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--includes#",implode(" ",$argv))){$GLOBALS["DEBUG_INCLUDES"]=true;}
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.templates.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.ini.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::framework/class.unix.inc\n";}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::frame.class.inc\n";}
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');



if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('display_errors', 1);	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if($GLOBALS["VERBOSE"]){echo " commands= ".implode(" ",$argv)."\n";}
	
if($argv[1]=="--init"){die();}
if($argv[1]=="--config"){build();die();}


function build(){
	
$f[]="<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
$f[]="<fuppes_config version=\"0.7.2.3\">";
$f[]="  <shared_objects>";
$f[]="    <!--<dir>/mnt/music</dir>-->";
$f[]="    <!--<itunes>/Users/.../iTunes.xml</itunes>-->";
$f[]="    <dir>/root/.mldonkey/incoming/files/angelique@touzeau.com/</dir>";
$f[]="  </shared_objects>";
$f[]="  <network>";
$f[]="    <!--empty = automatic detection-->";
$f[]="    <interface>br5</interface>";
$f[]="    <!--empty or 0 = random port-->";
$f[]="    <http_port>35381</http_port>";
$f[]="    <!--list of ip addresses allowed to access fuppes. if empty all ips are allowed-->";
$f[]="    <allowed_ips>";
$f[]="      <!--These are examples of what data you can put between the ip tags where (* => anything, [x-y] => range)-->";
$f[]="      <!--<ip>192.168.0.1</ip>-->";
$f[]="      <!--<ip>192.168.0.[20-100]</ip>-->";
$f[]="      <!--<ip>192.168.0.*</ip>-->";
$f[]="      <!--<ip>192.*.[0-2].[40-*]</ip>-->";
$f[]="    </allowed_ips>";
$f[]="  </network>";
$f[]="  <content_directory>";
$f[]="    <!--a list of possible charsets can be found under:";
$f[]="      http://www.gnu.org/software/libiconv/-->";
$f[]="    <local_charset>UTF-8</local_charset>";
$f[]="    <!--libs used for metadata extraction when building the database. [true|false]-->";
$f[]="    <use_imagemagick>true</use_imagemagick>";
$f[]="    <use_taglib>true</use_taglib>";
$f[]="    <use_libavformat>true</use_libavformat>";
$f[]="  </content_directory>";
$f[]="  <global_settings>";
$f[]="    <temp_dir/>";
$f[]="    <!--uuid is written to and read from <config-dir>/uuid.txt if set to true-->";
$f[]="    <use_fixed_uuid>false</use_fixed_uuid>";
$f[]="  </global_settings>";
$f[]="  <device_settings>";
$f[]="    <!--\"default\" settings are inhertied by specific devices and can be overwritten-->";
$f[]="    <!--do NOT remove the \"default\" device settings-->";
$f[]="    <!--all new file types have to be added to the default settings-->";
$f[]="    <!--adding new file types just to a specific device will have no affect-->";
$f[]="    <device name=\"default\">";
$f[]="      <!--specify the maximum length for file names (0 or empty = unlimited)-->";
$f[]="      <max_file_name_length>0</max_file_name_length>";
$f[]="      <!--[file|container]-->";
$f[]="      <playlist_style>file</playlist_style>";
$f[]="      <show_childcount_in_title>false</show_childcount_in_title>";
$f[]="      <enable_dlna>false</enable_dlna>";
$f[]="      <transcoding_release_delay>4</transcoding_release_delay>";
$f[]="      <file_settings>";
$f[]="        <!--audio files-->";
$f[]="        <file ext=\"mp3\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>audio/mpeg</mime_type>";
$f[]="          <dlna>MP3</dlna>";
$f[]="        </file>";
$f[]="        <file ext=\"ogg\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>application/octet-stream</mime_type>";
$f[]="          <transcode enabled=\"true\">";
$f[]="            <ext>mp3</ext>";
$f[]="            <mime_type>audio/mpeg</mime_type>";
$f[]="            <dlna>MP3</dlna>";
$f[]="            <http_encoding>chunked</http_encoding>";
$f[]="            <decoder>vorbis</decoder>";
$f[]="            <encoder>lame</encoder>";
$f[]="            <bitrate>192</bitrate>";
$f[]="            <samplerate>44100</samplerate>";
$f[]="          </transcode>";
$f[]="        </file>";
$f[]="        <file ext=\"mpc\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>application/octet-stream</mime_type>";
$f[]="          <transcode enabled=\"true\">";
$f[]="            <ext>mp3</ext>";
$f[]="            <mime_type>audio/mpeg</mime_type>";
$f[]="            <dlna>MP3</dlna>";
$f[]="            <http_encoding>chunked</http_encoding>";
$f[]="            <decoder>musepack</decoder>";
$f[]="            <encoder>lame</encoder>";
$f[]="            <bitrate>192</bitrate>";
$f[]="            <samplerate>44100</samplerate>";
$f[]="          </transcode>";
$f[]="        </file>";
$f[]="        <file ext=\"wav\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>audio/x-wav</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"flac\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>audio/x-flac</mime_type>";
$f[]="          <transcode enabled=\"true\">";
$f[]="            <ext>mp3</ext>";
$f[]="            <mime_type>audio/mpeg</mime_type>";
$f[]="            <dlna>MP3</dlna>";
$f[]="            <http_encoding>chunked</http_encoding>";
$f[]="            <decoder>flac</decoder>";
$f[]="            <encoder>lame</encoder>";
$f[]="            <bitrate>192</bitrate>";
$f[]="            <samplerate>44100</samplerate>";
$f[]="          </transcode>";
$f[]="        </file>";
$f[]="        <file ext=\"wma\">";
$f[]="          <type>AUDIO_ITEM</type>";
$f[]="          <mime_type>audio/x-ms-wma</mime_type>";
$f[]="          <dlna>WMAFULL</dlna>";
$f[]="        </file>";
$f[]="        <!--image files-->";
$f[]="        <file ext=\"jpg\">";
$f[]="          <ext>jpeg</ext>";
$f[]="          <type>IMAGE_ITEM</type>";
$f[]="          <mime_type>image/jpeg</mime_type>";
$f[]="          <convert enabled=\"false\">";
$f[]="            <!--<dcraw enabled=\"true\">-q 0</dcraw>-->";
$f[]="            <ext>png</ext>";
$f[]="            <mime_type>image/png</mime_type>";
$f[]="            <height>0</height>";
$f[]="            <width>0</width>";
$f[]="            <!--set \"greater\" to \"true\" if you only want to resize images greater than \"height\" or \"width\"-->";
$f[]="            <greater>false</greater>";
$f[]="            <!--set \"less\" to \"true\" if you only want to resize images less than \"height\" or \"width\"-->";
$f[]="            <less>false</less>";
$f[]="            <!--set \"less\" and \"greater\" to \"false\" if you always want to resize-->";
$f[]="          </convert>";
$f[]="        </file>";
$f[]="        <file ext=\"bmp\">";
$f[]="          <type>IMAGE_ITEM</type>";
$f[]="          <mime_type>image/bmp</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"png\">";
$f[]="          <type>IMAGE_ITEM</type>";
$f[]="          <mime_type>image/png</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"gif\">";
$f[]="          <type>IMAGE_ITEM</type>";
$f[]="          <mime_type>image/gif</mime_type>";
$f[]="        </file>";
$f[]="        <!--video files-->";
$f[]="        <file ext=\"mpg\">";
$f[]="          <ext>mpeg</ext>";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/mpeg</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"mp4\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/mp4</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"avi\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-msvideo</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"wmv\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-ms-wmv</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"vob\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-ms-vob</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"mkv\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-matroska</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"vdr\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-extension-vdr</mime_type>";
$f[]="          <transcode enabled=\"true\">";
$f[]="            <ext>vob</ext>";
$f[]="            <mime_type>video/x-ms-vob</mime_type>";
$f[]="          </transcode>";
$f[]="        </file>";
$f[]="        <file ext=\"flv\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>application/x-flash-video</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"asf\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/x-ms-asf</mime_type>";
$f[]="        </file>";
$f[]="        <!--playlists-->";
$f[]="        <file ext=\"pls\">";
$f[]="          <type>PLAYLIST</type>";
$f[]="          <mime_type>audio/x-scpls</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"m3u\">";
$f[]="          <type>PLAYLIST</type>";
$f[]="          <mime_type>audio/x-mpegurl</mime_type>";
$f[]="        </file>";
$f[]="        <file ext=\"wpl\">";
$f[]="          <type>PLAYLIST</type>";
$f[]="          <mime_type>application/vnd.ms-wpl</mime_type>";
$f[]="        </file>";
$f[]="      </file_settings>";
$f[]="    </device>";
$f[]="    <!--For other device settings take a look at http://fuppes.ulrich-voelkel.de/wiki/index.php/Category:Device-->";
$f[]="    <!--If you have more than one device it is a good idea to set the ip address as some devices may have conflicting \"user agents\".-->";
$f[]="    <!--It is safe to remove unneeded devices-->";
$f[]="    <device name=\"PS3\" enabled=\"true\">";
$f[]="      <user_agent>UPnP/1.0 DLNADOC/1.00</user_agent>";
$f[]="      <user_agent>PLAYSTATION3</user_agent>";
$f[]="      <!--<ip></ip>-->";
$f[]="      <enable_dlna>true</enable_dlna>";
$f[]="      <transcoding_release_delay>50</transcoding_release_delay>";
$f[]="      <file_settings>";
$f[]="        <file ext=\"ogg\">";
$f[]="          <type>AUDIO_ITEM_MUSIC_TRACK</type>";
$f[]="          <transcode enabled=\"true\">";
$f[]="            <http_encoding>stream</http_encoding>";
$f[]="          </transcode>";
$f[]="        </file>";
$f[]="      </file_settings>";

$f[]="      <file ext=\"avi\">";
$f[]="      <type>VIDEO_ITEM</type>";
$f[]="      <mime_type>video/x-divx</mime_type>";
$f[]="      </file>";



$f[]="    </device>";
$f[]="    <device name=\"Xbox 360\" virtual=\"Xbox 360\" enabled=\"false\">";
$f[]="      <user_agent>Xbox/2.0.\d+.\d+ UPnP/1.0 Xbox/2.0.\d+.\d+</user_agent>";
$f[]="      <user_agent>Xenon</user_agent>";
$f[]="      <xbox360>true</xbox360>";
$f[]="      <show_empty_resolution>true</show_empty_resolution>";
$f[]="      <!--This section is for the mime types that the makers of the XBox changed from standards.-->";
$f[]="      <file_settings>";
$f[]="        <file ext=\"avi\">";
$f[]="          <type>VIDEO_ITEM</type>";
$f[]="          <mime_type>video/avi</mime_type>";
$f[]="        </file>";
$f[]="      </file_settings>";
$f[]="      <description_values>";
$f[]="        <friendly_name>%s %v : 1 : Windows Media Connect</friendly_name>";
$f[]="        <model_name>Windows Media Connect compatible (%s)</model_name>";
$f[]="        <model_number>2.0</model_number>";
$f[]="      </description_values>";
$f[]="    </device>";
$f[]="  </device_settings>";
$f[]="</fuppes_config>";	
echo "Starting......: fuppes building configuration done...\n";
if(!is_dir("/etc/fuppes")){@mkdir("/etc/fuppes");}
if(!is_dir("/var/db/fuppes")){@mkdir('/var/db/fuppes',644,true);}
@file_put_contents("/etc/fuppes/fuppes.cfg", @implode("\n", $f));


	
}
