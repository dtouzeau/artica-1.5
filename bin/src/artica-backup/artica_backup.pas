program artica_backup;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,logs,rdiffbackup,unix,zsystem, backup,mysql_daemon, incremental_backup,cyrus,backup_rsync,tcpip,
  CyrBackup;

 var
 
 back:Tbackup;
 dar:Trdiffbackup;
 mysql:tmysql_daemon;
 zlogs:tlogs;
 increment:tincrement;
 s:string;
 i:integer;
 SYS:tsystem;
 CCYRUS:Tcyrus;
 rsyncb:tbackup_rsync;
 cyrb:Tcyrback;
 ztcp:ttcpip;
begin
    zlogs:=Tlogs.Create;
    rsyncb:=tbackup_rsync.Create;
    SYS:=Tsystem.Create();
    s:='';


if ParamStr(1)='ping' then begin
   ztcp:=ttcpip.Create;
   writeln('host:',ParamStr(2),'=',ztcp.isPinged(ParamStr(2)));
   halt(0);
end;


 if ParamCount>0 then begin
     for i:=1 to ParamCount do begin
        s:=s  + ' ' +ParamStr(i);

     end;
     s:=trim(s);
 end;
  zlogs.Debuglogs('"'+s+'" command line');


if ParamStr(1)='--list-mysql-databases' then begin
   SYS:=Tsystem.Create;
   back:=tbackup.Create;
   back.GetMysqlDatabases();
   halt(0);
end;


if ParamStr(1)='--single-backup' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP IS STARTING');
      cyrb.rsync_local_start(ParamStr(2));
      halT(0);
end;

if ParamStr(1)='--single-cyrus' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP IS STARTING');
      cyrb.rsync_local_start(ParamStr(2));
      halT(0);
end;


if ParamStr(1)='--rsync-cyrus' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP IS STARTING');
      cyrb.rsync_cyrus(ParamStr(2));
      halT(0);
end;
if ParamStr(1)='--rsync-ldap' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP LDAP IS STARTING');
      cyrb.rsync_OPENLDAP(ParamStr(2));
      halT(0);
end;
if ParamStr(1)='--rsync-mysql' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP LDAP IS STARTING');
      cyrb.rsync_MYSQL_DATABASES(ParamStr(2));
      halT(0);
end;
if ParamStr(1)='--rsync-artica' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP LDAP IS STARTING');
      cyrb.rsync_Artica_settings(ParamStr(2));
      halT(0);
end;
if ParamStr(1)='--rsync-folder' then begin
      cyrb:=Tcyrback.Create;
      zlogs.Syslogs('PERFORM BACKUP FOLDER IS STARTING');
      cyrb.rsync_folder(ParamStr(2),ParamStr(3));
      halT(0);
end;

if ParamStr(1)='--rebuild-ldap' then begin
   SYS:=Tsystem.Create;
   back:=tbackup.Create;
   back.REBUILD_LDAP_DATABASES();
   halt(0);
end;

if ParamStr(1)='--repair-database' then begin
   SYS:=Tsystem.Create;
   mysql:=tmysql_daemon.Create(SYS);
   mysql.REPAIR_DATABASES(); 
   halt(0);
end;

if ParamStr(1)='--repair-artica-branch' then begin
   SYS:=Tsystem.Create;
   back:=tbackup.Create;
   back.REBUILD_ARTICA_BRANCH();
   halt(0);
end;

if ParamStr(1)='--instant-ldap-recover' then begin
   SYS:=Tsystem.Create;
   back:=tbackup.Create;
   back.INSTANT_RECOVER_LDAP_DATABASES(ParamStr(2));
   halt(0);
end;



if ParamStr(1)='--repair-seen-file' then begin
   SYS:=Tsystem.Create;
   CCYRUS:=TCyrus.Create(SYS);
   CCYRUS.REPAIR_CYRUS_SEEN_FILE(ParamStr(2));
   halt(0);
end;


if Paramstr(1)='--export-config' then begin
      back:=tbackup.Create;
      zlogs.Syslogs('PERFORM ARTICA EXPORT IS STARTING');
      back.perform_backup();
      halt(0);
end;

if ParamStr(1)='--import-config' then begin
      back:=tbackup.Create;
      back.perform_restore();
      halt(0);
end;


if ParamStr(1)='--backup' then begin
      writeln('This feature is no longer supported....');
      halt(0);
      back:=tbackup.Create;
      zlogs.Syslogs('PERFORM ARTICA BACKUP IS STARTING');
      back.perform_backup();
      zlogs.Syslogs('PERFORM ARTICA BACKUP IS FINISH');
      fpsystem(back.artica_path + '/bin/artica-backup-share');
      back.retranslate_backup();
      halt(0);
end;

if ParamStr(1)='--sql' then begin
      zlogs.LIST_MYSQL_DATABASES();
      halt(0);
end;

if ParamStr(1)='--restore' then begin
      back:=tbackup.Create;
      back.perform_restore();
      halt(0);
end;

if ParamStr(1)='--restore-cyrus-single-backup' then begin
      back:=tbackup.Create;
      back.restore_cyrus_imap_databases(ParamStr(2));
      halt(0);
end;
if ParamStr(1)='--restore-cyrus-mysql' then begin
      back:=tbackup.Create;
      back.restore_from_mysqlhotcopy(ParamStr(2));
      halt(0);
end;







if ParamStr(1)='--retranslate' then begin
      back:=tbackup.Create;
      back.retranslate_backup();
      halt(0);
end;

if ParamStr(1)='--incremental' then begin

      if SYS.PROCESS_EXIST(SYS.PIDOF_PATTERN(ExtractFileName(ParamStr(0))+'\s+'+ParamStr(1))) then begin
         writeln('Already instance executed');
      end;
      increment:=tincrement.Create;
      increment.StartBackup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;

if ParamStr(1)='--incremental-cyrus' then begin
      increment:=tincrement.Create;
      increment.artica_cyrus_backup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;

if ParamStr(1)='--incremental-mails' then begin
      increment:=tincrement.Create;
      increment.artica_backupMails_backup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;

if ParamStr(1)='--incremental-samba' then begin
      increment:=tincrement.Create;
      increment.artica_backup_samba_backup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;

if ParamStr(1)='--incremental-homes' then begin
      increment:=tincrement.Create;
      increment.artica_backup_homes_backup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;

if ParamStr(1)='--incremental-ldap' then begin
      increment:=tincrement.Create;
      increment.artica_ldap_backup();
      increment.BuildSingleCollection('');
      increment.free;
      halt(0);
end;


if ParamStr(1)='--incremental-computer' then begin
      increment:=tincrement.Create;
      //ParamStr(2) = computer
      //ParamStr(3)= username
      //ParamStr(4)= password
      //ParamStr(5)= shared folder
      //ParamStr(6)= remote folder
      if SYS.PROCESS_EXIST(SYS.PIDOF_PATTERN(ExtractFileName(ParamStr(0))+'\s+'+ParamStr(1)+'\s+'+ParamStr(2))) then begin
         exit;
      end;

      increment.artica_RemoteComputer_backup(ParamStr(2),ParamStr(3),ParamStr(4),ParamStr(5),ParamStr(6));
      increment.free;
      halt(0);
end;


if ParamStr(1)='--incremental-user' then begin
      SYS:=Tsystem.Create();
      if SYS.PROCESS_EXIST(SYS.PIDOF_PATTERN(ExtractFileName(ParamStr(0))+'\s+'+ParamStr(1))) then begin
         writeln('Already instance executed');
      end;
      increment:=tincrement.Create;
      increment.artica_backup_perso_backup();
      increment.free;
      halt(0);
end;

if ParamStr(1)='--mount-dar' then begin
      increment:=tincrement.Create;
      halt(0);
end;

if ParamStr(1)='--dar-query' then begin
      increment:=tincrement.Create;
      increment.query_file(ParamStr(2));
      increment.free;
      halt(0);

end;

if ParamStr(1)='--list-collection' then begin
      increment:=tincrement.Create;
      halt(0);
end;
if ParamStr(1)='--darxml' then begin
      increment:=tincrement.Create;
      increment.xml(ParamStr(2));
      increment.free;
      halt(0);

end;
if ParamStr(1)='--dar-cache' then begin
      increment:=tincrement.Create;
      increment.RefreshCache();
      increment.free;
      halt(0);
end;
if ParamStr(1)='--restore-full' then begin
      increment:=tincrement.Create;
      increment.RestoreDatabase(ParamStr(3),ParamStr(2),ParamStr(4));
      increment.free;
      halt(0);

end;
if ParamStr(1)='--restore-file' then begin

//--restore-file MAIRIE-AIX/reponse-technique.doc userdef_19e9463bfa25d30f9fdfd53917813039 7a6a9344a383c38cbce4686e38f07afd reponse-technique.doc
      increment:=tincrement.Create;
      increment.RestoreDatabaseSingleFile(ParamStr(2),ParamStr(3),ParamStr(4),ParamStr(5));
      increment.free;
      halt(0);

end;



if ParamStr(1)='--build-collections' then begin
   increment:=tincrement.Create;
   increment.Build_collections(ParamStr(2));
   increment.free;
   halt(0);
end;

if ParamStr(1)='--dar-restore-path' then begin
      increment:=tincrement.Create;
      increment.dar_restore_path(ParamStr(2));
      halt(0);
end;

if ParamStr(1)='--dar-populate' then begin
      increment:=tincrement.Create;
      increment.dar_populate(ParamStr(2));
      halt(0);
end;






if ParamStr(1)='--dar-backup-single-path' then begin
      increment:=tincrement.Create;
      increment.artica_backup_single_path(ParamStr(2));
      rsyncb.SSL_STOP();
      increment.free;
      halt(0);
end;
if ParamStr(1)='--dar-size' then begin
      increment:=tincrement.Create;
      increment.GetCollectionsSize();
      increment.free;
      halt(0);
end;


if ParamStr(1)='--dar-find' then begin
 increment:=tincrement.Create;
      increment.DAR_FIND_FILE(ParamStr(2),ParamStr(3));
      increment.free;
      halt(0);
end;





//


if ParamStr(1)='--full-backup' then begin
      back:=tbackup.Create;
      back.perform_sauvegarde(ParamStr(2));
      halt(0);
end;
if ParamStr(1)='--full-restore' then begin
      back:=tbackup.Create;
      back.perform_sauvegarde_restore(ParamStr(2));
      halt(0);
end;



if ParamStr(1)='--dar-status' then begin
      dar:=Trdiffbackup.Create;
      writeln(dar.DAR_STATUS());
      halt(0);
end;

if ParamStr(1)='--dar-restore-file' then begin
      back:=tbackup.Create;
      back.dar_restore_single(ParamStr(2),ParamStr(3),ParamStr(4));
      halt(0);
end;




if ParamStr(1)='--dar-restore-full' then begin
      back:=tbackup.Create;
      back.dar_restore_database(ParamStr(2),ParamStr(3),ParamStr(4));
      halt(0);
end;



  zlogs.Debuglogs('"'+s+'" command line is not understood, launching help');

writeln('artica-backup is in charge of save your datas and restore them in case');
writeln('of crashed server, datas deletion and so on...');
writeln('');
writeln('Usage:');

writeln('');
writeln('');
writeln('incrementals commands -------------------------------------------------------');
writeln('Use --verbose and/or "debug" additionnal token to output debug informations');
writeln(chr(9)+'--incremental.........: Perform a full backup in incremental mode');
writeln(chr(9)+'--incremental-cyrus...: Perform a mailbox backup only in incremental mode');
writeln(chr(9)+'--incremental-mails...: Perform a Artica mails backup features backup only in incremental mode');
writeln(chr(9)+'--incremental-samba...: Perform a Samba shares folder backup in incremental mode');
writeln(chr(9)+'--incremental-homes...: Perform a homes folders backup in incremental mode');
writeln(chr(9)+'--incremental-user....: Perform a user defined folders backup in incremental mode');
writeln(chr(9)+'--incremental-ldap....: Perform a full backup of LDAP server');
writeln(chr(9)+'--list-collection.....: Export the collection list');
writeln(chr(9)+'--darxml..............: export XML tree of a collection name in temporary artica web folder path');
writeln(chr(9)+'--restore-full path db: Restore a full collection (db) in folder (path)');
writeln(chr(9)+'--build-collections...: Build dar_manager collections specified from a path');
writeln(chr(9)+'ping host.............: Detect if a host is available or not');


writeln('');
writeln('');
writeln('Save datas -------------------------------------------------------');
writeln(chr(9)+'--backup.............: Perform a full backup in datas mode');
writeln(chr(9)+'                       This operation export datas from databases server and');
writeln(chr(9)+'                       make incremental backup');
writeln('');
writeln(chr(9)+'--retranslate........: Perform backup disks retranslations (--verbose supported) ');
writeln('');
writeln(chr(9)+'--full-backup path...: Perform a full backup in [path] using files mode');
writeln(chr(9)+'                       This operation export files and configuration files from');
writeln(chr(9)+'                       databases server.');
writeln(chr(9)+'                       It usefull when you want to change servers or to restore');
writeln(chr(9)+'                       basically an entire server.');
writeln('');
writeln('');

writeln('Restoring datas ----------------------------------------------------');
writeln(chr(9)+'--restore..................: Perform a restore by last export command ');
writeln(chr(9)+'--import-config............: Perform a restore by last export command (alias of --restore) ');
writeln(chr(9)+ '                      full file path using the backup with "--backup" command ');
writeln('');
writeln(chr(9)+'--full-restore [path]: Perform a restore by given the tar.gz');
writeln(chr(9)+ '                      full file path using the backup with "--full-backup" command ');

writeln(chr(9)+'--instant-ldap-recover [file]: Perform LDAP recover for InstantLdap backup.');
writeln(chr(9)+ '                              files are stored in /opt/artica/ldap-backup');
writeln('');
writeln('');
writeln('DAR commands --------------------------------------------------------');
writeln(chr(9)+'--dar-status.........: Get dar status');
writeln(chr(9)+'--dar-restore-file...: Restore file (executed by artica)');
writeln(chr(9)+'--dar-restore-full...: Restore full database (executed by artica)');
writeln('Mysql commands --------------------------------------------------------');
writeln(chr(9)+'--mysql-root-password: Change root mysql password ');
writeln(chr(9)+'--list-mysql-databases: List mysql databases ');
end.

