<?php

define( "PICTORIAL_UID" , "uid" );
define( "PICTORIAL_PWD" , "pwd" );

if (
    $_SERVER["SERVER_NAME"]=="internal-dev1"
    || strstr(getcwd(),"internal-dev1")!=FALSE
    || $_SERVER["SERVER_NAME"]=="internal-dev2"
    || strstr(getcwd(),"internal-dev2")!=FALSE
   ) {

    define( "HTTP_SERVER"  , "http://{$_SERVER["SERVER_NAME"]}" );

    define( "PICTORIAL_HOST", "internal-dev" );
    define( "PICTORIAL_DB"  , "tempdb" );

    $debug = true;
    define( "DEBUG", true );
    define( "INCLUDES"    , "/home/uid/DEV_PhpIncludes/" );

} else if ( $_SERVER["SERVER_NAME"]=="internal-prod" || $_SERVER["SERVER_NAME"]=="internal-prod" ) {

    define( "HTTP_SERVER"  , "http://{$_SERVER["SERVER_NAME"]}" );

    define( "PICTORIAL_HOST", "internal-prod" );
    define( "PICTORIAL_DB"  , "realdb" );

    define( "INCLUDES"    , "/home/uid/PhpIncludes/" );
    define( "DEBUG", false );
}


require_once( "/home/uid/PhpIncludes/phpabstract.lib.php");
$db   = new Db("pgsql");
$conn = $db->db_connect( PICTORIAL_HOST, PICTORIAL_UID, PICTORIAL_PWD, PICTORIAL_DB);

$rUser = str_replace("@internal.domain.com","",strtolower($_SERVER["REMOTE_USER"]));
$allowed = array(
    'uid1',
    'uid2',
    'uid3'
);
