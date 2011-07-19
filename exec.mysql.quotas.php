<?PHP

/*
 * MySQL quota script
 * written by Sebastian Marsching
 *
 */

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/*
 * Create table for quota data with the following statement:
 *
 * CREATE TABLE `Quota` (`Db` CHAR(64) NOT NULL, 
 * `Limit` BIGINT NOT NULL,
 * `Exceeded` ENUM('Y','N') DEFAULT 'N' NOT NULL,
 * PRIMARY KEY (`Db`), UNIQUE (`Db`));
 *
 * The field 'db' stores the information for which database
 * you want to limit the size.
 * The field 'limit' is the size limit in bytes.
 * The field 'exceeded' is only used internally and must be
 * initialized with 'N'.
 */
 
/*
 * Settings
 */
 
$mysql_host  = 'localhost';
$mysql_user  = 'root'; // Do NOT change, root-access is required
$mysql_pass  = '';
$mysql_db    = 'quotadb'; // Not the DB to check, but the db with the quota table
$mysql_table = 'quota';

/*
 * Do NOT change anything below
 */
 
$debug = 0;

// Connect to MySQL Server

if (!mysql_connect($mysql_host, $mysql_user, $mysql_pass))
{
 echo "Connection to MySQL-server failed!";
 exit;
}

// Select database

if (!mysql_select_db($mysql_db))
{
 echo "Selection of database $mysql_db failed!";
 exit;
}

// Check quota for each entry in quota table

$sql = "SELECT * FROM $mysql_table;";
$result = mysql_query($sql);

while ($row = mysql_fetch_array($result))
{
 $quota_db = $row['db'];
 $quota_limit = $row['limit'];
 $quota_exceeded = ($row['exceeded']=='Y') ? 1 : 0;
 
 if ($debug)
  echo "Checking quota for '$quota_db'...\n";
 
 $qsql = "SHOW TABLE STATUS FROM $quota_db;";
 $qresult = mysql_query($qsql);
 
 if ($debug)
  echo "SQL-query is \"$qsql\"\n";
 
 $quota_size = 0;
 
 while ($qrow = mysql_fetch_array($qresult))
 {
  if ($debug)
  { echo "Result of query:\n"; var_dump($qrow); }
  $quota_size += $qrow['Data_length'] + $qrow['Index_length'];
 }
 
 if ($debug)
  echo "Size is $quota_size bytes, limit is $quota_limit bytes\n";
 
 if ($debug && $quota_exceeded)
  echo "Quota is marked as exceeded.\n";
 if ($debug && !$quota_exceeded)
  echo "Quota is not marked as exceeded.\n";
 
 if (($quota_size > $quota_limit) && !$quota_exceeded)
 {
  if ($debug)
   echo "Locking database...\n";
  // Save in quota table  
  $usql = "UPDATE $mysql_table SET exceeded='Y' WHERE db='$quota_db';";
  mysql_query($usql);
  if ($debug)
   echo "Querying: $usql\n";
  // Dismiss CREATE and INSERT privilege for database
  mysql_select_db('mysql');
  $usql = "UPDATE db SET Insert_priv='N', Create_priv='N' WHERE Db='$quota_db';";
  mysql_query($usql);
  if ($debug)
   echo "Querying: $usql\n";
  mysql_select_db($mysql_db);
 }
 
 if (($quota_size <= $quota_limit) && $quota_exceeded) 
 {
  if ($debug)
   echo "Unlocking database...\n";
  // Save in quota table
  $usql = "UPDATE $mysql_table SET exceeded='N' WHERE db='$quota_db';";
  mysql_query($usql);
  if ($debug)
   echo "Querying: $usql\n";
  // Grant CREATE and INSERT privilege for database
  mysql_select_db('mysql');
  $usql = "UPDATE db SET Insert_priv='Y', Create_priv='Y' WHERE Db='$quota_db';";
  mysql_query($usql);
  if ($debug)
   echo "Querying: $usql\n";
  mysql_select_db($mysql_db);
 }
}

?>