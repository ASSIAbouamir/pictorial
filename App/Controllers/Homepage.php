<?php

class Homepage extends App {
    function index() {
        $this->renderLayoutTemplate( 'index.html' );
    }
    function error() {
        $this->renderLayoutTemplate( 'error.html' );
    }
}
