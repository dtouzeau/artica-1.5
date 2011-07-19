CREATE TABLE users (
  id         int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, 
  priority   integer      NOT NULL DEFAULT '7',
  policy_id  integer unsigned NOT NULL DEFAULT '1',
  email      varchar(255) NOT NULL UNIQUE,
  fullname   varchar(255) DEFAULT NULL,  
  local      char(1)
);

CREATE TABLE mailaddr (
  id         int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  priority   integer      NOT NULL DEFAULT '7', 
  email      varchar(255) NOT NULL UNIQUE
);

CREATE TABLE wblist (
  rid        integer unsigned NOT NULL,  
  sid        integer unsigned NOT NULL,  
  wb         varchar(10)  NOT NULL,  
  PRIMARY KEY (rid,sid)
);

CREATE TABLE policy (
  id  int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                   
  policy_name      varchar(32),    

  virus_lover          char(1) default NULL,     -- Y/N
  spam_lover           char(1) default NULL,     -- Y/N
  banned_files_lover   char(1) default NULL,     -- Y/N
  bad_header_lover     char(1) default NULL,     -- Y/N

  bypass_virus_checks  char(1) default NULL,     -- Y/N
  bypass_spam_checks   char(1) default NULL,     -- Y/N
  bypass_banned_checks char(1) default NULL,     -- Y/N
  bypass_header_checks char(1) default NULL,     -- Y/N

  spam_modifies_subj   char(1) default NULL,     -- Y/N

  virus_quarantine_to      varchar(64) default NULL,
  spam_quarantine_to       varchar(64) default NULL,
  banned_quarantine_to     varchar(64) default NULL,
  bad_header_quarantine_to varchar(64) default NULL,
  clean_quarantine_to      varchar(64) default NULL,
  other_quarantine_to      varchar(64) default NULL,
  spam_tag_level  float default NULL, 
  spam_tag2_level float default NULL, 
  spam_kill_level float default NULL, 
  spam_dsn_cutoff_level        float default NULL,
  spam_quarantine_cutoff_level float default NULL,
  addr_extension_virus      varchar(64) default NULL,
  addr_extension_spam       varchar(64) default NULL,
  addr_extension_banned     varchar(64) default NULL,
  addr_extension_bad_header varchar(64) default NULL,
  warnvirusrecip      char(1)     default NULL, -- Y/N
  warnbannedrecip     char(1)     default NULL, -- Y/N
  warnbadhrecip       char(1)     default NULL, -- Y/N
  newvirus_admin      varchar(64) default NULL,
  virus_admin         varchar(64) default NULL,
  banned_admin        varchar(64) default NULL,
  bad_header_admin    varchar(64) default NULL,
  spam_admin          varchar(64) default NULL,
  spam_subject_tag    varchar(64) default NULL,
  spam_subject_tag2   varchar(64) default NULL,
  message_size_limit  integer     default NULL, 
  banned_rulenames    varchar(64) default NULL  
);

CREATE TABLE maddr (
  id         int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email      varchar(255) NOT NULL UNIQUE, 
  domain     varchar(255) NOT NULL     
) ENGINE=InnoDB;

CREATE TABLE msgs (
  mail_id    varchar(12)   NOT NULL PRIMARY KEY,  
  secret_id  varchar(12)   DEFAULT '',  
  am_id      varchar(20)   NOT NULL,    
  time_num   integer unsigned NOT NULL, 
  time_iso   char(16)      NOT NULL,    
  sid        integer unsigned NOT NULL, 
  policy     varchar(255)  DEFAULT '',  
  client_addr varchar(255) DEFAULT '',  
  size       integer unsigned NOT NULL, 
  content    char(1),                   
  quar_type  char(1),                   
  quar_loc   varchar(255)  DEFAULT '',  
  dsn_sent   char(1),                   
  spam_level float,                     
  message_id varchar(255)  DEFAULT '',  
  from_addr  varchar(255)  DEFAULT '',  
  subject    varchar(255)  DEFAULT '',  
  host       varchar(255)  NOT NULL,    
  FOREIGN KEY (sid) REFERENCES maddr(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX msgs_idx_sid      ON msgs (sid);
CREATE INDEX msgs_idx_mess_id  ON msgs (message_id); 
CREATE INDEX msgs_idx_time_num ON msgs (time_num);


CREATE TABLE msgrcpt (
  mail_id    varchar(12)   NOT NULL,
  rid        integer unsigned NOT NULL,
  ds         char(1)       NOT NULL, 
  rs         char(1)       NOT NULL,    
  bl         char(1)       DEFAULT ' ',  
  wl         char(1)       DEFAULT ' ', 
  bspam_level float,                    
  smtp_resp  varchar(255)  DEFAULT '',   
  FOREIGN KEY (rid)     REFERENCES maddr(id)     ON DELETE RESTRICT,
  FOREIGN KEY (mail_id) REFERENCES msgs(mail_id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX msgrcpt_idx_mail_id  ON msgrcpt (mail_id);
CREATE INDEX msgrcpt_idx_rid      ON msgrcpt (rid);


CREATE TABLE quarantine (
  mail_id    varchar(12)   NOT NULL,    
  chunk_ind  integer unsigned NOT NULL, 
  mail_text  blob NOT NULL,             
  PRIMARY KEY (mail_id,chunk_ind),
  FOREIGN KEY (mail_id) REFERENCES msgs(mail_id) ON DELETE CASCADE
) ENGINE=InnoDB;


OPTIMIZE TABLE msgs, msgrcpt, quarantine, maddr;
