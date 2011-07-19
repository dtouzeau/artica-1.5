unit debian;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,RegExpr in 'RegExpr.pas',global_conf,IniFiles,unix,logs;

  type
  TDebian=class


private
       GLOBAL_INI:MyConf;
       function ReadFileIntoString(path:string):string;
       Repos_list:TstringList;
       dpkg_list:TstringList;
       procedure ShowScreen(line:string);
       procedure ManualInstall(application_requires:string);
       LOGS:Tlogs;
       function LinuxInfosDistri():string;

public
      constructor Create();
      function  ShowRepositories(confpath:string):string;
      function  AnalyseRequiredPackages(application_requires:string):string;
      function  CheckPackages():boolean;
      procedure LoadReposRequired();
      function UpdateReposConfig():boolean;
      procedure Free;
      debug:Boolean;
      echo_local:boolean;
      ConfigPathDir:string;
      function ISReposListed(repository:string):boolean;
      procedure HTTPInstall(source:string);
      D:boolean;

END;

implementation

constructor TDebian.Create();
begin
  GLOBAL_INI:=MyConf.Create();
  Repos_list:=TstringList.Create;
  dpkg_list:=TstringList.Create;
  LOGS:=Tlogs.Create;
  D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
end;

procedure TDebian.Free();
begin
   GLOBAL_INI.Free;
   Repos_list.Free;
   LOGS.Free;
end;

function TDebian.ShowRepositories(confpath:string):string;
var phppath,distribution,reposfile,command_line:string;
 //install:Tclass_install;
 List:TstringList;
 i:integer;
 D:Boolean;
 Answer:string;
begin
 D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
 LOGS.Debug:=debug;
 LOGS.Enable_echo:=echo_local;
    ShowScreen('************************************************************');
 ShowScreen('ShowRepositories:: "' + confpath + '"');


    if length(confpath)=0 then begin
       phppath:=ExtractFilePath(ParamStr(0));
       distribution:=LinuxInfosDistri();
     
       if length(distribution)=0 then begin
         ShowScreen('ShowRepositories:: Your distribution is not supported...');
         ShowScreen('ShowRepositories:: Unable to determine distribution...');
         exit;
       end;


       reposfile:=phppath + '/install/distributions/' + distribution + '/repositories.txt';
       if not fileexists(reposfile) then begin
          ShowScreen('ShowRepositories:: unable to locate ' + reposfile);
          exit;
         end;
     confpath:=reposfile;
     
     command_line:=phppath + '/install/distributions/' + distribution + '/addmedia.sh';
     if FileExists(command_line) then begin
         writeln('QUESTION: DO YOU WANT TO ADD ' + distribution + ' INTERNET MEDIA IN YOUR SYSTEM ? (yes/no):yes');
         readln(Answer);
         if length(Answer)=0 then answer:='yes';
         if answer='yes' then begin
             fpsystem('/bin/chmod 777 ''' + command_line+'''');
             fpsystem(''''+ command_line+'''');
             fpsystem('/bin/mv ''' + command_line + '''' +  '''' + phppath + '/install/distributions/' + distribution+ '/addmedia.bak');
         end;
     
     end;
     
     
end;


 if UpdateReposConfig()=false then exit;
 ConfigPathDir:=ExtractFilePath(confpath);

 

if not CheckPackages() then begin
    writeln('ShowRepositories:: unable to check packages ');
    exit;
end;




 command_line:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('beffore_check');
 if length(command_line)>0 then begin
    writeln(command_line);
    fpsystem(command_line);
 end else begin
      ShowScreen('ShowRepositories:: No commands lines specified in beffore_check');
 
 end;
 

list:=TstringList.Create;

ShowScreen('ShowRepositories:: Analyse postfix index packages........' );
List.add(AnalyseRequiredPackages('postfix')) ;
ShowScreen('ShowRepositories:: Analyse LDAP index packages...........' );
List.add( AnalyseRequiredPackages('ldap'));
ShowScreen('ShowRepositories:: Analyse APACHE index packages.........');
List.add( AnalyseRequiredPackages('apache'));
ShowScreen('ShowRepositories:: Analyse PHP5 index packages...........');
List.add( AnalyseRequiredPackages('php5'));
ShowScreen('ShowRepositories:: Analyse rrd index packages............');
List.add( AnalyseRequiredPackages('rrd'));
ShowScreen('ShowRepositories:: Analyse auth index packages............');
List.add( AnalyseRequiredPackages('auth'));
ShowScreen('ShowRepositories:: Analyse make index packages............');
List.add( AnalyseRequiredPackages('make'));

 if ParamStr(1)='-install' then begin
    if GLOBAL_INI.COMMANDLINE_PARAMETERS('all') then begin
              ShowScreen('************************************************************');
              ShowScreen('All settings as been applied.......');
              ShowScreen('ShowRepositories:: Analyse cyrus index packages............');
              List.add(  AnalyseRequiredPackages('cyrus22'));

              ShowScreen('ShowRepositories:: Analyse tls index packages............');
              List.add( AnalyseRequiredPackages('tls'));
    
              ShowScreen('ShowRepositories:: Analyse APP_MAILGRAPH index packages............');
              List.add( AnalyseRequiredPackages('APP_MAILGRAPH'));
              
              ShowScreen('ShowRepositories:: Analyse APP_FETCHMAIL index packages............');
              List.add( AnalyseRequiredPackages('APP_FETCHMAIL'));
              
              ShowScreen('ShowRepositories:: Analyse APP_PROCMAIL index packages............');
              List.add( AnalyseRequiredPackages('APP_PROCMAIL'));
    end;

    command_line:='';
    ShowScreen('ShowRepositories:: ' +IntToStr(List.Count) + ' lines');
    for i:=0 to list.Count-1 do begin
           if D then showScreen('ShowRepositories:: increment -> ' + command_line);
           command_line:=command_line + list.Strings[i] + ' ';
    end;
    List.free;
    result:=command_line;
    ShowScreen('************************************************************');
    exit(command_line);

 end;


   
   
if GLOBAL_INI.get_MANAGE_MAILBOXES()='yes' then begin
   ShowScreen('ShowRepositories:: Analyse imap packages............:"' + GLOBAL_INI.get_MANAGE_MAILBOX_SERVER()+'"' + ' v2="' + GLOBAL_INI.CYRUS_GET_V2() + '"');
   if GLOBAL_INI.get_MANAGE_MAILBOX_SERVER()='cyrus' then  begin
      if GLOBAL_INI.CYRUS_GET_V2()='yes' then begin
         ShowScreen('ShowRepositories:: Analyse cyrus22 index packages............');
         List.add( AnalyseRequiredPackages('cyrus22'));
      end;
      
      if GLOBAL_INI.CYRUS_GET_V2()='no' then begin
            ShowScreen('ShowRepositories:: Analyse Cyrus index packages............');
            List.add( AnalyseRequiredPackages('cyrus'));
            
      end;
   end;
end;
 command_line:='';
    ShowScreen('ShowRepositories:: ' +IntToStr(List.Count) + ' lines');
    for i:=0 to list.Count-1 do begin
           command_line:=command_line + list.Strings[i] + ' ';

    end;
    List.free;
    result:=command_line;
    exit(command_line);

    ShowScreen('************************************************************');
end;
//##############################################################################
function TDebian.CheckPackages():boolean;
var
command_show,ExpressionGrep,ConfFile,repositories_list,repositories_list_expression,datalist:string;
RegExpr:TRegExpr;
MyDebug:boolean;
begin
   ConfFile:=GLOBAL_INI.LINUX_CONFIG_PATH() + '/infos.conf';
   if D then ShowScreen('CheckPackages:: Settings stored into ' +  ConfFile);
   MyDebug:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
   
   if ParamStr(1)='-check-pack' then MyDebug:=True;
   
   
   if not fileExists(ConfFile) then  begin
      ShowScreen('CheckPackages:: Unable to locate ' + ConfFile);
      exit;
   end;
   
   
   

   repositories_list:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('repositories_list');
   repositories_list_expression:=GLOBAL_INI.LINUX_REPOSITORIES_INFOS('repositories_list_expression');
   
   
   if length(repositories_list)=0 then begin
      ShowScreen('CheckPackages::ERROR Unable to parse conf file with  REPOSITORIES=>repositories_list');
      exit;
   end;
   
   if length(repositories_list_expression)=0 then begin
      ShowScreen('CheckPackages:: ERROR Unable to parse conf file with  REPOSITORIES=>repositories_list_expression');
      exit;
   end;
   
   
   command_show:=repositories_list +' >/tmp/dpkg-list.txt';
   ExpressionGrep:=repositories_list_expression;
   if MyDebug then writeln(command_show +' >/tmp/dpkg-list.txt');
   fpsystem(command_show +' >/tmp/dpkg-list.txt');
   if D then writeln('Reading repositories...');
   datalist:=ReadFileIntoString('/tmp/dpkg-list.txt');
   RegExpr:=TRegExpr.create;
   RegExpr.ModifierI:=True;
   RegExpr.expression:=ExpressionGrep;
   if MyDebug then writeln('regex -> ' ,ExpressionGrep);

   if RegExpr.Exec(datalist) then  repeat
         if MyDebug then writeln('add into memory -> ' ,RegExpr.Match[1]);
          dpkg_list.Add(RegExpr.Match[1]);
   until not RegExpr.ExecNext;
   
   if D then ShowScreen('CheckPackages:: ' + IntTostr( dpkg_list.Count) + ' Packages installed on this system');
   exit(true);
end;
//##############################################################################
function TDebian.LinuxInfosDistri():string;
var
   ini:myconf;
begin
   ini:=myconf.Create();
   exit(ini.LINUX_DISTRIBUTION());
end;


//##############################################################################
function TDebian.AnalyseRequiredPackages(application_requires:string):string;
var  path,phppath,datas:string;
RegExpr:TRegExpr;
RegExpr2:TRegExpr;
mRes:string;
MyDebug:boolean;
D,add:Boolean;
MList,MList2:TStringList;
i:integer;

begin

    if ParamStr(2)='simulate' then MyDebug:=True;
    D:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');

    path:=GLOBAL_INI.LINUX_CONFIG_PATH() + '/repositories.txt';
    
    if D then writeln('AnalyseRequiredPackages: Path=' + path);

    MList:=TStringList.Create;
    phppath:=ExtractFilePath(ParamStr(0));
    mRes:='';
    

   if not fileExists(path) then  begin
      ShowScreen('Unable to locate ' + path);
      exit;
   end;
   if D then writeln('Loading ' + path);
   MList.LoadFromFile(path);


   datas:=ReadFileIntoString(path);
   RegExpr:=TRegExpr.create;
   RegExpr2:=TRegExpr.Create;
   RegExpr.Expression:='include\((.+?)\)';
   if RegExpr.exec(datas) then begin
       if D then ShowScreen('Include ' + RegExpr.Match[1]);
       path:=phppath + 'install/distributions/' + RegExpr.Match[1] + '/repositories.txt';
       if not fileExists(path) then  begin
          ShowScreen('AnalyseRequiredPackages:: Unable to locate ' + path);
          exit;
       end;
      if D then  ShowScreen('AnalyseRequiredPackages:: Reading ' + path);
       MList2:=TStringList.Create;
       MList2.LoadFromFile(path);
       MList.AddStrings(MList2);
       MList2.Free;
   end;

   RegExpr2.Expression:='^([a-zA-Z0-9_\-\.\+]+)->' + application_requires + ';(http|ftp)(.+);';
   RegExpr.Expression:='^([a-zA-Z0-9_\-\.\+]+)->' + application_requires + ';';
   
   
   for i:=0 to MList.Count-1 do begin
      if D then ShowScreen('AnalyseRequiredPackages:: line (' + intToStr(i) + ') "' + MList.Strings[i] + '" | ' + application_requires);
      add:=true;

      if RegExpr2.Exec(MList.Strings[i]) then  begin
            if D then ShowScreen('FOUND A NOT repository package "'+RegExpr2.Match[1]+'"');
            add:=false;
            if ISReposListed(RegExpr2.Match[1])=false then begin
               if D then ShowScreen('manually installing ' + RegExpr2.Match[1]);
               HTTPInstall(RegExpr2.Match[2] + RegExpr2.Match[3]);
            end else begin
                if D then ShowScreen('AnalyseRequiredPackages:: Package already installed ' + ExtractFilename(RegExpr2.Match[2] + RegExpr2.Match[3]));
            end;
        
        end;



      //--------------- Standard packages --------------------------------------------------------------------------------------------
      if add=true then begin
       if RegExpr.Exec(MList.Strings[i]) then  begin
          if D then ShowScreen('AnalyseRequiredPackages:: Found "' + RegExpr.Match[1] + '" For ' + application_requires);
          if   RegExpr.Match[1]='manual' then begin
                  ShowScreen('AnalyseRequiredPackages:: use manual mode for '  + application_requires);
                  ManualInstall(application_requires);
          
          end else begin
          
              if ISReposListed(RegExpr.Match[1])=false then begin
                 mRes:=mRes + ' ' + RegExpr.Match[1];
                 if D then writeln('AnalyseRequiredPackages:: [' + RegExpr.Match[1] +'] package didn''t exists..');
              end;
          end;
       end;
      end;
       
       
    end;



    if length(mRes)>0 then begin
       mRes:=' ' + trim(mRes);
       if D then if MyDebug=True then ShowScreen('AnalyseRequiredPackages:: RESULT "' + mRes + '"');
       Result:=trim(mRes);
    end;
    
end;
//##############################################################################
procedure TDebian.ManualInstall(application_requires:string);
var  path:string;
RegExpr,FRegExpr:TRegExpr;
prefix:string;
MList,MList2:TStringList;
i:integer;
MustExecute,force:boolean;
commandline:string;

begin
    path:=GLOBAL_INI.LINUX_CONFIG_PATH() + '/' + application_requires + '.txt';
    if not FileExists(path) then begin
       ShowScreen('ManualInstall:: WARNING !!! Unable to stat ' + path);
       exit;
    end;

    
    MList:=TStringList.Create;
    MList2:=TStringList.Create;
    MList.LoadFromFile(path);
    RegExpr:=TRegExpr.create;
    FRegExpr:=TRegExpr.Create;
    
    FRegExpr.Expression:=';=force';
    RegExpr.Expression:='^([a-zA-Z0-9_\-\.\+]+)->(.+);';
    if DirectoryExists('/tmp/repos') then begin
       ShowScreen('Delete content of /tmp/repos');
       fpsystem('rm -rf /tmp/repos/*');
    end;
    ForceDirectories('/tmp/repos');
         for i:=0 to MList.Count-1 do begin
         MustExecute:=false;
         prefix:='';
         force:=false;
         
               if RegExpr.Exec(Mlist.Strings[i]) then begin
                 if FRegExpr.Exec(Mlist.Strings[i]) then begin
                    ShowScreen('Force installing ' + ExtractFileName(RegExpr.Match[2]));
                    MustExecute:=true;
                 end else begin
                     if ISReposListed(RegExpr.Match[1])=false then MustExecute:=true;
                 end;
                  
                 if MustExecute then begin
                    if force then prefix:=' --nodeps --force ';
                    commandline:='wget ' +  RegExpr.Match[2] + ' --output-document=/tmp/repos/' + ExtractFileName(RegExpr.Match[2]);
                    ShowScreen(commandline);
                    fpsystem(commandline);
                    MList2.Add(prefix + '/tmp/repos/' + ExtractFileName(RegExpr.Match[2]));
                 end;
               end;
         end;
         
         
    for i:=0 to MList2.Count-1 do begin
          HTTPInstall(MList2.Strings[i])
    end;



end;


//##############################################################################
procedure TDebian.HTTPInstall(source:string);
var
ext:string;
begin
     ext:=ExtractFileExt(source);
     ShowScreen('############### ' + ExtractFileName(source) + '  ###############');
     ShowScreen('Internet/path source is "' + ext + '" ');
     
     if ext='.rpm' then begin
        ShowScreen('install rpm ');
        fpsystem('rpm -iv ' +source);
        exit;
     end;
     if ext='.deb' then begin
        ShowScreen('install deb ');
        fpsystem('dpkg -i ' +source);
        exit;
     end;

end;


//##############################################################################
procedure TDebian.LoadReposRequired();
var datas,path:string;
RegExpr:TRegExpr;

begin
path:=ConfigPathDir + '/infos.conf';


   if not fileExists(path) then  begin
      LOGS.logsInstall('Unable to locate ' + path);
      writeln('LoadReposRequired::Unable to locate ', path);
      exit;
   end;


     
     datas:=ReadFileIntoString(path);
     RegExpr:=TRegExpr.create;
     RegExpr.Expression:='([a-zA-Z0-9\-\.\+]+)->([a-z]+)';
     if RegExpr.Exec(datas) then  repeat
        Repos_list.Add(RegExpr.Match[1]);
     until not RegExpr.ExecNext;
end;
//##############################################################################
function TDebian.ISReposListed(repository:string):boolean;
var i:integer;
D:boolean;
DD:boolean;
begin
   DD:=false;
   D:=false;
   DD:=GLOBAL_INI.COMMANDLINE_PARAMETERS('debug');
   if DD then ShowScreen('ISReposListed:: ' + repository + ' search in "'  + IntTOStr(dpkg_list.count) + '" array lines');
   if dpkg_list.count=0 then begin
        if DD then ShowScreen('ISReposListed:: Load repositories list');
        CheckPackages();
   end;
   

  for i:=0 to dpkg_list.Count-1 do begin
      if D then ShowScreen('ISReposListed:: ' + repository + '<>"'  + dpkg_list.Strings[i] + '"');
      if repository=dpkg_list.Strings[i] then begin
        if DD then ShowScreen('ISReposListed:: ' + repository +' detected...');
        result:=True;
        exit;
      end;
  end;
   result:=False;
end;
//##############################################################################



function TDebian.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   datas:string;
   datas_file:string;
begin
      datas_file:='';
      if not FileExists(path) then begin
        writeln('Error:TDebian.ReadFileIntoString -> file not found (' + path + ')');
        exit;

      end;
      TRY
     assign(Afile,path);
     reset(Afile);
     while not EOF(Afile) do
           begin
           readln(Afile,datas);
           datas_file:=datas_file + datas +CRLF;
           end;

close(Afile);
             EXCEPT
              writeln('Error:TDebian.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;
//##############################################################################

function TDebian.UpdateReposConfig():boolean;
var
   linux_config_path,update_config,update_config_mode,updater_config_path,update_config_source,update_after_execute,update_config_notif,tempdata:string;
   iniSource,iniConfig:TiniFile;
   Sections,keys:TStringList;
   i,t:integer;
   RegExpr:TRegExpr;
begin
     linux_config_path:=GLOBAL_INI.LINUX_CONFIG_PATH();
     update_config:=GLOBAL_INI.LINUX_INSTALL_INFOS('update_config');
     
     if not fileExists(linux_config_path + '/infos.conf') then begin
        writeln('No configuration file (unable to stat) ' + linux_config_path + '/infos.conf');
        exit(false);
     end;
     
     if update_config<>'yes' then begin
        writeln('No operations defines before launching the updater/installer....(' + update_config + ' in path ' + linux_config_path + ')');
        exit(true);
     end;
     
     
     update_config_mode:=GLOBAL_INI.LINUX_INSTALL_INFOS('update_config_mode');
     updater_config_path:=GLOBAL_INI.LINUX_INSTALL_INFOS('updater_config_path');
     update_config_source:=GLOBAL_INI.LINUX_CONFIG_PATH() + '/' + GLOBAL_INI.LINUX_INSTALL_INFOS('update_config_source');
     update_after_execute:=GLOBAL_INI.LINUX_INSTALL_INFOS('update_after_execute');
     update_config_notif:=GLOBAL_INI.LINUX_INSTALL_INFOS('update_config_notif');
     

     if not FileExists(updater_config_path) then begin
        writeln('Unable to locate ' + updater_config_path + ' ABORTING...');
        exit(false);
     end;
     
     
     
     if update_config_mode='inifile' then begin
           writeln('Change source configuration file ' + updater_config_path);
           
           if not FileExists(update_config_source) then begin
              writeln('Unable to locate ' +  update_config_source + ' (specified in update_config_source key) ABORTING...');
              exit(false);
           end;
           


           GLOBAL_INI.set_FileStripDiezes(updater_config_path);
           Sections:=TStringList.Create;
           keys:=TStringList.Create;
           iniSource:=TiniFile.Create(update_config_source);
           iniConfig:=TiniFile.Create(updater_config_path);
           RegExpr:=TRegExpr.Create;
           RegExpr.Expression:='(.+)->(.+)=(.+)';
            if RegExpr.Exec(update_config_notif) then begin
               if iniConfig.ReadString(RegExpr.Match[1],RegExpr.Match[2],'')=RegExpr.Match[3] then begin
                    writeln('Already changed... aborting cause (update_config_notif)');
                    Sections.Free;
                    keys.Free;
                    RegExpr.Free;
                    iniConfig.free;
                    exit(true);
               end;
           end;
           
           
           iniSource.ReadSections(Sections);
           
              for i:=0 to Sections.Count-1 do begin
                      writeln('UPDATE Section: [' + Sections.Strings[i] + ']');
                      iniSource.ReadSection(Sections.Strings[i],keys);
                      for t:=0 to keys.Count -1 do begin
                          writeln('UPDATE key :"' + keys.Strings[t] + '"');
                          tempdata:=iniSource.ReadString(Sections.Strings[i],keys.Strings[t],'');
                          iniConfig.WriteString(Sections.Strings[i],keys.Strings[t],tempdata);
                      end;
              end;
       Sections.Free;
       keys.Free;
       RegExpr.Free;
       iniConfig.free;

     
     end;
     
     
     if length(update_after_execute)>0 then begin
         writeln('Execute ' + update_after_execute);
         fpsystem(update_after_execute);
     end;

          exit(true);


end;
//##############################################################################
procedure TDebian.ShowScreen(line:string);
 var  mLogs:Tlogs;
 begin
      mLogs:=Tlogs.Create();
      if ParamStr(1)<>'setup' then writeln('TDebian::' + line) else writeln(line);
      mLogs.logs('TDebian::' + line);
      mLogs.free;

 END;




end.
