<?php

require_once('vendor/autoload.php');

Falsum\Run::handler(true);

$f3 = Base::instance();

if ( $_SERVER["SERVER_NAME"]=="web-dev1.in.dynetics.com" || strstr(getcwd(),"web-dev1.in.dynetics.com")!=FALSE ) {

    $dotenv = new Dotenv\Dotenv(__DIR__, '.dev-env');

    $f3->config('App/Config/dev-setup.cfg');
    $f3->config('App/Config/dev-routes.cfg');

} else if ( $_SERVER["SERVER_NAME"]=="ea.in.dynetics.com" || strstr(getcwd(),"ea.in.dynetics.com")!=FALSE ) {

    $dotenv = new Dotenv\Dotenv(__DIR__);

    $f3->config('App/Config/setup.cfg');
    $f3->config('App/Config/routes.cfg');

}
$dotenv->load();



$f3->set( 'ONERROR', 'Homepage->error' );

$f3->set( 'rUser', str_replace("@in.dynetics.com","",strtolower($_SERVER["REMOTE_USER"])) );


// $cron = Cron::instance();
// $cron->log = TRUE;
// $cron->set( 'Job1', 'MyCron->clearCache', '* * * * *' );
// $cron->set('Job2','App->job2','@weekly');


$f3->run();