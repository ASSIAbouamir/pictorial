<?php

//-- https://stackoverflow.com/a/42854354/54775
class MyFilters {
    static public function lower($str) {
		return strtolower( $str );
	}
    static public function upper($str) {
		return strtoupper( $str );
	}
}

class App {
    public $baseSideBarLinks;
    public $db;
    public $p_f3;
    public $allOrgs;
    public $isHome;
    public function __construct() {
        $this->baseSideBarLinks = [
            // [ '/last10', 'Last 10 Applied' ],
            // [ '/upcoming-interviews', 'Upcoming Interviews' ]
        ];

        $this->db = new Db( "pgsql" );
        // $dbConn = $this->db->db_connect( "10.1.18.25", "dats", "br549", "dats_dev_sep_2018" );
        $dbConn = $this->db->db_connect(
            getenv("PICTORIAL_HOST"),
            getenv("PICTORIAL_UID"),
            getenv("PICTORIAL_PWD"),
            getenv("PICTORIAL_DB")
        );

        $this->p_f3 = \Base::instance();
        $this->isHome = false;
        if ( $this->p_f3->get('PATH')=='/' ) {
            $this->isHome = true;
        }
        // print '<pre>';
        // print_r( $this->p_f3 );
        // print '</pre>';
        // exit;

        $this->allOrgs = Employee::allOrgs();

        $this->setSideBarLinks();
    }//--end of __construct

    public function setSideBarLinks() {
        $this->p_f3->set('sideBarLinks',$this->baseSideBarLinks);
    }//--end of setSideBarLinks

    public function renderLayoutTemplate( $viewFile ) {
        $this->p_f3->set( 'view'   , $viewFile );
        $this->p_f3->set( 'allOrgs', $this->allOrgs );
        $this->p_f3->set( 'isHome' , $this->isHome );
        $this->p_f3->set( 'isDev'  , $this->p_f3->get('isDev') );

        //-- https://stackoverflow.com/a/42854354/54775
        $tpl = \Template::instance();
        $tpl->filter( "lower", "MyFilters::lower" );
        $tpl->filter( "upper", "MyFilters::upper" );

        echo $tpl->render('layout.html','text/html');
    }//--end of renderLayoutTemplate


    public function oneRow( $sql ) {
        $res = $this->db->db_query( $sql );
        if ( ! $res ) {
            return null;
        }
        return $this->db->db_fetch_object( $res, 0 );
    }//--end of oneRow
    public function allRows( $sql ) {
        $results = array();
        $res = $this->db->db_query( $sql );
        if ( ! $res ) {
            return null;
        }
        $num = $this->db->db_num_rows( $res );
        for ($i=0; $i < $num; $i++) {
            $results[] = $this->db->db_fetch_object( $res, $i );
        }
        return $results;
    }//--end of allRows

}//--end of class App