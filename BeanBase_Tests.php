<?php

if ( file_exists(__DIR__.'/tests/config/my.db.config.php') ) {
  require_once( __DIR__.'/tests/config/my.db.config.php' );
} else {
  require_once( __DIR__.'/tests/config/db.config.php' );
}

require_once( __DIR__.'/tests/simpletest/autorun.php' );
require_once( __DIR__.'/tests/vendor/rb.php' );
require_once( __DIR__.'/rbb.php' );
require_once( __DIR__.'/BaseModel.php' );

require_once( __DIR__.'/tests/unit/BaseTestCase.php' );

class BeanBase_Tests extends TestSuite {

  public function __construct() {
    parent::__construct();

    // $this->collect( __DIR__.'/unit', new SimplePatternCollector('/_test.php/') );

    // RBB
    $this->addFile( __DIR__.'/unit/RBB_Utils.php' );
    $this->addFile( __DIR__.'/unit/RBB_CRUD.php' );
    $this->addFile( __DIR__.'/unit/RBB_Relations.php' );

    // BaseModel
    $this->addFile( __DIR__.'/unit/BaseModel_Utils.php' );
    $this->addFile( __DIR__.'/unit/BaseModel_CRUD.php' );
  }

}