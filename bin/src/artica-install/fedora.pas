unit fedora;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}

interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',global_conf,IniFiles,oldlinux,install_common,logs;

  type
  TFedora=class


private
       GLOBAL_INI:MyConf;
       function AnalyseRequiredPackages(application_requires:string):string;
       logs:Tlogs;
       function ReadFileIntoString(path:string):string;
       Repos_list:TstringList;
       rpm_list:TstringList;
       function ISReposListed(repository:string):boolean;


public
      constructor Create();
      function  ShowRepositories():string;
      procedure CheckPackages();
      procedure FedoraCoreEnlartment;
      procedure LoadReposRequired();
      procedure Free;

END;

implementation

constructor TFedora.Create();
begin
  GLOBAL_INI:=MyConf.Create();
  logs:=Tlogs.Create;
   rpm_list:=TstringList.Create;

end;

procedure TFedora.Free();
begin
   GLOBAL_INI.Free;
   logs.Free;
   rpm_list.Free;
end;

function TFedora.ShowRepositories():string;
begin
CheckPackages();
result:=AnalyseRequiredPackages('www');
result:= result + AnalyseRequiredPackages('postfix');
result:=result +  AnalyseRequiredPackages('rrd');
result:=result +  AnalyseRequiredPackages('auth');
result:=result +  AnalyseRequiredPackages('ldap');
if GLOBAL_INI.get_POSTFIX_DATABASE()='mysql' then result:=result + AnalyseRequiredPackages('mysql');
if GLOBAL_INI.get_MANAGE_MAILBOXES()='yes' then begin
   if GLOBAL_INI.get_MANAGE_MAILBOX_SERVER()='courier' then  result:=result + AnalyseRequiredPackages('courier');
   if GLOBAL_INI.get_MANAGE_MAILBOX_SERVER()='cyrus' then  result:=result + AnalyseRequiredPackages('cyrus');
end;
end;

procedure TFedora.CheckPackages();
var
command_show,ExpressionGrep,datalist:string;
RegExpr:TRegExpr;
Myini:TIniFile;
sIni:myConf;
begin


   command_show:='/bin/rpm -qa >/tmp/artica.rpm-list.txt';
   ExpressionGrep:='([a-z\-_A-Z0-9]+)-[0-9\.]+';
   Shell(command_show);
   writeln('Reading rpm list...');
   datalist:=ReadFileIntoString('/tmp/artica.rpm-list.txt');
   RegExpr:=TRegExpr.create;
   RegExpr.ModifierI:=True;
   RegExpr.expression:=ExpressionGrep;

   if RegExpr.Exec(datalist) then  repeat
          //writeln('Read ' +  RegExpr.Match[1] + '->'+ RegExpr.Match[2]);
          rpm_list.Add(RegExpr.Match[1]);
   until not RegExpr.ExecNext;

   writeln(IntTostr(rpm_list.Count) + ' Packages installed on this system');

end;

//##############################################################################
function TFedora.AnalyseRequiredPackages(application_requires:string):string;
var  path,datas:string;
RegExpr:TRegExpr;
mRes:string;

begin
 path:=GLOBAL_INI.get_INSTALL_PATH() + '/bin/install/repos-fc.txt';
     if not FileExists(path) then begin
       writeln('Error:TFedora.LoadReposRequired -> file not found (' + path + ')');
       readln();
       exit;
     end;
   datas:=ReadFileIntoString(path);

   RegExpr:=TRegExpr.create;
   RegExpr.Expression:='([a-zA-Z0-9\-]+)->' + application_requires;
     if RegExpr.Exec(datas) then  repeat
        if ISReposListed(RegExpr.Match[1])=false then begin
              mRes:=mRes + ' ' + RegExpr.Match[1];
        end;
     until not RegExpr.ExecNext;
    if length(mRes)>0 then mRes:=' ' + mRes;
    result:=mRes;
end;



//#############################################################################
procedure TFedora.LoadReposRequired();
var datas,path:string;
RegExpr:TRegExpr;

begin
     path:=GLOBAL_INI.get_INSTALL_PATH() + '/bin/install/repos-fc.txt';
     if not FileExists(path) then begin
       writeln('Error:TFedora.LoadReposRequired -> file not found (' + path + ')');
       exit;
     end;

     datas:=ReadFileIntoString(path);
     RegExpr:=TRegExpr.create;
     RegExpr.Expression:='([a-zA-Z0-9\-]+)->([a-z]+)';
     if RegExpr.Exec(datas) then  repeat
        Repos_list.Add(RegExpr.Match[1]);
     until not RegExpr.ExecNext;
end;
//##############################################################################
function TFedora.ISReposListed(repository:string):boolean;
var i:integer;
begin

  for i:=0 to rpm_list.Count-1 do begin
      if repository=rpm_list.Strings[i] then begin
        result:=True;
        exit;
      end;
  end;
   result:=False;
end;
//##############################################################################
function TFedora.ReadFileIntoString(path:string):string;
         const
            CR = #$0d;
            LF = #$0a;
            CRLF = CR + LF;
var
   Afile:text;
   i:integer;
   datas:string;
   datas_file:string;
begin

      if not FileExists(path) then begin
        writeln('Error:TFedora.ReadFileIntoString -> file not found (' + path + ')');
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
              writeln('Error:TFedora.ReadFileIntoString -> unable to read (' + path + ')');
           end;
result:=datas_file;


end;
//##############################################################################
procedure TFedora.FedoraCoreEnlartment;
var
   datafile,regex:string;
   RegExpr:TRegExpr;
   Fedoraversion:string;
   yumconf:string;
   YUMINI:TIniFile;
   enlartenment:boolean;
   answer:string;
begin
   datafile:=ReadFileIntoString('/etc/redhat-release');
   regex:='[Ff]edora [Cc]ore [Rr]elease\s+([0-9])';
   RegExpr:=TRegExpr.create;
   RegExpr.expression:=regex;

   if RegExpr.Exec(datafile) then Fedoraversion:=RegExpr.Match[1];

   writeln('This system us Fedora Core v' + Fedoraversion);
   RegExpr.Free;
   yumconf:='/etc/yum.conf';

   if not FileExists(yumconf) then begin
     writeln('Error reading ' + yumconf);
     exit();
   end;

   answer:='yes';
   YUMINI:=TIniFile.Create(yumconf);
   enlartenment:= YUMINI.ReadBool('enlartenment','enabled',false);
   if enlartenment=false then begin
      writeln('Some of the packages you have to install (such as courier-imap) ');
      writeln('are not included in the official Fedora ' + Fedoraversion + ' repositories');
      writeln('The program must activate "enlartenment" into your system in order to');
      writeln('allow "yum" to get specifics repositories');
      writeln('Do you want that the program do this operation (yes/no) ? [yes]:');
      readln(answer);
      if length(answer)=0 then answer:='yes';

      if answer='yes' then begin
          YUMINI.Free;
          writeln('Strip comments in ' + yumconf );
          GLOBAL_INI.set_FileStripDiezes(yumconf);
          YUMINI:=TIniFile.Create(yumconf);
          YUMINI.WriteString('enlartenment','name','Enlartenment Repository for $releasever - $basearch');
          YUMINI.WriteString('enlartenment','baseurl','http://www.enlartenment.com/packages/fedora/$releasever/$basearch/');
          YUMINI.WriteString('enlartenment','gpgkey','file:///etc/pki/rpm-gpg/RPM-GPG-KEY-enlartenment');
          YUMINI.WriteInteger('enlartenment','enabled',1);
          YUMINI.WriteInteger('enlartenment','gpgcheck',1);

          YUMINI.WriteString('enlartenment-sources','name','Enlartenment Repository for $releasever - Sources');
          YUMINI.WriteString('enlartenment-sources','baseurl','http://www.enlartenment.com/packages/fedora/$releasever/SRPMS/');
          YUMINI.WriteString('enlartenment-sources','gpgkey','file:///etc/pki/rpm-gpg/RPM-GPG-KEY-enlartenment');
          YUMINI.WriteInteger('enlartenment-sources','enabled',1);
          YUMINI.WriteInteger('enlartenment-sources','gpgcheck',1);
          writeln('please type "yes" for the next operation if ask');

          Shell('rpm --import http://www.enlartenment.com/RPM-GPG-KEY.mf');

          writeln('Success modify /etc/yum.conf for enlartenment...');
      end
         else begin
             writeln('repositories "enlartenment" enabled...');

      end;
      GLOBAL_INI.Free;
      YUMINI.Free;
end;







end;



end.
