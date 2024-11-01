<?php
/** 
 * Database tables information for past and new versions.
 * 
 * This file is not fully in use yet. The intention is to migrate it to the
 * installation class and rather than an array I will simply store every version
 * of each tables query. Each query can be broken down to compare against existing 
 * tables. I find this array approach too hard to maintain over many plugins.
 * 
 * @todo move this to installation class but also reduce the array to actual queries per version
 * 
 * @package WTG Portal Manager
 * @author Ryan Bayne   
 * @since 0.0.1
 * @version 8.1.2
 */

// load in WordPress only
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
 
 
/*   Column Array Example Returned From "mysql_query( "SHOW COLUMNS FROM..."
        
          array(6) {
            [0]=>
            string(5) "row_id"
            [1]=>
            string(7) "int(11)"
            [2]=>
            string(2) "NO"
            [3]=>
            string(3) "PRI"
            [4]=>
            NULL
            [5]=>
            string(14) "auto_increment"
          }
                  
    +------------+----------+------+-----+---------+----------------+
    | Field      | Type     | Null | Key | Default | Extra          |
    +------------+----------+------+-----+---------+----------------+
    | Id         | int(11)  | NO   | PRI | NULL    | auto_increment |
    | Name       | char(35) | NO   |     |         |                |
    | Country    | char(3)  | NO   | UNI |         |                |
    | District   | char(20) | YES  | MUL |         |                |
    | Population | int(11)  | NO   |     | 0       |                |
    +------------+----------+------+-----+---------+----------------+            
*/
   
global $wpdb;   
$wtgportalmanager_tables_array =  array();
##################################################################################
#                                 webtechglobal_log                                         #
##################################################################################        
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['name'] = $wpdb->prefix . 'webtechglobal_log';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['required'] = false;// required for all installations or not (boolean)
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['pluginversion'] = '0.0.1';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['usercreated'] = false;// if the table is created as a result of user actions rather than core installation put true
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['version'] = '0.0.1';// used to force updates based on version alone rather than individual differences
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['primarykey'] = 'row_id';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['uniquekey'] = 'row_id';
// webtechglobal_log - row_id
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['row_id']['type'] = 'bigint(20)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['row_id']['null'] = 'NOT NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['row_id']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['row_id']['default'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['row_id']['extra'] = 'AUTO_INCREMENT';
// webtechglobal_log - outcome
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['outcome']['type'] = 'tinyint(1)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['outcome']['null'] = 'NOT NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['outcome']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['outcome']['default'] = '1';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['outcome']['extra'] = '';
// webtechglobal_log - timestamp
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['timestamp']['type'] = 'timestamp';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['timestamp']['null'] = 'NOT NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['timestamp']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['timestamp']['default'] = 'CURRENT_TIMESTAMP';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['timestamp']['extra'] = '';
// webtechglobal_log - line
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['line']['type'] = 'int(11)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['line']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['line']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['line']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['line']['extra'] = '';
// webtechglobal_log - file
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['file']['type'] = 'varchar(250)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['file']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['file']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['file']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['file']['extra'] = '';
// webtechglobal_log - function
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['function']['type'] = 'varchar(250)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['function']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['function']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['function']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['function']['extra'] = '';
// webtechglobal_log - sqlresult
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlresult']['type'] = 'blob';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlresult']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlresult']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlresult']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlresult']['extra'] = '';
// webtechglobal_log - sqlquery
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlquery']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlquery']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlquery']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlquery']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlquery']['extra'] = '';
// webtechglobal_log - sqlerror
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlerror']['type'] = 'mediumtext';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlerror']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlerror']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlerror']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['sqlerror']['extra'] = '';
// webtechglobal_log - wordpresserror
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['wordpresserror']['type'] = 'mediumtext';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['wordpresserror']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['wordpresserror']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['wordpresserror']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['wordpresserror']['extra'] = '';
// webtechglobal_log - screenshoturl
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['screenshoturl']['type'] = 'varchar(500)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['screenshoturl']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['screenshoturl']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['screenshoturl']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['screenshoturl']['extra'] = '';
// webtechglobal_log - userscomment
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userscomment']['type'] = 'mediumtext';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userscomment']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userscomment']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userscomment']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userscomment']['extra'] = '';
// webtechglobal_log - page
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['page']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['page']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['page']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['page']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['page']['extra'] = '';
// webtechglobal_log - version
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['version']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['version']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['version']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['version']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['version']['extra'] = '';
// webtechglobal_log - panelid
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelid']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelid']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelid']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelid']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelid']['extra'] = '';
// webtechglobal_log - panelname
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelname']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelname']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelname']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelname']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['panelname']['extra'] = '';
// webtechglobal_log - tabscreenid
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenid']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenid']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenid']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenid']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenid']['extra'] = '';
// webtechglobal_log - tabscreenname
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenname']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenname']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenname']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenname']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['tabscreenname']['extra'] = '';
// webtechglobal_log - dump
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['dump']['type'] = 'longblob';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['dump']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['dump']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['dump']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['dump']['extra'] = '';
// webtechglobal_log - ipaddress
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['ipaddress']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['ipaddress']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['ipaddress']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['ipaddress']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['ipaddress']['extra'] = '';
// webtechglobal_log - userid
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userid']['type'] = 'int(11)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userid']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userid']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userid']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['userid']['extra'] = '';
// webtechglobal_log - comment
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['comment']['type'] = 'mediumtext';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['comment']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['comment']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['comment']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['comment']['extra'] = '';
// webtechglobal_log - type
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['type']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['type']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['type']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['type']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['type']['extra'] = '';
// webtechglobal_log - category
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['category']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['category']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['category']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['category']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['category']['extra'] = '';
// webtechglobal_log - action
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['action']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['action']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['action']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['action']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['action']['extra'] = '';
// webtechglobal_log - priority
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['priority']['type'] = 'varchar(45)';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['priority']['null'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['priority']['key'] = '';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['priority']['default'] = 'NULL';
$wtgportalmanager_tables_array['tables']['webtechglobal_log']['columns']['priority']['extra'] = '';              
?>