<?php


class cyrus_maillog{
	var $buffer;
	
	
	function cyrus_maillog(){
		
		
	}
	
	public function ParseBuffer($buffer){
		$buffer=$this->buffer;
		
		if(preg_match('#cyrus.+?:DBERROR.+?DB_VERSION_MISMATCH#',$buffer,$re)){
			$this->cyrus_database_error($buffer);
			return true;
		}

	if(preg_match('#cyrus\/imap.+?DBERROR db[0-9]: PANIC: fatal region error detected; run recovery#',$buffer)){
			$this->events("DBERROR detected...");
			$file="/etc/artica-postfix/croned.1/cyrus.db.error";
			$ftime=file_time_min($file);
			if($ftime>10){
				$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
				$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
				$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
				$this->email_events("Cyrus database error !!",$buffer,"mailbox");
				$this->events("DBERROR detected, take action ftime=$ftime");
				@unlink($file);
				file_put_contents($file,"#");
			}else{
				$this->events("DBERROR detected, but take action after 10mn");
			}
			return true;	
		}		
		
		
		if(preg_match('#lmtpunix.+?DBERROR db4: Logging region out of memory; you may need to increase its size#',$buffer,$re)){
				$pb=md5("cyrus:Logging region out of memory");
				$file="/etc/artica-postfix/croned.1/$pb.error";
				$time=file_time_min($file);
				if($time>10){
					$this->events("DBERROR db4: Logging region out of memory; ");
					$this->email_events("Cyrus: DBERROR db4: Logging region out of memory; ","Cyrus-imapd report:\n$buffer\nArtica will enable DB_CONFIG to fix this issue","mailbox");
					@unlink($file);
					file_put_contents($file,"#");
					$sock=new sockets();
					$sock->SET_INFO("EnableCyrusDBConfig",1);
					shell_exec("/usr/share/artica-postfix/bin/artica-install --cyrus-db_config &");
				}
					$this->events("DBERROR db4: Logging region out of memory; (SKIP) $time/10mn");
					return true;
				}

				
			if(preg_match('#DBERROR db4:(.+?): unexpected file type or format#',$buffer,$re)){
				$this->cyrus_db_error($buffer,$re[1]);
				return true;
			}
			
			if(preg_match("#cyrus\/lmtp\[.+?:\s+IOERROR: not a sieve bytecode file\s+(.+?)$#",$buffer,$re)){
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["SIEVEC_PATH"]} {$re[1]} {$re[1]}");
				return true;	
			}			

			if(preg_match('#couldn.+?exec.+?imapd: Too many open files#',$buffer)){
				cyrus_generic_error($buffer,"Too many open files");
				return true;
			}
			if(preg_match("#sieve script\s+(.+?)\s+doesn.+?t exist: No such file or directory#",$buffer,$re)){
				$this->cyrus_sieve_error($re[1]);
				return true;
			}	

			if(preg_match("#cyrus\/lmtpunix\[.+?:\s+verify_user\(.+?\)\s+failed:\s+System I\/O error#",$buffer,$re)){
				$this->cyrus_generic_reconfigure($buffer,"Cyrus I/O error");
				return true;
			}
			
			if(preg_match('#imap.+?IOERROR.+?opening\s+(.+?):.+?Permission denied#',$buffer,$re)){
				if(is_dir($re[1])){
					events("chown ".dirname($re[1]));
					$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/bin/chown -R cyrus:mail '.dirname($re[1]));
				}
				return true;
			}
			if(preg_match('#IOERROR: fstating sieve script (.+?): No such file or directory#',$buffer,$re)){
				$this->events("/bin/touch {$re[1]}");
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/touch {$re[1]}");
				return null;
			}
		
			if(preg_match('#ctl_cyrusdb.+?IOERROR.+?: Permission denied#',$buffer,$re)){
				$file="/etc/artica-postfix/croned.1/cyrus.IOERROR.permissions.error";
				if(file_time_min($file)>10){
				$this->events("IOERROR detected, check perms");
				$this->email_events("Cyrus error permissions on databases","Cyrus imap claim $buffer\nArtica will try to repair it","mailbox");
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkperms');
				@unlink($file);
				}else{
					events("IOERROR detected, but take action after 10mn");
				}	
				@file_put_contents($file,"#");	
				return true;
			}

			if(preg_match('#cyrus\/lmtpunix\[.+? IOERROR: can not open sieve script\s+(.+?):\s+Permission denied#',$buffer,$re)){
				$file="/etc/artica-postfix/croned.1/cyrus.IOERROR.permissions.". md5($re[1]).".error";
				if(file_time_min($file)>10){
					$this->events("IOERROR detected {$re[1]}, check perms");
					$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/chown cyrus:mail {$re[1]}");
					@unlink($file);
				}else{
					$this->events("IOERROR detected, {$re[1]} but take action after 10mn");
					}	
			@file_put_contents($file,"#");	
			return true;
		}	

		if(preg_match("#cyrus\/master.+? unable to create lmtpunix listener socket(.+?)#",$buffer,$re)){
			cyrus_socket_error($buffer,"$re[1]");
			return true;
		}	
		if(preg_match('#DBERROR: skiplist recovery\s+(.+?)\.seen:\s+ADD\s+at.+?exists#',$buffer,$re)){
			$this->cyrus_bad_seen($re[1]);
			return true;
		}	
		if(preg_match("#cyrus\/lmtp.+?verify_user\(user\.(.+?)\)\s+failed: Mailbox does not exist#",$buffer,$re)){
			$this->cyrus_mailbox_not_exists($buffer,$re[1]);
			return true;
		}
		if(preg_match("#cyrus\/lmtp.+?verify_user\((.+?)\!user\.(.+?)\)\s+failed: Mailbox does not exist#",$buffer,$re)){
			$this->cyrus_mailbox_not_exists($buffer,"{$re[2]}@{$re[1]}");
			return true;
		}
		if(preg_match("#cyrus\/imap\[.+?: IOERROR: opening\s+(.+?):\s+No such file or directory#",$buffer,$re)){
			@mkdir(dirname($re[1]),0755,true);
			shell_exec("/bin/touch {$re[1]}");
			events("postfix -> mkdir ".dirname($re[1]));
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("chown -R cyrus:mail ".dirname($re[1]));
			return;
		}
		
	if(preg_match("#cyrus/imaps\[.+?unable to get certificate from#",$buffer,$re)){
		$this->events("cyrus ssl certificate error");
		$file="/etc/artica-postfix/croned.1/cyrus.ssl.certificate.failed";
		if(file_time_min($file)>5){
			$this->email_events("Cyrus: SSL Certificate error","Cyrus-imap claim\n$buffer\nArtica will rebuild the certificate","mailbox");
			@unlink("/etc/ssl/certs/cyrus.pem");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install -cyrus ssl");
			@unlink($file);
			file_put_contents($file,"#");
		}	
		return true;
	
	}

	if(preg_match("#cyrus\/lmtp\[.+?verify_user\(user\.(.+?)\)\s+failed: Mailbox has an invalid format#",$buffer,$re)){
		$re[1]=str_replace("^",".",$re[1]);
		$file="/etc/artica-postfix/croned.1/cyrus.lmptp.{$re[1]}.MailboxHasAnInvalidFormat";
		$this->events("cyrus {$re[1]} Mailbox has an invalid format");
		if(file_time_min($file)>20){
			$this->email_events("Mailbox corrupted for {$re[1]}","Cyrus claim\n$buffer\nCheck this mailbox","mailbox");
			@unlink($file);
			file_put_contents($file,"#");
		}
		return true;	
	}	
	
	
		if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+No such file or directory#",$buffer,$re)){
			@mkdir(dirname($re[1]),null,true);
			@file_put_contents($re[1]," ");
			return true;
		}
		if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+Permission denied#",$buffer,$re)){
			shell_exec("/bin/chown cyrus:mail {$re[1]}");
			return true;
		}
		if(preg_match("#cyrus\/.+?\[.+?IOERROR: fstating sieve script\s+(.+?):\s+Permission denied#",$buffer,$re)){
			shell_exec("/bin/chown cyrus:mail {$re[1]}");
			return true;
		}

		if(preg_match("#cyrus\/imap\[.+?:\s+Deleted mailbox user\.(.+)#",$buffer,$re)){
			$this->email_events("{$re[1]} Mailbox has been deleted",$buffer,"mailbox"); 
			return true;
		}
		if(preg_match("#cyrus.+?reconstruct\[.+?:\s+Updating last_appenddate for user\.(.+?):#",$buffer,$re)){
			email_events("{$re[1]} Mailbox has been reconstructed",$buffer,"mailbox"); 
			return true;
		}

		if(preg_match("#cyrus\/lmtpunix.+?IOERROR:\s+opening.+?\/user\/(.+?)\/cyrus.header:\s+No such file or directory#",$buffer,$re)){
			$file="/etc/artica-postfix/croned.1/postfix.{$re[1]}.mbx.error";
			$this->events("lmtpunix -> mailbox IOERROR error");
			if(file_time_min($file)>5){
				$this->email_events("{$re[1]} Mailbox is deleted but postfix wants to tranfert mails !","Cyrus imap claim\n$buffer\nArtica will re-create the mailbox","mailbox");
				$this->events("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.cyrus-restore.php --create-mbx {$re[1]}"); 
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.cyrus-restore.php --create-mbx {$re[1]}");
				@unlink($file);
				file_put_contents($file,"#");
				return true;
			}
			$this->events("lmtpunix -> mailbox IOERROR error (timeout)");
			return true;
		}

		if(preg_match("#cyrus.+?:\s+IOERROR: reading cache record for user\.(.+?)\.(.+?)\s+.+?mailbox needs a reconstruct#",$buffer,$re)){
			$file="/etc/artica-postfix/croned.1/postfix.{$re[1]}.{$re[2]}.mbx.reconstruct";
			$this->events("{$re[1]}/{$re[2]} -> mailbox IOERROR error (reconstruct)");
			if(file_time_min($file)>5){
				$this->email_events("{$re[1]} Mailbox {$re[2]} seems to be corrupted","Cyrus-imap claim\n$buffer\nArtica will try to reconstruct the mailbox","mailbox");
				$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.cyrus-repair-mailbox.php \"{$re[1]}\"";
				$this->events("$cmd"); 
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
				@unlink($file);
				file_put_contents($file,"#");
				return true;
			}
				$this->events("{$re[1]}/{$re[2]} -> mailbox IOERROR error (timeout)");
			return true;
		}

		if(preg_match("#cyrus.+?unable to get certificate from.+?(.+?)cyrus\.pem#",$buffer,$re)){
			$this->cyrus_vertificate_error();
			return true;
		}		
						
						
		
		
	}
	
	private function cyrus_sieve_error($file){
		$this->events("cyrus_sieve_error():: /bin/touch \"$file\"");
		shell_exec("/bin/touch \"$file\" &");
	}	
	
	private function cyrus_db_error($buffer,$dbfile){
		$dbfile=trim($dbfile);
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){return null;}	
		$stime=date('YmdHis');
		$b_path="$dbfile.bak.$stime";
		@unlink($dbfile);
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
		$this->email_events("Warning cyrus db error on $dbfile","cyrus-imap claim: $buffer file will be backuped to",'mailbox');
		@unlink($file);
		file_put_contents($file,"#");	
		}	
		
	private function cyrus_generic_error($buffer,$subject){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){return null;}	
		$this->events("Cyrus error !! $buffer (cache=$file)");
		$this->email_events("cyrus-imapd error: $subject","$buffer, Artica will restart cyrus",'mailbox');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
		@unlink($file);
		file_put_contents($file,"#");
		
	}	

	private function cyrus_generic_reconfigure($buffer,$subject){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){return null;}	
		$this->events("Cyrus error !! $buffer (cache=$file)");
		$this->email_events("cyrus-imapd error: $subject","$buffer, Artica will reconfigure cyrus",'mailbox');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
		@unlink($file);
		file_put_contents($file,"#");	
	}	
	
	private function cyrus_socket_error($buffer,$socket){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){return null;}	
		$this->email_events("cyrus-imapd socket error: $socket","Postfix claim \"$buffer\", Artica will restart cyrus",'mailbox');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
		@unlink($file);
		@file_put_contents($file,"#");
	}	
	
	private function cyrus_bad_seen($fileseen){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		$fileseen=$fileseen.".seen";
		if(file_time_min($file)<15){return null;}
		$this->email_events('Warning Corrupted mailbox detected','Cyrus claim that '.$fileseen.'is corrupted, Artica will delete this file to repair it','mailbox');
	    $GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --repair-seen-file $fileseen");
		@unlink($file);
		file_put_contents($file,"#");
	 }	
	 
	private function cyrus_mailbox_not_exists($line,$user){
		$user=str_replace('^','.',$user);
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__.'.'.md5($user);
		if(file_time_min($file)<15){return null;}
		$this->email_events("Warning Mailbox does not exist $user","Mailbox server claim: $user mailbox is not available you should create an alias or mailbox $line","mailbox");
		@unlink($file);
		@file_put_contents($file,"#");	
	}

	private function cyrus_vertificate_error($buffer){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){
			$this->events("Cyrus certificate error, time-out");
			return null;
		}	
		@unlink($file);
		$cmd="/usr/share/artica-postfix/bin/artica-install -cyrus ssl";
		$this->events(__FUNCTION__. " <$cmd>");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);	
		$this->email_events("Cyrus certificate error","Service cyrus claim \"$buffer\" Artica will rebuild certificate for cyrus-imapd",'mailbox');
		@file_put_contents($file,"#");	
		return;		
	}

	private function cyrus_database_error($buffer){
		$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
		if(file_time_min($file)<15){return null;}
		$this->email_events("cyrus-imapd FATAL error !! database engine is incompatible reinstall mailbox system !","Cyrus claim: $buffer",'mailbox');
		$this->interface_events("APP_CYRUS_IMAP",$buffer);
		@unlink($file);
		@file_put_contents($file,"#");
	}	

private function interface_events($product,$line){
	$ini=new Bs_IniHandler();
	if(is_file("/usr/share/artica-postfix/ressources/logs/interface.events")){
		$ini->loadFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	}
	$ini->set($product,'error',$line);
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	@chmod("/usr/share/artica-postfix/ressources/logs/interface.events",0755);
	
}
	
	
	private function email_events($subject,$text,$context){
		$GLOBALS["CLASS_UNIX"]->send_email_events($subject,$text,$context);
	}

	private function events($text){
		
		$logFile="/var/log/artica-postfix/postfix-logger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);		
	}

	
	
	
}