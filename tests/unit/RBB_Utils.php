<?php

class RBB_Utils extends BaseTestCase {

  public function test_Utils() {
    foreach ( $this->_selectors as $selector ) {
      $this->_smoke_utils( $selector );
    }
  }

  private function _smoke_utils( $db_type ) {
    R::selectDatabase( $db_type );

    $test_type = "test";
    $test_data = array();
    $test_bean = R::dispense( $test_type );
    $complete  = array( 'complete' );

    // Test get_bean_type()
    $this->assertIdentical( RBB::get_bean_type($test_bean), $test_type );

    // Test is_modified()
    $this->assertTrue( RBB::is_modified($test_bean) );

    // is_complete() is deprecated
    // $this->assertFalse( RBB::is_complete($test_data, $complete) );
    // $test_data['complete'] = 1;
    // $this->assertTrue( RBB::is_complete($test_data, $complete) );

    // Test check_complete()
    try {
        RBB::check_complete( $test_data, $complete );
        $this->fail( 'Expected BeanBase_Exception' );
    } catch ( BeanBase_Exception $e ) {
        $this->pass();
    }

    try {
        $test_data['complete'] = 1;
        RBB::check_complete( $test_data, $complete );
        $this->pass();
    } catch ( BeanBase_Exception $e ) {
        $this->fail( 'Expected BeanBase_Exception' );
    }

    // Test strip_out()
    $this->assertEqual( RBB::strip_out($test_data, $complete), array() );

    // Test keep_only()
    $this->assertEqual( RBB::keep_only($test_data, $complete), $test_data );

    // Test insert_timestamp()
    $timestamp = new DateTime( 'now' );
    $format    = "Y-m-d H:i:s";
    $test_bean = RBB::insert_timestamp( $test_bean, 'updated', $timestamp, $format );
    $this->assertIdentical( $test_bean->updated, $timestamp->format($format) );

    // Test set_unique()
    $test_bean->title = "This has to be unique";
    R::store( $test_bean );

    // $verify_bean = R::dispense( $test_type );
    // RBB::set_unique( $verify_bean, array('title') );
    // $verify_bean->title = "This has to be unique";

    // try {
    //   R::store( $verify_bean );
    //   $this->fail( 'Should catch RedBean_Exception_SQL' );
    // } catch ( RedBean_Exception_SQL $e ) {
    //   $this->pass();
    // }

    // Test is_assoc()
    $this->assertFalse( RBB::is_assoc(array()) );
    $this->assertTrue( RBB::is_assoc($test_data) );

    // Cleanup
    unset( $test_data, $test_bean, $complete, $timestamp );
  }

}
