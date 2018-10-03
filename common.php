<?php

define( "PICTORIAL_UID" , "dats_temp" );
define( "PICTORIAL_PWD" , "br549" );

if (
    $_SERVER["SERVER_NAME"]=="alpha.in.dynetics.com"
    || strstr(getcwd(),"alpha.in.dynetics.com")!=FALSE
    || $_SERVER["SERVER_NAME"]=="web-dev1.in.dynetics.com"
    || strstr(getcwd(),"web-dev1.in.dynetics.com")!=FALSE
   ) {

    define( "HTTP_SERVER"  , "http://{$_SERVER["SERVER_NAME"]}" );

    define( "PICTORIAL_HOST", "10.1.18.25" );
    define( "PICTORIAL_DB"  , "dats_temp_dev_sep_2018" );

    $debug = true;
    define( "DEBUG", true );
    define( "INCLUDES"    , "/home/perry/DEV_PhpIncludes/" );

} else if ( $_SERVER["SERVER_NAME"]=="et.in.dynetics.com" || $_SERVER["SERVER_NAME"]=="ea.in.dynetics.com" ) {

    define( "HTTP_SERVER"  , "http://{$_SERVER["SERVER_NAME"]}" );

    define( "PICTORIAL_HOST", "aether" );
    define( "PICTORIAL_DB"  , "dats_temp" );

    define( "INCLUDES"    , "/home/perry/PhpIncludes/" );
    define( "DEBUG", false );
}


require_once( "/home/perry/PhpIncludes/phpabstract.lib.php");
$db   = new Db("pgsql");
$conn = $db->db_connect( PICTORIAL_HOST, PICTORIAL_UID, PICTORIAL_PWD, PICTORIAL_DB);

$rUser = str_replace("@in.dynetics.com","",strtolower($_SERVER["REMOTE_USER"]));
$allowed = array(
    'perry',
    'baskin',
    'hughesm'
);