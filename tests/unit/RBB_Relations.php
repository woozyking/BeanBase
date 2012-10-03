<?php

class RBB_Relations extends BaseTestCase {

  public function test_Rel() {
    foreach ( $this->_selectors as $selector ) {
      $this->_smoke_rel( $selector );
    }
  }

  private function _smoke_rel( $db_type ) {
    // ==================================================================
    //
    // Test ONE-TO-ONE
    //
    // ------------------------------------------------------------------
    $test_type_1 = "test";
    $test_type_2 = "testrel";

    $test_bean_1 = R::dispense( $test_type_1 );
    $test_bean_2 = R::dispense( $test_type_2 );

    // R::store( $test_bean_1, $test_bean_2);
    RBB::associate( $test_bean_1, $test_bean_2, RBB::RB_HAS_ONE );

    $test_bean_1_loaded = R::relatedOne( $test_bean_2, $test_type_1 );
    $test_bean_2_loaded = R::relatedOne( $test_bean_1, $test_type_2 );

    $this->assertEqual( $test_bean_1->id, $test_bean_1_loaded->id );
    $this->assertEqual( $test_bean_2->id, $test_bean_2_loaded->id );

    // Test if expected exception is thrown when relating two beans that are already related
    // expectedException would swallow the rest of the testing code
    // so try/catch clause were used
    // $this->expectException( 'BeanBase_Exception_Relation' );
    // RBB::associate( $test_bean_1, $test_bean_2, RBB::RB_HAS_ONE );
    try {
      RBB::associate( $test_bean_1, $test_bean_2, RBB::RB_HAS_ONE );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    unset( $test_bean_1, $test_bean_2, $test_bean_1_loaded, $test_bean_2_loaded );

    // ==================================================================
    //
    // Test ONE-TO-MANY
    //
    // ------------------------------------------------------------------
    $test_type_1 = "test1";
    $test_type_2 = "test2";
    $test_bean_1 = R::dispense( $test_type_1 );
    $num_rel     = 2;

    // Test from the perspective of test_bean_rel's parent id == test_bean_1->id
    // And expected exception if relationship is already established
    for ( $i = 0; $i < $num_rel; $i++ ) {
      $test_bean_rel = R::dispense( $test_type_2 );

      RBB::associate( $test_bean_1, $test_bean_rel, RBB::RB_HAS_MANY );

      $this->assertEqual( $test_bean_rel->$test_type_1->id, $test_bean_1->id );

      // Test expected exception
      try {
        RBB::associate( $test_bean_1, $test_bean_rel, RBB::RB_HAS_MANY );
        $this->fail( 'Expected BeanBase_Exception_Relation' );
      } catch ( BeanBase_Exception_Relation $e ) {
        $this->pass();
      }

      unset( $test_bean_rel );
    }

    // Extra test from another direction of the relationship
    $own_phrase = "own".ucfirst( $test_type_2 );
    $this->assertIdentical( count($test_bean_1->$own_phrase), $num_rel );

    unset( $test_bean_1 );

    // ==================================================================
    //
    // Test MANY-TO-MANY
    //
    // ------------------------------------------------------------------
    $test_type_1 = "t3";
    $test_type_2 = "t4";

    list( $b1, $b2 ) = R::dispense( $test_type_1, 2 );
    list( $r1, $r2 ) = R::dispense( $test_type_2, 2 );

    RBB::associate( $b1, $r1, RBB::RB_HAVE_MANY );
    RBB::associate( $b1, $r2, RBB::RB_HAVE_MANY );
    RBB::associate( $b2, $r1, RBB::RB_HAVE_MANY );
    RBB::associate( $b2, $r2, RBB::RB_HAVE_MANY );

    $this->assertIdentical( count(R::related($b1, $test_type_2)), 2 );
    $this->assertIdentical( count(R::related($b2, $test_type_2)), 2 );
    $this->assertIdentical( count(R::related($r1, $test_type_1)), 2 );
    $this->assertIdentical( count(R::related($r2, $test_type_1)), 2 );

    // Test expected exception
    try {
      RBB::associate( $b1, $r1, RBB::RB_HAVE_MANY );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b1, $r2, RBB::RB_HAVE_MANY );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b2, $r1, RBB::RB_HAVE_MANY );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b2, $r2, RBB::RB_HAVE_MANY );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    unset( $b1, $b2, $r1, $r2 );

    // ==================================================================
    //
    // Test BELONGS-TO
    //
    // ------------------------------------------------------------------
    $test_type_1 = "t5";
    $test_type_2 = "t6";
    $test_bean   = R::dispense( $test_type_1 );
    $num_rel     = 2;

    for ( $i = 0; $i < $num_rel; $i++ ) {
      $rel_bean = R::dispense( $test_type_2 );

      RBB::associate( $rel_bean, $test_bean, RBB::RB_BELONGS_TO );

      $this->assertEqual( $rel_bean->$test_type_1->id, $test_bean->id );

      try {
        RBB::associate( $rel_bean, $test_bean, RBB::RB_BELONGS_TO );
        $this->fail( 'Expected BeanBase_Exception_Relation' );
      } catch ( BeanBase_Exception_Relation $e ) {
        $this->pass();
      }

      unset( $rel_bean );
    }

    // Extra test from another direction of the relationship
    $own_phrase = "own".ucfirst( $test_type_2 );
    $this->assertIdentical( count($test_bean->$own_phrase), $num_rel );

    unset( $test_bean );

    // ==================================================================
    //
    // Test relate() method
    //
    // ------------------------------------------------------------------
    $type_1 = "t1";
    $type_2 = "has1";
    $type_3 = "hsm";
    $type_4 = "hvm";
    $type_5 = "belongs";

    list( $b1, $b2 ) = R::dispense( $type_1, 2 );
    $has_one         = R::dispense( $type_2 );
    list( $hsm1, $hsm2 ) = R::dispense( $type_3, 2 );
    list( $hvm1, $hvm2 ) = R::dispense( $type_4, 2 );
    $bl = R::dispense( $type_5 );

    R::store( $b1 );
    R::store( $b2 );
    R::store( $has_one );
    R::store( $hsm1 );
    R::store( $hsm2 );
    R::store( $hvm1 );
    R::store( $hvm2 );
    R::store( $bl );

    $filter = array(
        $type_2 => RBB::RB_HAS_ONE,
        $type_3 => RBB::RB_HAS_MANY,
        $type_4 => RBB::RB_HAVE_MANY,
        $type_5 => RBB::RB_BELONGS_TO
      );

    $data = array(
        $type_2.'_id' => $has_one->id,
        $type_3.'_id' => array( $hsm1->id, $hsm2->id ),
        $type_4.'_id' => array( $hvm1->id, $hvm2->id ),
        $type_5.'_id' => $bl->id
      );

    RBB::relate( $b1, $data, $filter );

    // Notice the dumps for b1, they're different after the second dump of the own list
    // $this->dump( $b1 );
    // $this->dump( $b1->{"own".ucfirst($type_3)} );
    // $this->dump( $b1 );

    // Test ONE-TO-ONE
    $this->assertEqual( R::relatedOne($b1, $type_2)->id, $has_one->id );
    $this->assertEqual( R::relatedOne($has_one, $type_1)->id, $b1->id );

    // Test ONE-TO-MANY
    $own = "own".ucfirst($type_3);
    $this->assertIdentical( count($b1->$own), 2 );

    // Test MANY-TO-MANY
    $data = array(
        $type_4.'_id' => array( $hsm1->id, $hsm2->id )
      );

    $filter = array(
        $type_4 => RBB::RB_HAVE_MANY
      );

    $shared = "shared".ucfirst($type_4);

    RBB::relate( $b2, $data, $filter );
    $this->assertIdentical( count($b1->$shared), 2 );
    $this->assertIdentical( count($b2->$shared), 2 );

    $shared = "shared".ucfirst($type_1);
    $this->assertIdentical( count($hvm1->$shared), 2 );
    $this->assertIdentical( count($hvm2->$shared), 2 );

    // Test BELONGS-TO
    $this->assertEqual( $b1->$type_5->id, $bl->id );
  }

}