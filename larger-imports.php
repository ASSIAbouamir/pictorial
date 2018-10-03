<?php

require_once( "common.php" );

if ( ! in_array( $rUser, $allowed ) ) {
    header( "location: /pic/d/" );
    exit;
}


$standardWhere = "
    pi.ready_for_ad_table = true
   and ( pi.processed = false or pi.processed is null )
   and a.fullname is not null
";

$rejectedWhere = " and ( pi.rejected = false or pi.rejected is null ) ";
if ( isset( $_GET['r'] ) ) {
    $rejectedWhere = " and pi.rejected = true ";
}
$picNotFoundWhere = " and ( pi.pic_not_found = false or pi.pic_not_found is null ) ";
if ( isset( $_GET['nf'] ) ) {
    $picNotFoundWhere = " and pi.pic_not_found = true ";
    $standardWhere = str_replace( "true", "false", $standardWhere );
}


$sql = "
    select a.emp_no as empnoint,
           a.fullname, a.location,
           CASE WHEN a.fullname is null THEN '0'
               ELSE '1'
           END as is_active_in_ad_table,
           pi.emp_no, pi.mtime, pi.th_img, pi.full_img,
           pi.width, pi.height,
           CASE -- WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN true
                WHEN pi.width = 1920 and pi.height = 1080 THEN true
                WHEN pi.width >=3280 THEN true

                WHEN pi.height > pi.width THEN true
                ELSE false
           END as wrong_size
      from larger_pics_import pi
           LEFT JOIN ad a
               ON a.emp_no = pi.emp_no
      where
        {$standardWhere}
        {$rejectedWhere}
        {$picNotFoundWhere}

    UNION

    select a.emp_no as empnoint,
           a.fullname, a.location,
           CASE WHEN a.fullname is null THEN '0'
               ELSE '1'
           END as is_active_in_ad_table,
           pi.emp_no, pi.mtime, pi.th_img, pi.full_img,
           pi.width, pi.height,
           CASE -- WHEN pi.width * pi.height > 480000 or pi.height > pi.width THEN true
                WHEN pi.width = 1920 and pi.height = 1080 THEN true
                ELSE false
           END as wrong_size
      from larger_pics_import pi
           LEFT JOIN other_ad a
               ON a.emp_no = pi.emp_no
      where
        {$standardWhere}
        {$rejectedWhere}
        {$picNotFoundWhere}
      order by 1
";
// print '<pre>';
// print_r( $sql );
// print '</pre>';
// exit;
$res = $db->db_query( $sql );
$num = $db->db_num_rows( $res );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Expires" content="Wed, 22 Aug 1990 13:56:48 GMT" />
    <meta http-equiv="Cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="nocache" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pictorial Larger Importer</title>

    <link rel="stylesheet" type="text/css" href="/esg/v2/assets/css/esg.v2.css" />

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <style>
        /* https://www.w3schools.com/howto/howto_css_cards.asp */
        .card {
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            /* width: 25%; */
        }
        .card:hover {
            box-shadow: 0 8px 7px 0 rgba(0,0,0,0.2);
        }
        .thumbnail-container>img {
            margin-left: auto;
            margin-right: auto;
            display: block;
            margin-top: 7px;
            border-radius: 10px;
            outline: none;
            border: 0;
        }
        .thumbnail-container {
            width: 100%;
        }

        .parent-card-container {
            width: 230px;
        }
        .desc-container {
            padding: 0px 15px;
            max-width: 230px;
        }
        .wrong-size {
            background-color: yellow;
        }
        .caption>p {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }
        h4 {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }
        label {
            display: block !important;
        }
    </style>
</head>
<body>


    <aside class="sidebar sidebar-explorer">
        <div class="sidebar-wrapper">
            <div class="sidebar-logo">
                <div class="sidebar-logo-wrapper">
                    <img src="/esg/v2/assets/img/dynetics-logo-white.svg" class="w-100" alt="">
                </div>
            </div>
            <div class="sidebar-links-wrapper">
                <div class="site-title">
                    <div class="site-title-wrapper">
                        <a href="larger-imports.php">Pictorial LARGER Importer</a>
                    </div>
                </div>
                <div class="site-links">
                    <ul class="site-links-list">
                        <li>
                            <a href="javascript:void(0)" id='check-all'>Check All</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" id='uncheck-all'>UN-Check All</a>
                        </li>
                        <li>
                            <br>&nbsp;
                        </li>
                        <li>
                            <a href="javascript:void(0)" id='save-imports'>Save Imports</a>
                        </li>
                        <li>
                            <br>&nbsp;
                        </li>
                        <li>
                            <a href="javascript:void(0)" id='reject-imports'>Reject Selected</a>
                        </li>
                        <li>
                            <br>&nbsp;
                        </li>
                        <li>
                            <?php if ( ! isset($_GET['r']) )  { ?>
                                <a href="larger-imports.php?r=1">Show Rejected</a>
                            <?php } ?>
                        </li>
                        <li>
                            <?php if ( ! isset($_GET['nf']) )  { ?>
                                <a href="larger-imports.php?nf=1">Show Pic Not Found</a>
                            <?php } ?>
                        </li>
                        <?php if ( isset( $_GET['r']) || isset( $_GET['nf']) ) { ?>
                            <li>
                                <a href="larger-imports.php">Show All</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </aside>


    <main class="main">
        <div class="container">
            <div class="wrapper">

                <form action="larger-save-imports.php" method="post">

                    <input type="hidden" name="reject-these" id="reject-these" value="0">

                    <h1>
                        Pictorial LARGER Imports
                        <?php if ( isset( $_GET['r'] ) ) { ?>
                            - REJECTED
                        <?php } ?>
                        <?php if ( isset( $_GET['nf'] ) ) { ?>
                            - PIC NOT FOUND
                        <?php } ?>
                    </h1>

                    <div class="cards flex row wrap">
                        <?php
                            $output = "";
                            for ($i=0; $i < $num; $i++) {
                                $row = $db->db_fetch_object( $res, $i );
                                $caption = $row->emp_no;
                                if ( strlen( $row->fullname )>0 ) {
                                    $caption = "{$row->fullname} <p><small><a href='http://et.in.dynetics.com/pic/d/#employees/{$row->emp_no}' target='_blank'>{$row->emp_no}</a></small></p>";
                                }
                                if ( strlen( $row->location )>0 ) {
                                    $caption .= "<p><small>{$row->location}</small></p>";
                                }
                                $wrongSizeClass = "";
                                $wrongSizeChecked = "";
                                if ( $row->wrong_size=='t' ) {
                                    $wrongSizeClass = " wrong-size";
                                    $wrongSizeChecked = " checked";
                                }
                                $size = "<small>({$row->width}x{$row->height})</small>";
                                if ( strlen($row->width)==0 && strlen($row->height)==0 ) {
                                    $size = "";
                                }
                                $output .= "
                                    <div class='card{$wrongSizeClass}'>
                                        <div class='parent-card-container'>
                                            <div class='thumbnail-container'>
                                                <img src='data:image/jpeg;base64,{$row->full_img}' width='224' height='168'>
                                            </div>
                                            <div class='desc-container'>
                                                {$size}
                                                <label>
                                                    <h4>
                                                        <input type='checkbox' name='emp_no[]' value='{$row->emp_no}' class='checkbox'{$wrongSizeChecked}>
                                                        <span class='caption'>{$caption}</span>
                                                    </h4>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }
                            print $output;
                        ?>
                    </div>
                </form>
            </div>

        </div>

    </main>

    <script>
        $(document).on("click","#check-all",function(e){
            $(document).find("form input:checkbox").prop("checked",true);
        });
        $(document).on("click","#uncheck-all",function(e){
            $(document).find("form input:checkbox").prop("checked",false);
        });
        $(document).on("click","#save-imports",function(e){
            $(document).find("form").trigger("submit");
        });
        $(document).on("click","#reject-imports",function(e) {
            $("#reject-these").val('1');
            $(document).find("form").trigger("submit");
        });
    </script>
</body>
</html>