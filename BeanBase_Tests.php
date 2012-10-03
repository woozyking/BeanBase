<?php

define( 'TESTS_DIR', __DIR__.'/tests' );

if ( file_exists(TESTS_DIR.'/config/my.db.config.php') ) {
  require_once( TESTS_DIR.'/config/my.db.config.php' );
} else {
  require_once( TESTS_DIR.'/config/db.config.php' );
}

require_once( TESTS_DIR.'/simpletest/autorun.php' );
require_once( TESTS_DIR.'/vendor/rb.php' );
require_once( __DIR__.'/rbb.php' );
require_once( __DIR__.'/BaseModel.php' );

require_once( TESTS_DIR.'/unit/BaseTestCase.php' );

class BeanBase_Tests extends TestSuite {

  public function __construct() {
    parent::__construct();

    // $this->collect( __DIR__.'/unit', new SimplePatternCollector('/_test.php/') );

    // RBB
    $this->addFile( TESTS_DIR.'/unit/RBB_Utils.php' );
    $this->addFile( TESTS_DIR.'/unit/RBB_CRUD.php' );
    $this->addFile( TESTS_DIR.'/unit/RBB_Relations.php' );

    // BaseModel
    $this->addFile( TESTS_DIR.'/unit/BaseModel_Utils.php' );
    $this->addFile( TESTS_DIR.'/unit/BaseModel_CRUD.php' );
  }

}