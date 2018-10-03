<?php
// namespace App\Utils;

class Util {

    private static $prod_apiBaseUrl = "https://ea.in.dynetics.com/pic/api/";
    private static $dev_apiBaseUrl  = "http://web-dev1.in.dynetics.com/lance_test/pic/api/";

    //-- Constructor
    function __construct() {
    }

    public static function CallAPI( $method="GET", $url, $data = false, $isDev = false ) {
        $curl = curl_init();

        if ( $isDev===true ) {
            $url = static::$dev_apiBaseUrl . $url;
        } else {
            $url = static::$prod_apiBaseUrl . $url;
        }

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //-- getenv, aka. php DOTENV
        curl_setopt($curl, CURLOPT_USERPWD, getenv("API_UID") . ":" . getenv("API_PWD") );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }//--end of CallAPI

} // End of class Db