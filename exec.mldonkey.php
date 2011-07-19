<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.donkey.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--server=(.+?)\s+#",@implode(" ",$argv))){$_GLOBAL["MLDONKEY_SERVER"]=$re[1];}

if($argv[1]=="--build"){buildconf();die();}
if($argv[1]=="--settings"){ChangeSettings($argv[2]);die();}



function buildconf(){

	
$f[]="";	
$f[]="client_name = {$_GLOBAL["MLDONKEY_SERVER"]}";
$f[]="allowed_ips = [\"127.0.0.1\";]";
$f[]="gui_port = 0";
$f[]="gift_port = 0";
$f[]="http_port = 0";
$f[]="telnet_port = 4000";
$f[]="alias_commands = [(quit, q);(exit, q);]";
$f[]="max_hard_upload_rate = 10";
$f[]="max_hard_download_rate = 50";
$f[]="max_hard_upload_rate_2 = 5";
$f[]="max_hard_download_rate_2 = 20";
$f[]="max_opened_connections = 200";
$f[]="max_opened_connections_2 = 100";
$f[]="max_indirect_connections = 30";
$f[]="max_upload_slots = 5";
$f[]="max_release_slots = 20";
$f[]="friends_upload_slot = true";
$f[]="small_files_slot_limit = 10240";
$f[]="dynamic_slots = false";
$f[]="max_connections_per_second = 5";
$f[]="nolimit_ips = [\"127.0.0.1\";]";
$f[]="copy_read_buffer = true";
$f[]="enable_overnet = true";
$f[]="enable_kademlia = false";
$f[]="enable_servers = true";
$f[]="enable_bittorrent = true";
$f[]="enable_donkey = true";
$f[]="enable_opennap = false";
$f[]="enable_soulseek = false";
$f[]="enable_gnutella = false";
$f[]="enable_gnutella2 = false";
$f[]="enable_fasttrack = false";
$f[]="enable_directconnect = false";
$f[]="enable_fileTP = true";
$f[]="client_ip = \"\"";
$f[]="force_client_ip = false";
$f[]="discover_ip = true";
$f[]="user_agent = default";
$f[]="web_infos = [";
$f[]="  (\"contact.dat\", 168, \"http://download.overnet.org/contact.dat\");";
$f[]="  (\"geoip.dat\", 0, \"http://www.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz\");";
$f[]="  (\"server.met\", 0, \"http://www.gruk.org/server.met.gz\");";
$f[]="  (\"guarding.p2p\", 96, \"http://www.bluetack.co.uk/config/level1.gz\");";
$f[]="  (hublist, 0, \"http://dchublist.com/hublist.config.bz2\");";
$f[]="  (\"nodes.gzip\", 0, \"http://update.kceasy.com/update/fasttrack/nodes.gzip\");]";
$f[]="";
$f[]="referers = [(\".*suprnova.*\", \"http://www.suprnova.org/\");]";
$f[]="cookies = []";
$f[]="http_proxy_server = \"\"";
$f[]="http_proxy_port = 8080";
$f[]="http_proxy_tcp = false";
$f[]="html_mods_bw_refresh_delay = 11";
$f[]="html_mods_theme = \"\"";
$f[]="auto_commit = true";
$f[]="pause_new_downloads = false";
$f[]="release_new_downloads = true";
$f[]="max_concurrent_downloads = 50";
$f[]="max_recover_zeroes_gap = 16";
$f[]="";
$f[]="	(* A command that is called when a file is committed, does not work on MinGW.";
$f[]="	  Arguments are (kept for compatability):";
$f[]="	    \$1 - temp file name, without path";
$f[]="	    \$2 - file size";
$f[]="	    \$3 - filename of the committed file";
$f[]="	  Also these environment variables can be used (prefered way):";
$f[]="	    \$TEMPNAME  - temp file name, including path";
$f[]="	    \$FILEID    - same as \$1";
$f[]="	    \$FILESIZE  - same as \$2";
$f[]="	    \$FILENAME  - same as \$3";
$f[]="	    \$FILEHASH  - internal hash";
$f[]="	    \$DURATION  - download duration";
$f[]="	    \$INCOMING  - directory used for commit";
$f[]="	    \$NETWORK   - network used for downloading";
$f[]="	    \$ED2K_HASH - ed2k hash if MD4 is known";
$f[]="	    \$FILE_OWNER - user who started the download";
$f[]="	    \$FILE_GROUP - group the file belongs to";
$f[]="	    \$USER_MAIL - mail address of file_owner";
$f[]="	   *)";
$f[]="file_completed_cmd = \"\"";
$f[]="";
$f[]="	(* The command which is called when a download is started. Arguments";
$f[]="	  are '-file <num>'";
$f[]="	  Also these environment variables can be used (prefered way):";
$f[]="	    \$TEMPNAME  - temp file name, including path";
$f[]="	    \$FILEID    - same as \$1";
$f[]="	    \$FILESIZE  - same as \$2";
$f[]="	    \$FILENAME  - same as \$3";
$f[]="	    \$FILEHASH  - internal hash";
$f[]="	    \$NETWORK   - network used for downloading";
$f[]="	    \$ED2K_HASH - ed2k hash if MD4 is known";
$f[]="	    \$FILE_OWNER - user who started the download";
$f[]="	    \$FILE_GROUP - group the file belongs to";
$f[]="	    \$USER_MAIL - mail address of file_owner";
$f[]="	   *)";
$f[]="file_started_cmd = \"\"";
$f[]="run_as_user = \"\"";
$f[]="run_as_useruid = 0";
$f[]="run_as_group = \"\"";
$f[]="run_as_groupgid = 0";
$f[]="ask_for_gui = false";
$f[]="start_gui = false";
$f[]="recover_temp_on_startup = true";
$f[]="smtp_server = \"127.0.0.1\"";
$f[]="smtp_port = 25";
$f[]="mail = \"\"";
$f[]="add_mail_brackets = true";
$f[]="filename_in_subject = true";
$f[]="url_in_mail = \"\"";
$f[]="temp_directory = temp";
$f[]="share_scan_interval = 30";
$f[]="create_file_mode = 664";
$f[]="create_dir_mode = 755";
$f[]="create_file_sparse = true";
$f[]="hdd_temp_minfree = 50";
$f[]="hdd_temp_stop_core = false";
$f[]="hdd_coredir_minfree = 50";
$f[]="hdd_coredir_stop_core = true";
$f[]="hdd_send_warning_interval = 1";
$f[]="shared_directories = [";
$f[]="  {  dirname = shared";
$f[]="     strategy = all_files";
$f[]="     priority = 0";
$f[]="};";
$f[]="  {  dirname = \"/home/mldonkey/incoming/files\"";
$f[]="     strategy = incoming_files";
$f[]="     priority = 0";
$f[]="};";
$f[]="  {  dirname = \"/home/mldonkey/incoming/directories\"";
$f[]="     strategy = incoming_directories";
$f[]="     priority = 0";
$f[]="};]";
$f[]="";
$f[]="allowed_commands = [(df, df);(ls, \"ls incoming\");]";
$f[]="allow_any_command = false";
$f[]="allow_browse_share = 1";
$f[]="messages_filter = \"DI-Emule|ZamBoR|Ketamine|eMule FX|AUTOMATED MESSAGE|Hi Honey!|Do you live in my area|download HyperMule\"";
$f[]="comments_filter = \"http://|https://|www\\.\"";
$f[]="save_results = 0";
$f[]="buffer_writes = false";
$f[]="emule_mods_count = false";
$f[]="emule_mods_showall = false";
$f[]="backup_options_delay = 0";
$f[]="backup_options_generations = 10";
$f[]="backup_options_format = \"tar.gz\"";
$f[]="shutdown_timeout = 3";
$f[]="client_bind_addr = \"0.0.0.0\"";
$f[]="http_bind_addr = \"0.0.0.0\"";
$f[]="gui_bind_addr = \"0.0.0.0\"";
$f[]="telnet_bind_addr = \"127.0.0.1\"";
$f[]="print_all_sources = true";
$f[]="improved_telnet = true";
$f[]="verbosity = \"\"";
$f[]="loop_delay = 5";
$f[]="enable_openft = false";
$f[]="rss_feeds = []";
$f[]="rss_preprocessor = xmllint";
$f[]="ip_blocking_descriptions = false";
$f[]="ip_blocking = \"\"";
$f[]="ip_blocking_countries = []";
$f[]="ip_blocking_countries_block = false";
$f[]="geoip_dat = \"\"";
$f[]="tcpip_packet_size = 40";
$f[]="mtu_packet_size = 1500";
$f[]="minimal_packet_size = 600";
$f[]="socket_keepalive = false";
$f[]="html_mods = true";
$f[]="html_mods_style = 0";
$f[]="html_mods_human_readable = true";
$f[]="html_mods_use_relative_availability = true";
$f[]="html_mods_vd_network = true";
$f[]="html_mods_vd_comments = true";
$f[]="html_mods_vd_user = false";
$f[]="html_mods_vd_group = false";
$f[]="html_mods_vd_active_sources = true";
$f[]="html_mods_vd_age = true";
$f[]="html_flags = true";
$f[]="html_mods_vd_gfx = true";
$f[]="html_mods_vd_gfx_remove = false";
$f[]="html_mods_vd_gfx_fill = true";
$f[]="html_mods_vd_gfx_split = false";
$f[]="html_mods_vd_gfx_stack = true";
$f[]="html_mods_vd_gfx_flip = true";
$f[]="html_mods_vd_gfx_mean = true";
$f[]="html_mods_vd_gfx_transparent = true";
$f[]="html_mods_vd_gfx_png = true";
$f[]="html_mods_vd_gfx_h = true";
$f[]="html_mods_vd_gfx_x_size = 795";
$f[]="html_mods_vd_gfx_y_size = 200";
$f[]="html_mods_vd_gfx_h_intervall = 60";
$f[]="html_mods_vd_gfx_h_dymamic = true";
$f[]="html_mods_vd_gfx_h_grid_time = 0";
$f[]="html_mods_vd_gfx_subgrid = 0";
$f[]="html_mods_vd_gfx_tag = false";
$f[]="html_mods_vd_gfx_tag_use_source = false";
$f[]="html_mods_vd_gfx_tag_source = image";
$f[]="html_mods_vd_gfx_tag_png = true";
$f[]="html_mods_vd_gfx_tag_enable_title = true";
$f[]="html_mods_vd_gfx_tag_title = \"MLNet traffic\"";
$f[]="html_mods_vd_gfx_tag_title_x_pos = 4";
$f[]="html_mods_vd_gfx_tag_title_y_pos = 1";
$f[]="html_mods_vd_gfx_tag_dl_x_pos = 4";
$f[]="html_mods_vd_gfx_tag_dl_y_pos = 17";
$f[]="html_mods_vd_gfx_tag_ul_x_pos = 4";
$f[]="html_mods_vd_gfx_tag_ul_y_pos = 33";
$f[]="html_mods_vd_gfx_tag_x_size = 80";
$f[]="html_mods_vd_gfx_tag_y_size = 50";
$f[]="html_mods_vd_last = true";
$f[]="html_mods_vd_prio = true";
$f[]="html_vd_barheight = 2";
$f[]="html_vd_chunk_graph = true";
$f[]="html_vd_chunk_graph_style = 0";
$f[]="html_vd_chunk_graph_max_width = 200";
$f[]="html_mods_show_pending = true";
$f[]="html_mods_load_message_file = false";
$f[]="html_mods_max_messages = 50";
$f[]="html_checkbox_vd_file_list = true";
$f[]="html_checkbox_search_file_list = false";
$f[]="html_use_gzip = false";
$f[]="html_mods_use_js_tooltips = true";
$f[]="html_mods_js_tooltips_wait = 0";
$f[]="html_mods_js_tooltips_timeout = 100000";
$f[]="html_mods_use_js_helptext = true";
$f[]="allow_local_network = false";
$f[]="log_size = 300";
$f[]="log_file_size = 2";
$f[]="log_file = \"mlnet.log\"";
$f[]="log_to_syslog = true";
$f[]="gui_log_size = 30";
$f[]="sources_per_chunk = 3";
$f[]="config_files_security_space = 10";
$f[]="previewer = mldonkey_previewer";
$f[]="mldonkey_bin = \"/usr/bin\"";
$f[]="mldonkey_gui = \"/usr/bin/mlgui\"";
$f[]="buffer_writes_delay = 30.";
$f[]="buffer_writes_threshold = 1024";
$f[]="utf8_filename_conversions = []";
$f[]="interface_buffer = 1000000";
$f[]="max_name_len = 50";
$f[]="max_result_name_len = 50";
$f[]="max_filenames = 50";
$f[]="max_client_name_len = 25";
$f[]="term_ansi = true";
$f[]="update_gui_delay = 1.";
$f[]="http_realm = MLdonkey";
$f[]="html_frame_border = true";
$f[]="commands_frame_height = 46";
$f[]="motd_html = \"\"";
$f[]="compaction_delay = 2";
$f[]="vd_reload_delay = 120";
$f[]="create_mlsubmit = true";
$f[]="minor_heap_size = 32";
$f[]="relevant_queues = [";
$f[]="  0;";
$f[]="  1;";
$f[]="  2;";
$f[]="  3;";
$f[]="  4;";
$f[]="  5;";
$f[]="  6;";
$f[]="  8;";
$f[]="  9;";
$f[]="  10;]";
$f[]="";
$f[]="min_reask_delay = 600";
$f[]="display_downloaded_results = true";
$f[]="filter_table_threshold = 50";
$f[]="client_buffer_size = 500000";
$f[]="save_options_delay = 900.";
$f[]="server_connection_timeout = 30.";
$f[]="download_sample_rate = 1.";
$f[]="download_sample_size = 100";
$f[]="calendar = []";
$f[]="compaction_overhead = 25";
$f[]="space_overhead = 80";
$f[]="max_displayed_results = 1000";
$f[]="options_version = 21";
$f[]="max_comments_per_file = 100";
$f[]="max_comment_length = 256";
$f[]="";	
echo "Starting......: MLDonkey removing backup configuration files\n";
shell_exec('/bin/rm -rf /root/.mldonkey/old_config/*');
@file_put_contents("/root/.mldonkey/downloads.ini",@implode("\n",$f));
@file_put_contents("/root/.mldonkey/old_config/downloads.ini",@implode("\n",$f));
echo "Starting......: MLDonkey success downloads.ini configuration file\n";
//auth admin “”
}


function ChangeSettings($ID){
	$sql="SELECT * FROM mldonkey WHERE ID='$ID'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$array=unserialize(base64_decode($ligne["parameters"]));
	
	$ml=new EmuleTelnet();
	if($array["max_hard_upload_rate"]==null){$array["max_hard_upload_rate"]=0;}
	if($array["max_hard_download_rate"]==null){$array["max_hard_download_rate"]=0;}
	$max_hard_upload_rate=$ml->parameters_save("max_hard_upload_rate",$array["max_hard_upload_rate"]);
	$max_hard_download_rate=$ml->parameters_save("max_hard_download_rate",$array["max_hard_download_rate"]);
	$files_queries_per_minute=$ml->parameters_save("files_queries_per_minute",1);
	$server_connection_timeout=$ml->parameters_save("server_connection_timeout",25);
	$propagate_sources=$ml->parameters_save("propagate_sources","true");
	
	$ml->SaveConfig();
	$text="
	Parameters was modified to:
	---------------------------------
	Upload rate: {$array["max_hard_upload_rate"]}kb/s ($max_hard_upload_rate)
	Download rate: {$array["max_hard_download_rate"]}kb/s ($max_hard_download_rate)
	files_queries_per_minute:1 mn ($files_queries_per_minute)
	server_connection_timeout:25s ($server_connection_timeout)
	propagate_sources:True ($propagate_sources)
	";
	
	writelogs("MLDonkey Upload:{$array["max_hard_upload_rate"]}kb/s Down:{$array["max_hard_download_rate"]}kb/s",__FUNCTION__,__FILE__,__LINE__);
	
	send_email_events("[Task $ID]: MLDonkey Upload:{$array["max_hard_upload_rate"]}kb/s Down:{$array["max_hard_download_rate"]}kb/s",$text,"system");
}













?>