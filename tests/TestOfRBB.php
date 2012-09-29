<?php

require_once( __DIR__.'/simpletest/autorun.php' );
require_once( __DIR__.'/vendor/rb.php' );
require_once( '../rbb.php' );

/**
 * Smoke Tests for RBB
 *
 * @todo Test for set_unique
 * @todo Test for throwable exceptions
 */
class TestOfRBB extends UnitTestCase {

  public function __construct() {
    parent::__construct( 'Testing RBB Utility Class' );
  }

  public function setUp() {
    // Load config file
    if ( file_exists(__DIR__.'/config/my.db.config.php') ) {
      require_once( __DIR__.'/config/my.db.config.php' );
    } else {
      require_once( __DIR__.'/config/db.config.php' );
    }

    R::setup( 'sqlite:'.SQLite::FILE, SQLite::USER, SQLite::PASS );
    R::addDatabase( 'MySQL', 'mysql:host='.MySQL::HOST.';dbname='.MySQL::DB, MySQL::USER, MySQL::PASS, false );
    R::addDatabase( 'PgSQL', 'pgsql:host='.PgSQL::HOST.';dbname='.PgSQL::DB.'', PgSQL::USER, PgSQL::PASS, false );

    R::nuke();
  }

  public function tearDown() {
    R::nuke();
    R::close();
  }

  public function testUtils() {
    $test_type = "test";
    $test_data = array();
    $test_bean = R::dispense( $test_type );
    $complete  = array( 'complete' );

    // Test get_bean_type()
    $this->assertIdentical( RBB::get_bean_type($test_bean), $test_type );

    // Test is_modified()
    $this->assertTrue( RBB::is_modified($test_bean) );

    // Test is_complete()
    $this->assertFalse( RBB::is_complete($test_data, $complete) );
    $test_data['complete'] = 1;
    $this->assertTrue( RBB::is_complete($test_data, $complete) );

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
    ### TODO ###

    // Test is_assoc()
    $this->assertFalse( RBB::is_assoc(array()) );
    $this->assertTrue( RBB::is_assoc($test_data) );

    // Cleanup
    unset( $test_data, $test_bean, $complete, $timestamp );
  }

  // ==================================================================
  //
  // CRU tests
  //
  // ------------------------------------------------------------------
  public function testCRU() {
    $this->_smoke_cru( 'MySQL' );
    $this->_smoke_cru( 'PgSQL' );
    $this->_smoke_cru( 'default' );
  }

  private function _smoke_cru( $db_type ) {
    R::selectDatabase( $db_type );

    $test_type = "test";
    $filter    = array( 'filteredOut' );
    $data      = array(
                   'subject'     => 'testing create',
                   'filteredOut' => 'this should be filtered out and never exist in created beans'
                 );

    // ==================================================================
    //
    // Test CREATE
    //
    // ------------------------------------------------------------------
    $test_bean = RBB::create( $test_type, $data, $filter );
    $test_bean_unfiltered = RBB::create( $test_type, $data );

    // If bean instanceof RedBean_OODBBean
    $this->assertTrue( $test_bean instanceof RedBean_OODBBean );
    $this->assertTrue( $test_bean_unfiltered instanceof RedBean_OODBBean );

    // If bean->property === $data[property]
    $this->assertIdentical( $test_bean->subject, $data['subject'] );
    $this->assertIdentical( $test_bean_unfiltered->subject, $data['subject'] );

    // If filter worked as expected
    $this->assertFalse( isset($test_bean->filteredOut) );
    $this->assertTrue( isset($test_bean_unfiltered->filteredOut) );

    unset( $test_bean_unfiltered, $data );

    R::store( $test_bean );

    // ==================================================================
    //
    // Test READ
    //
    // ------------------------------------------------------------------
    $test_bean_read = RBB::read( $test_bean->id, $test_type );

    // If $test_bean->id === $test_bean_read->id
    $this->assertIdentical( $test_bean->subject, $test_bean_read->subject );

    unset( $test_bean );

    // ==================================================================
    //
    // Test UPDATE
    //
    // ------------------------------------------------------------------
    $data = array(
              'subject'      => 'Test new subject',
              'update_value' => 'This is updated value with new property added',
              'filteredOut'  => 'This should be filtered out'
            );

    $test_bean_update            = RBB::update( $test_bean_read, $data, $filter );
    $test_bean_update_unfiltered = RBB::update( $test_bean_read, $data );

    // If bean instanceof RedBean_OODBBean
    $this->assertTrue( $test_bean_update instanceof RedBean_OODBBean );
    $this->assertTrue( $test_bean_update_unfiltered instanceof RedBean_OODBBean );

    // If updated bean != read bean
    $this->assertNotEqual( $test_bean_read, $test_bean_update );
    $this->assertNotEqual( $test_bean_read, $test_bean_update_unfiltered );

    // If updated bean->property === data[property]
    $this->assertIdentical( $test_bean_update->subject, $data['subject'] );
    $this->assertIdentical( $test_bean_update_unfiltered->subject, $data['subject'] );

    // If filter worked as expected
    $this->assertFalse( isset($test_bean_update->filteredOut) );
    $this->assertTrue( isset($test_bean_update_unfiltered->filteredOut) );

    unset( $test_bean, $test_bean_read, $test_bean_update, $test_bean_update_unfiltered );
  }

  // ==================================================================
  //
  // Relational tests
  //
  // ------------------------------------------------------------------
  public function testRel() {
    $this->_smoke_rel( 'MySQL' );
    $this->_smoke_rel( 'PgSQL' );
    $this->_smoke_rel( 'default' );
  }

  private function _smoke_rel( $db_type ) {

  }

}