<?php
// namespace App\Utils;

class Db {

    var $DBType;
    var $DbLink;
    var $lastQueryUsed;

    //-- Constructor
    function __construct($dbIn=""){
        $this->DBType = $dbIn;

        // if ( $dbIn=="mssql" ) {
        //     die( "mssql_ functions do not exists on PHP 7+" );
        // }
    }

    //-- TEST: √ db_connect
    function db_connect($argHost, $argUser="", $argPass="", $argDB) {
        switch ($this->DBType) {
            case "odbc":
                $this->DbLink = odbc_connect($argHost, $argUser, $argPass);
                if ( ! $this->DbLink ) {
                    print "<b>SELECT DB FAILED</b>argDB == [{$argDB}]; argHost == [{$argHost}]<BR>\n";
                    $this->DbLink = FALSE;
                }
                break;
            case "sqlsrv":
                $connectionOptions = array(
                    "Database"          => $argDB,
                    "ConnectionPooling" => 0 //-- force a new connection, default is 1 (on)
                );
                //-- if passed as blanks, it could be that it's trying to do Windows Auth
                if ( strlen($argUser)>0 && strlen($argPass)>0 ) {
                    $connectionOptions['Uid'] = $argUser;
                    $connectionOptions['PWD'] = $argPass;
                }
                $this->DbLink = sqlsrv_connect($argHost, $connectionOptions);
                if ( ! $this->DbLink ) {
                    print "<b>SELECT DB FAILED</b>argDB == [$argDB]; argHost == [$argHost]<BR>\n";
                    $this->DbLink = FALSE;
                }
                break;
            case "mssql":
                $this->DbLink=mssql_connect($argHost, $argUser, $argPass);
                if (!@mssql_select_db($argDB, $this->DbLink)) {
                    print "<b>SELECT DB FAILED</b>argDB == [$argDB]; argHost == [$argHost]<BR>\n";
                    $this->DbLink=FALSE;
                }
                break;
            case "pgsql":
                $ConnectCommand = '';
                if ( strpos( $argHost, ':' ) !== false ) {
                    $HostArray = explode( ":", $argHost );
                    if ($HostArray[0]) {
                            $ConnectCommand .= "host=".$HostArray[0];
                    }
                    if ($HostArray[1]) {
                            $ConnectCommand .= " port=".$HostArray[1];
                    }
                } else {
                    $ConnectCommand .= "host=".$argHost;
                }
                if ($argUser) {
                        $ConnectCommand .= " user=".$argUser;
                }
                if ($argPass) {
                        $ConnectCommand .= " password=".$argPass;
                }
                $ConnectCommand .= " dbname=".$argDB;
                $this->DbLink=pg_connect($ConnectCommand);
                break;
            case "mysql":
                $this->DbLink=mysql_connect($argHost, $argUser, $argPass);
                if (!mysql_select_db($argDB, $this->DbLink)) {
                    print "<b>SELECT DB FAILED</b>argDB == [$argDB]; argHost == [$argHost]<BR>\n";
                    $this->DbLink=FALSE;
                }
                break;
            case "msql":
                $this->DbLink=msql_connect($argHost);
                msql_select_db($argDB, $this->DbLink);
                break;
        }
        return $this->DbLink;
    }

    //-- TEST: √ db_close
    function db_close() {
        switch ($this->DBType) {
            case "sqlsrv":
                $Status = sqlsrv_close($this->DbLink);
                break;
            case "mssql":
                $Status=mssql_close($this->DbLink);
                break;
            case "pgsql":
                $Status=pg_close($this->DbLink);
                break;
            case "mysql":
                $Status=mysql_close($this->DbLink);
                break;
            case "msql":
                $Status=msql_close($this->DbLink);
                break;
        }
        return $Status;
    }

    //-- TEST: √ db_query
    function db_query($argQry,$params=array()) {
        switch ($this->DBType) {
            case "odbc":
                $ResultIndex = odbc_exec($this->DbLink, $argQry);
                $this->lastQueryUsed = $argQry;
                break;
            case "sqlsrv":
                $ResultIndex = sqlsrv_query( $this->DbLink,
                                             $argQry,
                                             $params,
                                             array("Scrollable"=>"buffered") );
                break;
            case "mssql":
                $ResultIndex=mssql_query($argQry,$this->DbLink);
                break;
            case "pgsql":
                $argLink     = $this->DbLink;
                if ( count( $params ) > 0  ) {
                    $ResultIndex = @pg_query_params( $argLink, $argQry, $params );
                } else {
                    $ResultIndex = @pg_query( $argLink, $argQry );
                }
                if ( $ResultIndex===FALSE ) {
                    error_log( "[".date('m/d/Y H:i:s')."]\ndb_query ERROR\n" . $argQry . "\n\n",3,"Logs/lance.txt" );
                }
                break;
            case "mysql":
                $ResultIndex=mysql_query($argQry,$this->DbLink);
                break;
            case "msql":
                $ResultIndex=msql_query($argQry,$this->DbLink);
                break;
        }
        return $ResultIndex;
    }

    //-- TEST: √ db_result
    function db_result($resultId, $argRow=0, $argField=0) {
        switch ($this->DBType) {
            case "sqlsrv":
                $row    = sqlsrv_fetch_array( $resultId, SQLSRV_FETCH_BOTH, SQLSRV_SCROLL_ABSOLUTE, $argRow );
                $Result = $row[$argField];
                break;
            case "mssql":
                $Result=mssql_result($resultId, $argRow, $argField);
                break;
            case "pgsql":
                $Result=pg_result($resultId, $argRow, $argField);
                break;
            case "mysql":
                $Result=mysql_result($resultId, $argRow, $argField);
                break;
            case "msql":
                $Result=msql_result($resultId, $argRow, $argField);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_fetch_row
    function db_fetch_row($resultId, $argIndex=0) {
        switch ($this->DBType) {
            case "sqlsrv":
                $Result = sqlsrv_fetch_array( $resultId, SQLSRV_FETCH_NUMERIC, SQLSRV_SCROLL_ABSOLUTE, $argRow );
                break;
            case "mssql":
                $Result=mssql_fetch_row($resultId);
                break;
            case "pgsql":
                $Result=@pg_fetch_row($resultId, $argIndex);
                break;
            case "mysql":
                $Result=mysql_fetch_row($resultId);
                break;
            case "msql":
                $Result=msql_fetch_row($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_fetch_array
    function db_fetch_array($resultId, $argRow=0) {
        switch ($this->DBType) {
            case "sqlsrv":
                $Result = sqlsrv_fetch_array( $resultId, SQLSRV_FETCH_BOTH, SQLSRV_SCROLL_ABSOLUTE, $argRow );
                break;
            case "mssql":
                $Result=mssql_fetch_array($resultId);
                break;
            case "pgsql":
                $Result=pg_fetch_array($resultId, $argRow);
                break;
            case "mysql":
                $Result=mysql_fetch_array($resultId);
                break;
            case "msql":
                $Result=msql_fetch_array($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_fetch_object
    function db_fetch_object($resultId, $argRow=0) {
        switch ($this->DBType) {
            case "sqlsrv":
                $Result = sqlsrv_fetch_object( $resultId, null, null, SQLSRV_SCROLL_ABSOLUTE, $argRow );
                break;
            case "mssql":
                $Result=mssql_fetch_object($resultId);
                break;
            case "pgsql":
                $Result=pg_fetch_object($resultId, $argRow);
                break;
            case "mysql":
                $Result=mysql_fetch_object($resultId);
                break;
            case "msql":
                $Result=msql_fetch_object($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_num_rows
    function db_num_rows($resultId) {
        switch ($this->DBType) {
            case "odbc":
                //-- num_rows returns -1, cannot use this
                // $Result = odbc_num_rows( $resultId );
                break;
            case "sqlsrv":
                $Result = sqlsrv_num_rows( $resultId );
                break;
            case "mssql":
                $Result=mssql_num_rows($resultId);
                break;
            case "pgsql":
                $Result=pg_numrows($resultId);
                break;
            case "mysql":
                $Result=mysql_num_rows($resultId);
                break;
            case "msql":
                $Result=msql_num_rows($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_num_fields
    function db_num_fields($resultId) {
        switch ($this->DBType) {
            case "sqlsrv":
                $Result = sqlsrv_num_fields( $resultId );
                break;
            case "mssql":
                $Result=mssql_num_fields($resultId);
                break;
            case "pgsql":
                $Result=pg_numfields($resultId);
                break;
            case "mysql":
                $Result=mysql_num_fields($resultId);
                break;
            case "msql":
                $Result=msql_num_fields($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_error_message
    function db_error_message($connectId=0){
        switch ($this->DBType) {
            case "sqlsrv":
                $Result = sqlsrv_errors();
                break;
            case "mssql":
                $Result=mssql_get_last_message();
                break;
            case "pgsql":
                $Result=pg_last_error($this->DbLink);
                break;
            case "mysql":
                $Result=mysql_error();
                break;
            case "msql":
                $Result=msql_error();
                break;
        }
        return $Result;
    }

    //-- TEST: √ db_affected_rows
    function db_affected_rows($resultId) {
        switch ($this->DBType) {
            case "sqlsrv":
            case "mssql":
                $query_result = $this->db_query("SELECT @@ROWCOUNT AS RCOUNT");
                $Result       = $this->db_result($query_result,0,"RCOUNT");
                break;
            case "pgsql":
                $Result=@pg_affected_rows($resultId);
                break;
            case "mysql":
                $Result=mysql_affected_rows();
                break;
            case "msql":
                $Result=msql_affected_rows($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: db_free_results
    function db_free_results($resultId) {
        switch ($this->DBType) {
            case "sqlsrv":
                @sqlsrv_free_stmt( $resultId );
                break;
            case "mssql":
                @mssql_free_result($resultId);
                break;
            case "pgsql":
                @pg_freeresult($resultId);
                break;
            case "mysql":
                @mysql_free_result($resultId);
                break;
            case "msql":
                @msql_free_result($resultId);
                break;
        }
        return $Result;
    }

    //-- TEST: db_fill_select
    function db_fill_select($userTable, $PK, $fieldName, $where="", $order=FALSE, $multi=FALSE) {
        if ( strlen($where)>0 ){
            $sqlQuery = "Select * FROM $userTable $where";
        } else {
            $sqlQuery = "Select * FROM $userTable";
        }

        if ( $order ) {
            $sqlQuery = $sqlQuery." order by $fieldName";
        }

        switch ($this->DBType) {
            case "sqlsrv":
            case "mssql":
            case "pgsql":
                $queryResult  = $this->db_query($sqlQuery);
                if ( !$queryResult ){
                    print "ERROR :: ".$this->db_error_message()."\n";
                    print "QUERY :: $sqlQuery\n";
                }
                $numberRows   = $this->db_num_rows($queryResult);
                $numberFields = $this->db_num_fields($queryResult);

                if ( $numberRows > 0 ) {
                    for ($rowNumber = 0 ; $rowNumber < $numberRows ; $rowNumber++) {
                        print "<option value=\""
                              . $this->db_result($queryResult, $rowNumber, "$PK")
                              . "\">"
                              . trim($this->db_result($queryResult, $rowNumber, "$fieldName"))
                              ."</option>\n";
                    }
                }
                break;
        }
    }

    //--
    //-- 24-JUN-2004 :: LEP
    //--  this grew from a need to be able to support multiple database types. each
    //--  one having their own string length function name
    //--
    //-- TEST: dbStrLen
    function dbStrLen ( $dbColumn ) {
        switch ($this->DBType) {
            case "mysql":
            case "pgsql":
                $return = "length($dbColumn)";
                break;
            case "sqlsrv":
            case "mssql":
                $return = "LEN($dbColumn)";
                break;
        }
        return $return;
    } // end of function dbStrLen


    /**
      * prep - take a value as 2nd param, from the 1st param determine whether or not the value should be wrapped with single-quotes
      *
      * 3rd param explanation - Most of the time when I have <select> options, the very first option will have a value of -1 to detect if the user has chose a value
      */
    //-- TEST: prep
    function prep( $type, $value, $checkForNonValue=false ) {
     switch ($type) {
        case "varchar"   :
        case "v"         :
        case "date"      :
        case "d"         :
        case "text"      :
        case "te"        :
        case "timestamp" :
        case "ti"        :
            if ( $checkForNonValue==true && strlen($value)>0 && $value=='-1' ) {
                return "null";
            } else {
                if ( strlen($value)==0 ) {
                    return "null";
                } else {
                    return "'$value'"; // only difference, single-quotes
                }
            }
            break;
        case "integer":
        case "i"      :
        case "float"  :
        case "f"      :
            if ( $checkForNonValue==true && strlen($value)>0 && $value=='-1' ) {
                return "null";
            } else {
                if ( strlen($value)==0 ) {
                    return "null";
                } else {
                    return "$value";
                }
            }
            break;
        }
    } /** end of function prep **/
} // End of class Db