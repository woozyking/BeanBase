<?php

class UtilTestModel extends BaseModel {
  public $type = "utilTest";
}

class BaseModel_Utils extends BaseTestCase {

  public function test_CRUD() {
    foreach ( $this->_selectors as $selector ) {
      $this->_smoke_utils( $selector );
    }
  }

  private function _smoke_utils( $db_type ) {
    R::selectDatabase( $db_type );

    // ==================================================================
    //
    // Test count()
    //
    // ------------------------------------------------------------------
    $num_beans = mt_rand( 3, 13 );
    $util      = new UtilTestModel();
    $test_type = $util->type;

    for ( $i = 0; $i < $num_beans; $i++ ) {
      R::store( R::dispense($test_type) );
    }

    $count = $util->count();

    $this->assertIdentical( $count, $num_beans );

    // ==================================================================
    //
    // Test count_by()
    // TODO: wait for RedBean 3.3
    //
    // ------------------------------------------------------------------

    // ==================================================================
    //
    // Test batch_get()
    //
    // ------------------------------------------------------------------
    $beans = $util->batch_get( 0, $num_beans+1 );

    $this->assertIdentical( count($beans), $num_beans );
  }

}