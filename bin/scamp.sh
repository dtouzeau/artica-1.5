#!/usr/bin/env bash
# File Name: scamp.sh
# Version: 5.0.4b
# Date: Mon, 23 February 2009 21:53:34 GMT
# Author: Gerard Seibert <gerard@seibercom.net>

## ************************ DECLARE STATEMENTS *******************************##
# Declare statements - Should not be altered !
declare -a DIRLIST
declare -a DL_ERROR
declare -a MSRBL_LIST
declare -a ZIPFILES
declare -i CREATE_CONFIG
declare -i DIRLIST_COUNT
declare -i FAILED
declare -i GET_LDB
declare -i get_malware
declare -i get_msrbl
declare -i get_sane
declare -i get_securite
declare -i INSTALLED
declare -i LIMIT
declare -i MK_LOG
declare -i MSRBL_COUNT
declare -i NO_UPDATE
declare -i RELOAD
declare -i REST
declare -i RESTING
declare -i W_SUM
declare -i ZIPFILES_COUNT

## ************************************************************************** ##
: << DEFAULTS
These are the default values for the program. They can be over written by
1) using the [-c]option and creating a new config file
DEFAULTS

Script_Name="scamp"
Version="5.0.4b"
New_Config_Ver="3"

# You can set the location of the Clamav database here using the $SIG_DB
# variable or you can set it using an environmental variable.
# By default, it is NULL -- you must set it in the config file. The config
# file setting will OVER-RIDE the setting here or in the environment.
# Place the exact PATH between the double quotation marks.

SIG_DB=${SIG_DB:-""}

C_GROUP=${C_GROUP:-"clamav"}
C_PID=${C_PID:-"/var/run/clamav/clamd.pid"}
C_USER=${C_USER:-"clamav"}
CONFIG_FILE=${CONFIG_FILE:-/etc/${Script_Name}.conf}
GET_LDB=${GET_LDB:-1}
get_malware=${get_malware:-1}
get_msrbl=${get_msrbl:-1}
get_sane=${get_sane:-1}
get_securite=${get_securite:-1}
MK_LOG=${MK_LOG:-0}
RELOAD=${RELOAD:-1}
REST=${REST:-0}
W_SUM=${W_SUM:-1}

## Other Global variables:
# GPG options
GPG_OPT="--no-options -q --no-default-keyring"
## ************************************************************************** ##
function readconf()
{
match=0
while read line; do
# skip comments
  [[ ${line:0:1} == "#" ]] && continue
# skip empty lines
  [[ -z "$line" ]] && continue
# still no match? lets check again
    if [[ $match == 0 ]]; then
# do we have an opening tag ?
      if [[ ${line:$((${#line}-1))} == "{" ]]; then
# strip "{"
        group=${line:0:$((${#line}-1))}
# strip whitespace
        group=${group// /}
# do we have a match ?
          if [[ "$group" == "$1" ]]; then
            match=1
            continue
          fi
        continue
      fi
# found closing tag after config was read - exit loop
    elif [[ ${line:0} == "}" && $match == 1 ]]; then
      break
    # got a config line eval it
    else
      eval $line
    fi
done < "${CONFIG_FILE}"
}
## ************************************************************************** ##
# Read the existing config file if it exists
if [[ -s ${CONFIG_FILE} ]]; then
  readconf "default"
fi
## ************************************************************************** ##
# Get any command line options
while [[ $# -gt 0 ]]; do
  case "$1" in
  -h)
    printf "\n\n
USAGE: $0 [ [-c] -h [-l|-L] [-q|-Q] [-r|-R] -v]\n\n
\t(-c) to create a new config file (exit after completion}
\t(-h) to view this help message
\t(-l) (lower case ell) to turn off logging
\t(-L) to activate logging
\t(-q) to skip printing the summary screen
\t(-Q) to display a summary screen
\t(-r) to deactivate the random download timing feature
\t(-R) to activate the random download feature
\t(-v) to view the script version and exit\n\n\n\n"
    exit 0
  ;;
# Turn the logging function off.
  -l)
    MK_LOG=0
  ;;
# Turn the logging function on.
  -L)
    MK_LOG=1
  ;;
# Turns on the random download timer. Works only via CRON
  -R)
    REST=1
    ;;
    -v)
    printf "\n\n\tVersion: %s\n\n" ${Version}
    exit 0
  ;;
# Turn random timer off
  -r)
    REST=0
  ;;
# Turn summary output off
  -q)
    W_SUM=0
  ;;
# Turn summary output on
  -Q)
    W_SUM=1
  ;;
  -c)
    CREATE_CONFIG=1
  ;;
  *)
    printf "\nIncorrect flag - Enter $0 -h to view help menu.\n"
    exit 1
  ;;
  esac
# Check the next parameter if there is one
  shift
done
## ***************************************************************************##
function start ()
{

if [[ ! -s ${CONFIG_FILE} ]]; then
# See if we are running via CRON. This script cannot be configured via CRON
  if [[ ! -t 0 ]]; then
    printf "\n\tSorry, you need to run this script interactly the first time.\n\n"
    printf "\tOnce the %s file is created you can run it via CRON\n\n" ${CONFIG_FILE}
    printf "\tError Code 4\n"
# EXIT the program
    exit 4
  fi

<< ASSIGN-FUNCTION
This simple function takes the preset value of a variable and compares it with
the value imput by the user. If it is a new value, the old value is over written
and the new value is placed in the 'config file'.
ASSIGN-FUNCTION

# This function assigns the user input to the correct variables
function assign_value ()
{
# Check to see how many variables were passed
  x=${#*}
# If two were passed
  if [[ "${x}" == "2" ]]; then
    eval ${2}=\${1}
  fi
}

: << ReadInput
We are going to get the values for the variables to be placed in the 'config' file.
ReadInput

echo "<><><><><><>><><><>><><><><><><><><><><><><><><><><><><><><><><><>>><><>"
while [[ ! ${clam_db} ]]; do
clear
if [[ -n ${ER_1} ]]; then
  printf "\n\n\t%s\n\n" ${ER_1}
fi

printf "Enter the location of the Clamav Database. It must be a Fully Qualified
path. Check your documentation. If it is entered incorrectly, the script will
fail. Usually locations:\n
/var/db/clamav
/var/lib/clamav
/usr/local/share/clamav\n
You MUST enter a PATH!\n\n
Press <RETURN> to accept default -- or modify if required
Clamav Database location:\n\n"
  if [[ -z ${SIG_DB} ]]; then
    printf "[NO DEFAULT SETTING] "
    read clam_db
  else
    printf "[Default: %s ] " ${SIG_DB}
    read clam_db
    break
  fi
done

# Check to see if the ${clam_db} variable is empty.
if [[ -n ${clam_db} ]]; then
  assign_value ${clam_db} SIG_DB
fi

clear

# Get the rest of the needed config file variables.
printf "\nAll of these may be set to DEFAULT by pressing <RETURN> at each option.\n
Check the 'PidFile' setting in your clamd.conf file
for the location of the Clamd PID file\n\n
Enter PID file location:\n
[Default: %s ] " ${C_PID}
read pid_file
assign_value ${pid_file} C_PID
printf "\f"

printf "\n\n\nDo you want to turn on the random download timer?
It only works when run via CRON. 1=on & 0=off\n
[default: %d ] " ${REST}
read rest
assign_value ${rest} REST
printf "\f"

printf "\n\n\nEnter: GROUP ownership of files:\n
[default: %s ] " ${C_GROUP}
read C_Group
assign_value ${C_Group} C_GROUP
printf "\f"

printf "\n\n\nEnter: USER ownership of files:\n
[default: %s ] " ${C_USER}
read C_User
assign_value ${C_User} C_USER
printf "\f"

printf "\n\n\nInstall the Sanesecurity files: 1=yes & 0=no\n
[default = %d ] " ${get_sane}
read i_sane
assign_value ${i_sane} get_sane
printf "\f"

printf "\n\n\nInstall the MSRBL files: 1=yes & 0=no\n
[default = %d ] " ${get_msrbl}
read i_msrbl
assign_value ${i_msrbl} get_msrbl
printf "\f"

printf "\n\n\nInstall the Malware files: 1=yes & 0=no\n
[default = %d ] " ${get_malware}
read i_malware
assign_value ${i_malware} get_malware
printf "\f"

printf "\n\n\nInstall the Securiteinfo files: 1=yes & 0=no\n
[default = %d ] " ${get_securite}
read i_securiteinfo
assign_value ${i_securiteinfo} get_securite
printf "\f"

printf "\n\n\nReload clamd after update: 1=yes & 0=no\n
[default = %d ] " ${RELOAD}
read auto_update
assign_value ${auto_update} RELOAD
printf "\f"

printf "\n\n\nInstall the *.ldb files: 1=yes & 0=no\n
[Default = %d ] " ${GET_LDB}
read ldb_files
assign_value ${ldb_files} GET_LDB
printf "\f"

printf "\n\n\nDo you want a summary screen printed out when finished?
Enter 1 to display the screen or 0 to skip it.\n
[Default = %d ] " ${W_SUM}
read w_sum
assign_value ${w_sum} W_SUM
printf "\f"

printf "\n\n\nDo you want to create a log file?
Default Location: /var/log/scamp.log
Enter 1 for yes & 0 for no.\n
[Default = %d ] " ${MK_LOG}
read mk_log
assign_value ${mk_log} MK_LOG
printf "\f"

# Print out a completion message
printf "\n\n\n
\tPlease Wait\n
\tWe are now configuring the system and installing the files\n
\tCongratulations, you may now run the program normally\n"

: << Write-Config
This is where we actually write the configuration file. It should only be done
once or when there is a change in the configuration file.
Write-Config

echo # Config version > ${CONFIG_FILE}
echo Config_Version "{" >> ${CONFIG_FILE}
echo Config_Ver=${New_Config_Ver} >> ${CONFIG_FILE}
echo "}" >> ${CONFIG_FILE}
# default settings >> ${CONFIG_FILE}
echo default "{" >> ${CONFIG_FILE}
# Sets the main Clamav database directory
echo SIG_DB=${SIG_DB} >> ${CONFIG_FILE}
# Sets the tmp directory
echo T_DIR='${SIG_DB}/tmp' >> ${CONFIG_FILE}
# Sets the msrbl directory
echo MSR_DIR='${T_DIR}/msr' >> ${CONFIG_FILE}
# Set the Securiteinfo dir
echo SI_DIR='${T_DIR}/securite' >> ${CONFIG_FILE}
# Set the Malware dir
echo MW_DIR='${T_DIR}/malware' >> ${CONFIG_FILE}
# Set the Clamav group owner
echo C_GROUP=${C_GROUP} >> ${CONFIG_FILE}
# Set the location for the 'clamd.pid' file
echo C_PID=${C_PID} >> ${CONFIG_FILE}
# Set the random download function - 0=off & 1=on
echo REST=${REST} >> ${CONFIG_FILE}
# Set the user
echo C_USER=${C_USER} >> ${CONFIG_FILE}
# Get Malware files - 1 means yes
echo get_malware=${get_malware} >> ${CONFIG_FILE}
# Get the MSRBL files - 1 means yes
echo get_msrbl=${get_msrbl} >> ${CONFIG_FILE}
# Get the sanesecurity files - 1 means yes
echo get_sane=${get_sane} >> ${CONFIG_FILE}
# Get the securiteinfo files - 1 means yes
echo get_securite=${get_securite} >> ${CONFIG_FILE}
# Location of the sanesecurity key
echo gpg_key_url="http://www.sanesecurity.net/publickey.gpg" >> ${CONFIG_FILE}
# MSRBL URL
echo MSRBL="rsync://rsync.mirror.msrbl.com/msrbl/" >> ${CONFIG_FILE}
# Malware file name
echo MW_FILE="mbl.db" >> ${CONFIG_FILE}
# Malware URL
echo MW_URL="http://www.malware.com.br/cgi/submit-agressive?action=list_clamav'&'type=agressive" >> ${CONFIG_FILE}
# Whether to download the *.ldb files - 0 means yes and 1 means no
echo GET_LDB=${GET_LDB} >> ${CONFIG_FILE}
# Whether to reload clamd if new files are installed - NO to stop
echo RELOAD=${RELOAD} >> ${CONFIG_FILE}
# Sanesecurity URL
echo SANE="rsync://rsync.sanesecurity.net/sanesecurity" >> ${CONFIG_FILE}
# Sanesecurity tmp directory
echo SANE_DB='${T_DIR}/sane' >> ${CONFIG_FILE}
# The following are the individual securiteinfo files
echo SI_URL1="http://clamav.securiteinfo.com/vx.hdb.gz" >> ${CONFIG_FILE}
echo SI_URL2="http://clamav.securiteinfo.com/honeynet.hdb.gz" >> ${CONFIG_FILE}
echo SI_URL3="http://clamav.securiteinfo.com/securiteinfo.hdb.gz" >> ${CONFIG_FILE}
echo SI_URL4="http://clamav.securiteinfo.com/antispam.ndb.gz" >> ${CONFIG_FILE}
# These are the combined securiteinfo files in one variable
echo SI_URL='$SI_URL1" "$SI_URL2" "$SI_URL3" "$SI_URL4' >> ${CONFIG_FILE}
# These are the individual MSRBL files
echo msrbl_Images='MSRBL-Images.hdb' >> ${CONFIG_FILE}
echo msrbl_SPAM_CR='MSRBL-SPAM-CR.ndb' >> ${CONFIG_FILE}
echo msrbl_SPAM='MSRBL-SPAM.ndb' >> ${CONFIG_FILE}
# Whether to display the summary screen
echo W_SUM=${W_SUM} >> ${CONFIG_FILE}
# Whether to create a log file
echo MK_LOG=${MK_LOG} >> ${CONFIG_FILE}
echo "}" >> ${CONFIG_FILE}
chmod 0664 ${CONFIG_FILE}
fi

# We will now exit - The config file has been created.
exit
}
##****************************************************************************##
# Check the config file version - update if required

if [[ ${CREATE_CONFIG} -gt 0 ]]; then
  if [[ -e ${CONFIG_FILE} ]]; then
    rm -f ${CONFIG_FILE}
  fi
  start
fi

if [[ -s ${CONFIG_FILE} ]]; then
  readconf "Config_Version"
    if [[ ! "${New_Config_Ver}" == "${Config_Ver}" ]]; then
      ER_1="You have an out dated configuration file. We need to create a new one"
      rm -f ${CONFIG_FILE}
      start
    fi
fi

if [[ ! -s ${CONFIG_FILE} ]]; then
  start
fi

## ***************************************************************************##
# Exit the program if the $SIG_DB setting not set.
if ! [[ ${SIG_DB} ]]; then
# Not set so exit program.
  printf "
You must set the SIG_DB variable (location of the Clamav database)
for this script to work.\n
Aborting program ...\n"
  exit 2
fi

export PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin
# For tcsh or csh shells you may need to use this instead. Comment out above
# and uncomment this. Modify as required.
# set PATH = (/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin)
##****************************************************************************##
# Make sure the directory exist. Create if not.
for D in ${T_DIR} ${SANE_DB} ${MSR_DIR} ${SI_DIR} ${MW_DIR}
  do
    if [ ! -d ${D} ]; then
      mkdir -p -m 0755 ${D}
      chown ${C_USER}:${C_GROUP} ${D}
      MEC=$?
        case $MEC in
          [0]*)
# No problems
            :
          ;;
          *)
# Something happened - the directory structure could not be created.
# The program is being forced to exit.
# This might be a file permission problem.
            printf "
Unable to create %s
Check your system and rerun script\n
Exiting ...\n" ${D}
            exit 3
          ;;
        esac
    fi
  done
## ************************************************************************** ##
# See if we are to reload clamd after update
if [ "${RELOAD}" -gt 0 ]; then
# Check to see if the path to the PID file is set
  if [[ ${C_PID} ]]; then
# Now make sure that the file actually exists
    if [[ -s ${C_PID} ]]; then
      CLAMD_PID=`cat ${C_PID}`
    fi
# OK, the file doesn't exist so try to get the PID of the clamd program
  elif which pidof > /dev/null; then
    CLAMD_PID=`pidof clamd`
  else
  # Maybe 'pidof' is not available. Try a more direct method
    if [[ `ps -U ${C_USER} | awk '/clamd/ { print $1 }'` ]]; then
      CLAMD_PID=`ps -U ${C_USER} | awk '/clamd/ { print $1 }'`
    else
    # OK, we cannot get the PID. Set $RELOAD to NO
      if [[ ! ${CLAMD_PID} ]]; then
        echo "Unable to get clamd PID. Turning 'RELOAD' off"
        RELOAD=0
      fi
    fi
  fi
fi
## ************************************************************************** ##
# Create the rsync file with the MSRBL files to download
# We delete any old version to insure that only the latest on is used.
if [[ -e ${MSR_DIR}/msrbl-files.txt ]]; then
  rm -f ${MSR_DIR}/msrbl-files.txt
fi
echo "MSRBL-Images.hdb" > ${MSR_DIR}/msrbl-files.txt
echo "MSRBL-SPAM-CR.ndb" >> ${MSR_DIR}/msrbl-files.txt
echo "MSRBL-SPAM.ndb" >> ${MSR_DIR}/msrbl-files.txt
chown ${C_USER}:${C_GROUP} ${MSR_DIR}/msrbl-files.txt
## ************************************************************************** ##
# Make sure we can find the program paths
# We will test for 'which' first
if ! which which > /dev/null; then
  printf "
Cannot find 'which'
If you are running this program from CRON
you probably need to set the path in the CRON file.
You might check out this URL for further information:
http://www.unixgeeks.org/security/newbie/unix/cron-1.html\n"
# Exit with code 5
  exit 5
fi

# Make sure the required programs are present!
if which curl >/dev/null; then
  DL_AGENT="curl"
  CMD=" -q --silent --remote-name --location --remote-time"
elif which wget >/dev/null; then
  DL_AGENT="wget"
  CMD=" -q -N "
else
  echo "Neither curl or wget can be found. Exiting program"
fi

# We will exit if one of the four named programs are not found.
for f in clamscan gunzip cmp rsync
do
  if ! which $f > /dev/null; then
    printf "
Cannot find: %s
We have to exit\n" ${f}
    exit 6
  fi
done
RSY="rsync"
## ************************************************************************** ##
# Set up GPG, if necessary
cd ${SANE_DB}
if [ ! -s "${SANE_DB}/pubring.gpg" ]; then
  printf "\f"
  echo "No GPG keyring found; initialising" >&2
  echo "(This should only occur once)" >&2
  printf "\n"
    case ${DL_AGENT} in
    [wget]*)
      if ! wget -O - "$gpg_key_url" | gpg ${GPG_OPT} --keyring "${SANE_DB}/pubring.gpg" --import -
        then
         echo "ERROR: could not import GPG public key; aborting" >&2
        exit 7
      fi
    ;;
    [curl]*)
      if ! curl -q --silent "$gpg_key_url" | gpg ${GPG_OPT} --keyring "${SANE_DB}/pubring.gpg" --import -
        then
          echo "ERROR: could not import GPG public key; aborting" >&2
        exit 7
      fi
    ;;
    *)
      echo "Neither 'curl' nor 'wget' available. We are forced to exit"
      exit 6
    esac
fi

chown ${C_USER}:${C_GROUP} "${SANE_DB}/pubring.gpg"
chmod 0664 "${SANE_DB}/pubring.gpg"

# Remove the superfilous 'pubring.gpg~' file if it exists
if [[ -e "${SANE_DB}/pubring.gpg~" ]]; then
  rm -f "${SANE_DB}/pubring.gpg~"
fi

##************************** FUNCTIONS ***************************************##
##****************************************************************************##
# This is the check_install function.
function check_install ()
{
cd ${T_DIR}
# chmod files
for C in $(ls *.?db *.db *.ftm 2>/dev/null)
do
  chmod 0664 ${C}
done
## Create an array of the files to be checked
LIMIT=0
for F in $(ls *.?db *.db *.ftm 2>/dev/null)
do
  if [[ -e ${F} ]]
    then
    DIRLIST[$LIMIT]=${F}
    LIMIT=$(( LIMIT+1 ))
  fi
done

## Initialize variables and start checking the files.
DIRLIST_COUNT=${#DIRLIST[*]}
FAILED=0
INSTALLED=0
LIMIT=0
NO_UPDATE=0

while [ "$LIMIT" -lt "$DIRLIST_COUNT" ]
do
clamscan --quiet --no-summary -d ${DIRLIST[$LIMIT]} </dev/null > /dev/null 2>&1
EC=$?
  case ${EC} in
  [0]*)
# Clamscan did not report any errors.
# Now check to see if this is a newer signature file.
# cmp will respond with '0' if the files are identical.
# 1 if they are different.
# 2 if the file does not exist.
# We will trap the exit code in variable 'CEC'.
# We are using the 's' flag to slightly improve speed.
# Remove them for more detailed output
# First make sure the file is larger than 0
    if [ -s ${DIRLIST[$LIMIT]} ]; then
      cmp -s ${DIRLIST[$LIMIT]} $SIG_DB/${DIRLIST[$LIMIT]}
      CEC=$?
    else
# The file has a 0 length -- bad
      CEC=9
    fi
  ;;
  *)
# Clamscan reported an error -- probably error 50.
# Therefore we will not attempt to install this file.
# The actual error code will be printed out in the event of a problem.
# The failed file will be erased.
# Comment out the line below beginning with 'rm' to save it.
# The variable $MVEC will be set to 'X' -- no-op
    echo -e "\n \a"
    echo " ********** WARNING **********"
    echo "Unable to install: ${DIRLIST[$LIMIT]}"
    echo "Clamscan exited with error code "${EC}
    echo "Try downloading and installing the file again."
    echo -e "\n"
    FAILED=$((FAILED+1))
    rm -f ${DIRLIST[$LIMIT]}
    MVEC="X"
    CEC=9
  ;;
  esac
# Process the cmp exit code
case $CEC in
  [0]*)
# The files are identical
# Update the 'NO_UPDATE' counter.
# Delete the file
# The variable $MVEC will be set to 'X' -- no-op
    if [[ ${W_SUM} -gt 0 ]] || [[ ${F_SUM} -gt 0 ]]; then
      printf "\t%sNo update available ${DIRLIST[$LIMIT]}\n"
    fi
    NO_UPDATE=$((NO_UPDATE+1))
    rm -f ${DIRLIST[$LIMIT]}
    MVEC="X"
  ;;
  [1]*)
# The files are different
# We will install the file
# Trap the 'mv' exit code in 'MVEC'
    mv -f ${DIRLIST[$LIMIT]} $SIG_DB/${DIRLIST[$LIMIT]}
    MVEC=$?
  ;;
  [2]*)
# Error code 2 usually means that file(2) does not exist.
# We will attempt to install file(1).
# Trap the 'mv' exit code in 'MVEC'
    mv -f ${DIRLIST[$LIMIT]} $SIG_DB/${DIRLIST[$LIMIT]}
    MVEC=$?
  ;;
  [9]*)
# Bad news
# Something was wrong with the file. Probably 0 length
    :
  ;;
  *)
# cmp encountered a problem.
# We will not try to install the file.
# The file will be erased.
# The variable $MVEC will be set to 'X' -- no-op
# Incrementing the 'FAILED' counter
    echo "cmp issued error code $CEC"
    echo "Unable to update ${DIRLIST[$LIMIT]} at this time"
    echo "Try again later"
    MVEC="X"
    rm -f ${DIRLIST[$LIMIT]}
    FAILED=$((FAILED+1))
  ;;
esac
# Process the mv exit code
case $MVEC in
  [0]*)
# Everything went well. The file installed correctly.
# Increment the 'INSTALLED' counter.
    if [[ ${W_SUM} -gt 0 ]] || [[ ${F_SUM} -gt 0 ]]; then
      printf "\tInstalled:......... ${DIRLIST[$LIMIT]}\n"
    fi
    INSTALLED=$((INSTALLED+1))
  ;;
  X*)
# Nothing happens here.
# For possible future use!
  :
  ;;
  *)
# OPPS, something went wrong.
# We are unable to install the file
# The file will be deleted
# Increment the 'FAILED' counter
    echo "mv issued error code $MVEC"
    echo "Unable to install at this time!"
    echo "Please try again later"
      if [ -n ${DIRLIST[$LIMIT]}]; then
        rm -f ${DIRLIST[$LIMIT]}
        FAILED=$((FAILED+1))
      fi
esac
# Increment the 'LIMIT' counter.
LIMIT=$((LIMIT+1))
# Clear the 'MVEC' flag -- set to NULL
unset MVEC
done
}
##****************************************************************************##
# This is the get_files function.
function get_files ()
{

: << DELAY_DOWNLOAD
This is the "snooze" function. It will delay the start of a download session
between 0 and 546 seconds (Roughly 9 minutes). It will only work when the
script is run via CRON.
The variable $REST activates this function. 0=off & 1=on
DELAY_DOWNLOAD
# See if the variable 'REST' has been set or if forcing it from command line
if [[ ${REST} -gt 0 ]]
  then
# See if we are running via cron
    if [[ ! -t 0 ]]
      then
# Use the BASH RANDOM function to generate a random number between 0 & 32767
        RESTING=$((RANDOM/60))
        sleep ${RESTING}
    fi
fi

# We now download the MSRBL files using the the specified program.

if [[ ${get_msrbl} -gt 0 ]]; then
  cd ${MSR_DIR}
  rsync -tu --files-from="${MSR_DIR}/msrbl-files.txt" ${MSRBL} ${MSR_DIR}
    for M in $(ls *.?db *.db 2>/dev/null); do
      chmod 0664 ${M}
      chown ${C_USER}:${C_GROUP} $M
      cp -pf ${M} ${T_DIR}
    done
fi

# Get everything else except the Malware files
if [[ ${get_securite} -gt 0 ]]; then
  cd ${SI_DIR}
  for D in ${SI_URL}
    do
      FN=`basename ${D}`
        if [[ "${DL_AGENT}" == "curl" ]]; then
          curl ${CMD} -z "${FN}" $D
        elif [[ "${DL_AGENT}" == "wget" ]]; then
          wget ${CMD} ${D}
        else
        echo "
We don't seem to have a download agent.
Sorry, but we have to exit
"
        exit 6
        fi
      EC=$?
        if [ ${EC} -gt 0 ]; then
          dl_error ${DL_AGENT} ${EC} ${FN}
        fi

# Unset variables
    unset EC
    unset dl_error
    done

# Call the unzip function
  unzip
  for SI in $(ls *.?db *.db 2>/dev/null); do
    chmod 0664 ${SI}
    chown ${C_USER}:${C_GROUP} ${SI}
    mv -f ${SI} ${T_DIR}
  done
fi

# Get the Malware files
if [[ ${get_malware} -gt 0 ]]; then
  cd ${MW_DIR}
  for D in ${MW_URL}
    do
      FN=${MW_FILE}
        if [[ "${DL_AGENT}" == "curl" ]]; then
          curl -q -L -s --output "${MW_FILE}" -z "${MW_FILE}" $D
        elif [[ "${DL_AGENT}" == "wget" ]]; then
          wget -q -O - ${MW_URL} > ${MW_DIR}/${MW_FILE}
        else
          echo "
We don't seem to have a download agent.
Sorry, but we have to exit
"
        exit 6
        fi
      EC=$?
        if [ ${EC} -gt 0 ]; then
          dl_error ${DL_AGENT} ${EC} ${FN}
        fi

# Unset variables
      unset EC
      unset dl_error
    done
fi

# Change file permission & ownership and copy to TMP directory.
for MW in $(ls *.?db *.db 2>/dev/null); do
  chmod 0664 ${MW}
  chown ${C_USER}:${C_GROUP} ${MW}
  cp -fp ${MW} ${T_DIR}
done

# SaneSecurity Files
# See if the "get_sane" flag is set
if [[ ${get_sane} -gt 0 ]]; then
# Change to the sanesecurity tmp database directory
  cd ${SANE_DB}
# Check to see if the "GET_LDB" flag is set
    if [[ "${GET_LDB}" -eq 0 ]]; then
      if [ -e *.ldb ]; then
        rm -f *.ldb
      fi
      rsync -rtu --exclude=*.ldb $SANE $SANE_DB
      EC=$?
      if [ ${EC} -gt 0 ]; then
        dl_error $RSY ${EC} ${FN}
      fi
    else
      rsync -rtu $SANE $SANE_DB
      EC=$?
        if [ ${EC} -gt 0 ]; then
          dl_error ${RSY} ${EC} ${FN}
        fi
    fi
# Unset variables
  unset EC
  unset dl_error
# Change the file ownership and mode
  for C in $(ls *.sig *.?db *.txt *.ftm 2>/dev/null); do
    chmod 0664 ${C}
    chown ${C_USER}:${C_GROUP} ${C}
  done
  # Check the signature - discard if bad
  for F in $(ls *.?db *.ftm 2>/dev/null)
    do
      if ! gpg_out=$(gpg ${GPG_OPT} --keyring "${SANE_DB}/pubring.gpg" --verify "${F}.sig" "${F}" 2>&1); then
        echo "Security Error ${F} is missing gpg sig" >&2
        echo "${gpg_out}" >&2
        rm -f "${F}.sig" "${F}"
      else
        cp -pf ${F} ${T_DIR}
      fi
    done
fi
}
##****************************************************************************##
# This is the unzip function
function unzip ()
{

# Change file permission's and ownership
for ZF in $(ls *.gz 2>/dev/null); do
  chown ${C_USER}:${C_GROUP} ${ZF}
  chmod 0664 ${ZF}
done

# Get a listing of the *.gz files
ZIPFILES=( $(ls *.gz 2>/dev/null) )

# Set variables
LIMIT=0
ZIPFILES_COUNT=${#ZIPFILES[*]}

while [ ${LIMIT} -lt ${ZIPFILES_COUNT} ]; do
# Get the files name and strip the '.gz' extension
  NoGZ=${ZIPFILES[$LIMIT]/%.gz/}
# Save the original gunzip file and its unzipped version
  gunzip -qfN <${ZIPFILES[$LIMIT]}> ${NoGZ}
# Capture the exit code
  GZEC=$?
    case ${GZEC} in
      [0]*)
# No problems.
      :
      ;;
      [1]*)
# Gunzip has a problem.
# Check the error code and try again!
      echo "gunzip issued error code $GZEC"
      echo "Unable to gunzip ${ZIPFILES[$LIMIT]}"
      echo "Please try again later"
      ;;
      [2]*)
# Gunzip issued a warning. We will attempt to continue.
      echo "gunzip issued warning code $GZEC"
      echo "We will attempt to continue"
      ;;
    esac
  LIMIT=$((LIMIT+1))
done
}
##****************************************************************************##
# This is the reload function
function reload_db ()
{
# See if any files were installed
if [[ ${INSTALLED} -gt 0 ]]; then
  if [[ "${RELOAD}" -gt 0 ]]; then
# Check if clamd is running
    if [[ -z "${CLAMD_PID}" ]]; then
# CLAM_PID not set
      printf "\tCLAM_PID not set. Unable to restart clamd.\n"
    else
      kill -USR2 ${CLAMD_PID}
        if [[ ${W_SUM} -gt 0 ]]; then
          printf "\tDatabase Reloaded\n"
        fi
    fi
  fi
fi
}
## ***************************************************************************##
function summary ()
{
# See if we are to print out a summary

if [[ ${W_SUM} -gt 0 ]]; then
# If a file has been installed, start here.
  if [[ ${INSTALLED} -gt 0 ]]; then
    printf "\n\tFiles saved to: %s\n\n" ${SIG_DB}
    printf "\tInstalled:   %d\n" ${INSTALLED}
    printf "\tNot Updated: %d\n" ${NO_UPDATE}
    printf "\tFailed:      %d\n\n" ${FAILED}
  else
# If a new file has not been installed, we branch here.
    INSTALLED=0
    printf "\n\tReloading of the database not required.\n\n"
    printf "\tInstalled:   %d\n" ${INSTALLED}
    printf "\tNot Updated: %d\n" ${NO_UPDATE}
    printf "\tFailed:      %d\n\n" ${FAILED}
  fi
fi
}
##***************************************************************************##
function chg_owner ()
{
# Change to the main clamav database
cd ${SIG_DB}
# Insure correct file permissions & ownership
for Y in $(ls *.?db *.db *.ftm 2>/dev/null)
do
  chmod 0664 ${Y}
  chown ${C_USER}:${C_GROUP} ${Y}
done
}
## ************************************************************************** ##
function log ()
{

if [[ MK_LOG -gt 0 ]]; then
  local D1=$(date "+%Y-%m-%d")
  local D2="DATE"
  local F="FAILED"
  local I="INSTALLED"
  local L1='%14s%5s%12s%6s%8s%10s%8s\n'
  local L2='%11s%8s%10s%8s%12s%6s%10s\n'
  local L_FILE="/var/log/scamp.log"
  local SPC='::'
  local T1=$(date "+%H:%M:%S")
  local T2="TIME"
  if [[ ! -s ${L_FILE} ]]; then
    printf "${L2}" ${D2} ${SPC} ${T2} ${SPC} ${I} ${SPC} ${F} > ${L_FILE}
    printf -vch "%70s" ""
    printf "%s\n" "${ch// /=}" >> ${L_FILE}
    printf "${L1}" ${D1} ${SPC} ${T1} ${SPC} ${INSTALLED} ${SPC} ${FAILED} >> ${L_FILE}
  else
    printf "${L1}" ${D1} ${SPC} ${T1} ${SPC} ${INSTALLED} ${SPC} ${FAILED} >> ${L_FILE}
  fi
chown ${C_USER}:${C_GROUP} ${L_FILE}
chmod 0644 ${L_FILE}
fi
}
## ***************************************************************************##
##                           Start Program                                    ##
get_files
check_install
chg_owner
summary
reload_db
log
exit 0
# Good luck! Read the "README.txt" file for the script documentation!
