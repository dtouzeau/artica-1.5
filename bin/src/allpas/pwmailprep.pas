{
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

                       Pascal Web Unit project (PWU)

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

 ------------------------------------------------------------------------------
  MailPrep Unit
 ------------------------------------------------------------------------------

  This unit contains functions to prepare SMTP mail messages or SendMail
  messages. Random boundary creator function, and a file content encoder is
  included in this unit.

 ------------------------------------------------------------------------------
  Developer Notes
 ------------------------------------------------------------------------------

  [ 14/OCT/2005 -L505 ]

   -created unit

 ------------------------------------------------------------------------------
  Developer Todo
 ------------------------------------------------------------------------------

 ------------------------------------------------------------------------------
  Contributors/Authors:
 ------------------------------------------------------------------------------
   Lars (L505)


%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
}



unit pwmailprep; {$mode objfpc}{$H+}


{------------------------------------------------------------------------------}
interface
{------------------------------------------------------------------------------}

uses
  base64enc,
  sysutils;


{--------- PUBLIC PROCEDURE/FUNCTION DECLARATIONS -----------------------------}

  function PrepEmailBoundary: string;
  function PrepFileContents(filename: string): string;

{------------------------------------------------------------------------------}


implementation


// prepares a randomly generated boundary for separating files and text in the
// message
function PrepEmailBoundary: string;
const
  CHARSET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  MAX = 20; //the maximum length of the randomized string
var
  iLoc: longword;
begin
  result:= '';
  SetLength(result, MAX);
  for iLoc:= 1 to MAX do
  begin
    randomize;
    result[iLoc] := CHARSET[random(61) + 1];
  end;
end;


// prepares file attachment into a string (base 64 encoded)
function  PrepFileContents(FileName: string): string;
var
  TheFile: file of char;
  temp: string;
  iLoc,                         //Local loop
  len,
  count: longword;
  buffer: array[1..4096] of char; //buffer to hold contents
  oldmode: byte;
begin
  result:= '';
  oldmode:= filemode;
  FileMode:= fmOpenRead; //open it read only. Web files do not always have write permissions. 
  assign(TheFile, filename);
  reset(TheFile);
  temp:= '';
  count:= 0;
  //get file contents into a temporary string
  while not eof(TheFile) do
  begin
    blockread(TheFile, buffer, sizeof(buffer), len);
    SetLength(temp, count + len);
    for iLoc:= 1 to len do
      temp[count + iLoc]:= buffer[iLoc];
    inc(count, len);
  end;
  close(TheFile);
  // encode temporary string contents and then place into function result
  result:= Base64Encode(temp);
  FileMode:= oldmode;
end;



end.


