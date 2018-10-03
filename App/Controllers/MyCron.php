<?php

class MyCron {

    function clearCache( $f3 ) {
        print "\n";
        print_r( $f3 );
        print "\n";
        exit;
        $cache = \Cache::instance();
        $cache->reset();
        print "[".date('r')."] after reset\n";
    }//--end of clearCache

}//--end of class MyCron