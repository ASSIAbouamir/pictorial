<?php

class Employee extends App {
    function __construct() {
        // call Grandpa's constructor
        parent::__construct();
    }

    // function beforeRoute( $f3, $params, $routeMethod ) {
    //     if ( $routeMethod=='Employee->import' ) {
    //         //-- check if allowed
    //     }
    // }//--end of beforeRoute

    function info(  ) {
        $empno = $this->p_f3->get("PARAMS.empno");
        $empl = json_decode(
            Util::CallAPI(
                "GET",
                "employees/{$empno}?test=1",
                false,
                $this->p_f3->get("isDev")
            )
        );

        if ( ! is_object( $empl ) ) {
            $this->p_f3->reroute("/");
        }

        $reports = json_decode(
            Util::CallAPI(
                "GET",
                "employees/{$empno}/reports?test=1",
                false,
                $this->p_f3->get("isDev")
            )
        );

        $empl->image = $this->img( $empl );
        foreach ($reports as $report) {
            $report->image = $this->img( $report );
        }

        $caption = "{$empl->lastname}, {$empl->firstname}";
        if ( strlen( $empl->location )>0 ) {
            $caption .= "<p><small>{$empl->location}</small></p>";
        }

        if ( isset( $empl->officephone ) ) {
            $empl->officephone = $this->scrubPhone( $empl->officephone );
        }

        $empl->caption    = $caption;
        if ( strlen( $empl->managerlastname )>0 && strlen( $empl->managerfirstname )>0 ) {
            $empl->mgr = "{$empl->managerlastname}, {$empl->managerfirstname}";
        } else {
            $empl->mgr = "";
        }
        $empl->hasReports = count( $reports ) > 0 ? true: false;
        $empl->thumbs     = $reports;
        $empl->thumbClass = "flex-item-with-employee";
        $this->p_f3->set( "data", $empl );
        $this->renderLayoutTemplate( "e3.html" );
    }//--end of function info

    function byOrg( ) {
        $orgid = $this->p_f3->get("PARAMS.orgid");
        $empls = json_decode(
            Util::CallAPI(
                "GET",
                "employees/org/{$orgid}?test=1",
                false,
                $this->p_f3->get("isDev")
            )
        );
        if ( is_array( $empls ) && count( $empls )==0 ) {
            $this->p_f3->reroute("/");
        }
        foreach ($empls as $empl) {
            $empl->image = $this->img( $empl );
        }

        $data             = new stdClass;
        $data->orgid      = $orgid;
        $data->thumbs     = $empls;
        $data->thumbClass = "flex-item-all-thumbs";
        $this->p_f3->set( "data", $data );
        $this->renderLayoutTemplate( "by-org.html" );
    }//--end of byOrg

    function search( ) {
        $searchString = $this->p_f3->get("PARAMS.search");
        // print '<pre>';
        // print_r( $searchString );
        // print '</pre>';
        // exit;
        $empls = json_decode(
            Util::CallAPI(
                "GET",
                "employees/search/{$searchString}?test=1",
                false,
                $this->p_f3->get("isDev")
            )
        );
        // print '<pre>';
        // print_r( $empls );
        // print '</pre>';
        // exit;
        foreach ($empls as $empl) {
            $empl->image = $this->img( $empl );
        }
        header('Content-Type: application/json');
        print json_encode( $empls );
    }//--end of search

    public static function allOrgs( ) {
        $orgs = json_decode(
            Util::CallAPI(
                "GET",
                "orgs?test=1"
            )
        );
        if ( !isset( $_SESSION['ALL-HOME-ORGS'] ) ) {
            $_SESSION['ALL-HOME-ORGS'] = $orgs;
        }
        return $_SESSION['ALL-HOME-ORGS'];
    }//--end of allOrgs

    public static function img( $emplObj ) {
        $imgString = "";
        if ( isset( $emplObj->image ) && strlen( $emplObj->image )>0 ) {
            $imgString = "data:image/jpeg;base64," . $emplObj->image;
        } else {
            $imgString = "data:image/gif;base64,R0lGODlhcABUAIABAP///////yH5BAUKAAEALAAAAABwAFQAAAJvjI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNf2jef6zvf+DwwKh8Si8YhMKpfMpvMJjUqn1Kr1is1qt9yu9wsOi8fksvmMTqvX7Lb7DY/L5/S6/Y7P6/f8vv8PGCg4SFhoeIiYqLjICFYAADs=";
        }
        return $imgString;
    }//--end of img

    public static function scrubPhone( $phone ) {
        $phone = trim( $phone );
        if ( $phone=='UNASSIGNED' ) {
            $phone = "";
        }
        if ( strlen( $phone )==10 ) {
            $areaCode   = substr( $phone, 0, 3 );
            $firstThree = substr( $phone, 4, 3 );
            $lastFour   = substr( $phone, 6, 4 );
            $phone      = sprintf( "%s-%s-%s", $areaCode, $firstThree, $lastFour );
        } else if ( strlen( $phone )>0 && strlen( $phone )!=10 ) {
            $phone = str_replace( " ", "-", $phone );
        }
        return $phone;
    }//--end of scrubPhone

}//--end of Employee class