<?php

if ( file_exists(__DIR__.'/config/my.db.config.php') ) {
  require_once( __DIR__.'/config/my.db.config.php' );
} else {
  require_once( __DIR__.'/config/db.config.php' );
}

require_once( __DIR__.'/simpletest/autorun.php' );
require_once( __DIR__.'/vendor/rb.php' );
require_once( __DIR__.'/vendor/rbb.php' );

class BeanBase_Tests extends TestSuite {

  public function __construct() {
    parent::__construct();

    // $this->collect( __DIR__.'/unit', new SimplePatternCollector('/_test.php/') );
    $this->addFile( __DIR__.'/unit/RBB_Tests.php' );
  }

}