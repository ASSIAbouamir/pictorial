<?php

require_once( "common.php" );

$p = $_POST;

$db->db_query( "BEGIN" );


if ( isset( $p['reject-these'] ) && $p['reject-these']=='1' ) {
    foreach ( $p['emp_no'] as $empno ) {
        $updateSql = "
            update pics_import
                set
                    rejected = true
              where emp_no = '{$empno}'
        ";
        $updateRes = $db->db_query( $updateSql );
        if ( ! $updateRes ) {
            $db->db_query( "ROLLBACK" );
            die( $updateSql );
        }
    }
    $db->db_query( "COMMIT" );
    // print "all rejected, go to <a href='imports.php'>import page</a>";
    header("location: imports.php");
    exit;
}



if ( isset( $p['emp_no'] ) && is_array( $p['emp_no'] ) ) {
    foreach ( $p['emp_no'] as $empno ) {
        $thImgSql = "select th_img from pics_import where ltrim( emp_no, '0' ) = ltrim( '{$empno}', '0' )";
        $thImgRes = $db->db_query( $thImgSql );
        $thImg    = $db->db_result( $thImgRes, 0, 0 );

        $checkSql = "select emp_no from pics where ltrim( emp_no, '0' ) = ltrim( '{$empno}', '0' )";
        $checkRes = $db->db_query( $checkSql );
        $checkNum = $db->db_num_rows( $checkRes );
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
        $dmlRes = $db->db_query( $dmlSql );
        if ( ! $dmlRes ) {
            $db->db_query( "ROLLBACK" );
            die( $dmlSql );
        }
    }
}

$eString = implode( "','", $p['emp_no'] );
$piUpdateSql = "
    update pics_import
        set
            is_active = true
    where emp_no in ( '{$eString}' )
";
$piUpdateRes = $db->db_query( $piUpdateSql );
if ( ! $piUpdateRes ) {
    $db->db_query( "ROLLBACK" );
    die( $piUpdateSql );
}

$db->db_query( "COMMIT" );

// print "all done, go to <a href='imports.php'>import page</a>";
header("location: imports.php");
exit;