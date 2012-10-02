<?php

class RBB_CRUD_test extends BaseTestCase {

  public function test_CRU() {
    foreach ( $this->_selectors as $selector ) {
      $this->_smoke_cru( $selector );
    }
  }

  private function _smoke_cru( $db_type ) {
    R::selectDatabase( $db_type );

    $test_type = "test";
    $filter    = array( 'filteredOut' );
    $data      = array(
                   'subject'     => 'testing create',
                 );

    // ==================================================================
    //
    // Test CREATE
    //
    // ------------------------------------------------------------------
    $test_bean = RBB::create( $test_type, $data );

    // If bean instanceof RedBean_OODBBean
    $this->assertTrue( $test_bean instanceof RedBean_OODBBean );

    // If bean->property === $data[property]
    $this->assertIdentical( $test_bean->subject, $data['subject'] );

    unset( $data );

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
              'update_value' => 'This is updated value with new property added'
            );

    $test_bean_update            = RBB::update( $test_bean_read, $data );

    // If bean instanceof RedBean_OODBBean
    $this->assertTrue( $test_bean_update instanceof RedBean_OODBBean );

    // If updated bean->property === data[property]
    $this->assertIdentical( $test_bean_update->subject, $data['subject'] );
  }

}
