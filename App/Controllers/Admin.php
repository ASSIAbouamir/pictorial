<?php

class Admin extends App {
    public $db;
    public $sideBarLinks;

    function __construct() {
        // call Grandpa's constructor
        parent::__construct();

        $this->sideBarLinks = [
            //-- <a href="javascript:void(0)" id='check-all'>Check All</a>
            //-- <a href="javascript:void(0)" id='save-imports'>Save Imports</a>
            //-- <a href="javascript:void(0)" id='reject-imports'>Reject Selected</a>
            [ 'javascript:void(0)', 'check-all', 'Check All' ],
            [ 'javascript:void(0)', 'save-imports', 'Save Imports' ],
            [ 'javascript:void(0)', 'reject-imports', 'Reject Selected' ]
        ];

        $this->db = new Db( "pgsql" );
        $dbConn = $this->db->db_connect(
            getenv("PICTORIAL_HOST"),
            getenv("PICTORIAL_UID"),
            getenv("PICTORIAL_PWD"),
            getenv("PICTORIAL_DB")
        );
    }

    function beforeRoute( $f3, $params, $routeMethod ) {
        if ( $routeMethod=='Admin->newpics' ) {
            //-- check if allowed
            if ( ! in_array( $f3->get('rUser'), array( 'perry', 'baskin', 'hughesm' ) ) ) {
                $f3->reroute("/");
            }
        }
    }//--end of beforeRoute

    function newpics(  ) {
        // print '<pre>';
        // print_r( $this );
        // print '</pre>';
        // exit;

        $sql = "
            select a.fullname, a.location,
                CASE WHEN a.fullname is null THEN '0'
                    ELSE '1'
                END as is_active_in_ad_table,
                pi.emp_no, pi.mtime, pi.is_active, pi.th_img, pi.full_img,
                pi.width, pi.height,
                CASE WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN true
                        ELSE false
                END as wrong_size,
                CASE WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN ' wrong-size'
                        ELSE ''
                END as wrong_size_class
            from pics_import pi
                LEFT JOIN ad a
                    ON a.emp_no = pi.emp_no
            where pi.is_active = false
                and ( pi.rejected = false or pi.rejected is null )
                and a.fullname is not null

            UNION

            select a.fullname, a.location,
                CASE WHEN a.fullname is null THEN '0'
                    ELSE '1'
                END as is_active_in_ad_table,
                pi.emp_no, pi.mtime, pi.is_active, pi.th_img, pi.full_img,
                pi.width, pi.height,
                CASE WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN true
                        ELSE false
                END as wrong_size,
                CASE WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN ' wrong-size'
                        ELSE ''
                END as wrong_size_class
            from pics_import pi
                LEFT JOIN other_ad a
                    ON a.emp_no = pi.emp_no
            where pi.is_active = false
                and ( pi.rejected = false or pi.rejected is null )
                and a.fullname is not null
            order by emp_no
        ";
        $res = $this->db->db_query( $sql );
        $num = $this->db->db_num_rows( $res );
        $rows = [];
        for ($i=0; $i < $num; $i++) {
            $row = $this->db->db_fetch_object( $res, $i );
            $caption = $row->emp_no;
            if ( strlen( $row->fullname )>0 ) {
                $caption = "{$row->fullname} <p><small><a href='http://ea.in.dynetics.com/pic/d/e/{$row->emp_no}' target='_blank'>{$row->emp_no}</a></small></p>";
            }
            if ( strlen( $row->location )>0 ) {
                $caption .= "<p><small>{$row->location}</small></p>";
            }
            $size = "<small>({$row->width}x{$row->height})</small>";
            if ( strlen($row->width)==0 && strlen($row->height)==0 ) {
                $size = "";
            }

            $row->caption = $caption;
            $row->size    = $size;
            $rows[] = $row;
        }
        $this->p_f3->set( "data", $rows );
        $this->p_f3->set( "sideBarLinks", $this->sideBarLinks );
        $this->renderLayoutTemplate( "newpics.html" );
    }//--end of newpics

    function nopics(  ) {

        $sql = "
            select q.emp_no, q.last_name, q.first_name, q.company
              from (
                    select first_name, last_name, emp_no, company
                        from ad
                    where emp_no not in (select emp_no from pics)
                    UNION
                    select first_name, last_name, emp_no, company
                            from other_ad
                    where emp_no not in (select emp_no from pics)
                    ) as q
            order by company desc, emp_no;
        ";
        $res = $this->db->db_query( $sql );
        $num = $this->db->db_num_rows( $res );
        $rows = [];
        for ($i=0; $i < $num; $i++) {
            $rows[] = $this->db->db_fetch_object( $res, $i );
        }
        $this->p_f3->set( "data", $rows );
        $this->p_f3->set( "sideBarLinks", $this->sideBarLinks );
        $this->renderLayoutTemplate( "nopics.html" );
    }//--end of nopics

    function newpicsSave() {
        // print '<pre>';
        // print_r( $_POST );
        // print '</pre>';
        // exit;
        $p = $_POST;
        $this->db->db_query( "BEGIN" );
        if ( isset( $p['reject-these'] ) && $p['reject-these']=='1' ) {
            foreach ( $p['emp_no'] as $empno ) {
                $updateSql = "
                    update pics_import
                        set
                            rejected = true
                      where emp_no = '{$empno}'
                ";
                // print '<pre>';
                // print_r( $updateSql );
                // print '</pre>';
                // exit;
                $updateRes = $this->db->db_query( $updateSql );
                if ( ! $updateRes ) {
                    $this->db->db_query( "ROLLBACK" );
                    die( $updateSql );
                }
            }
        } else if ( isset( $p['emp_no'] ) && is_array( $p['emp_no'] ) ) {
            foreach ( $p['emp_no'] as $empno ) {
                $thImgSql = "select th_img from pics_import where ltrim( emp_no, '0' ) = ltrim( '{$empno}', '0' )";
                $thImgRes = $this->db->db_query( $thImgSql );
                $thImg    = $this->db->db_result( $thImgRes, 0, 0 );

                $checkSql = "select emp_no from pics where ltrim( emp_no, '0' ) = ltrim( '{$empno}', '0' )";
                $checkRes = $this->db->db_query( $checkSql );
                $checkNum = $this->db->db_num_rows( $checkRes );
                $dmlSql = "";
                if ( $checkNum==1 ) {
                    $dmlSql = "
                        update pics theone
                            set
                                image = i.th_img
                           from pics_import i
                          where ltrim( theone.emp_no, '0' ) = ltrim( i.emp_no, '0' )
                            and ltrim( i.emp_no, '0' ) = ltrim( '{$empno}', '0' )
                    ";
                } else {
                    $dmlSql = "
                        insert into pics
                        (
                            emp_no, image
                        )
                        values
                        (
                            ltrim( '{$empno}', '0' ), '{$thImg}'
                        )
                    ";
                }
                // print '<pre>';
                // print_r( $dmlSql );
                // print '</pre>';
                // exit;
                $dmlRes = $this->db->db_query( $dmlSql );
                if ( ! $dmlRes ) {
                    $this->db->db_query( "ROLLBACK" );
                    die( $dmlSql );
                }
            }
            $eString = implode( "','", $p['emp_no'] );
            $piUpdateSql = "
                update pics_import
                    set
                        is_active = true
                where emp_no in ( '{$eString}' )
            ";
            $piUpdateRes = $this->db->db_query( $piUpdateSql );
            if ( ! $piUpdateRes ) {
                $this->db->db_query( "ROLLBACK" );
                die( $piUpdateSql );
            }

        }
        $this->db->db_query( "COMMIT" );
        $this->p_f3->reroute("/newpics");
    }//--end of newpicsSave

}//--end of class Admin