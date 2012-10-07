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
    $hash1 = md5( mt_rand(3, 10) );
    $hash2 = md5( mt_rand(11, 13) );
    $test_type_1 = "test";
    $test_type_2 = "testrel";

    $test_bean_1 = R::dispense( $test_type_1 );
    $test_bean_1->hash = $hash1;
    $test_bean_2 = R::dispense( $test_type_2 );
    $test_bean_2->hash = $hash2;

    // R::store( $test_bean_1, $test_bean_2);
    RBB::associate( $test_bean_1, RBB::RB_HAS_ONE, $test_bean_2 );

    $test_bean_1_loaded = R::relatedOne( $test_bean_2, $test_type_1 );
    $test_bean_2_loaded = R::relatedOne( $test_bean_1, $test_type_2 );

    $this->assertEqual( $test_bean_1->hash, $test_bean_1_loaded->hash );
    $this->assertEqual( $test_bean_2->hash, $test_bean_2_loaded->hash );

    // Test if expected exception is thrown when relating two beans that are already related
    // expectedException would swallow the rest of the testing code
    // so try/catch clause were used
    // $this->expectException( 'BeanBase_Exception_Relation' );
    // RBB::associate( $test_bean_1, $test_bean_2, RBB::RB_HAS_ONE );
    try {
      RBB::associate( $test_bean_1, RBB::RB_HAS_ONE, $test_bean_2 );
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
    $num_rel = 2;
    $hash    = md5( mt_rand(3, 18) );
    $test_type_1 = "test1";
    $test_type_2 = "test2";
    $test_bean_1 = R::dispense( $test_type_1 );
    $test_bean_1->hash = $hash;

    // Test from the perspective of test_bean_rel's parent id == test_bean_1->id
    // And expected exception if relationship is already established
    for ( $i = 0; $i < $num_rel; $i++ ) {
      $test_bean_rel = R::dispense( $test_type_2 );

      RBB::associate( $test_bean_1, RBB::RB_HAS_MANY, $test_bean_rel );

      $this->assertEqual( $test_bean_rel->$test_type_1->hash, $test_bean_1->hash );

      // Test expected exception
      try {
        RBB::associate( $test_bean_1, RBB::RB_HAS_MANY, $test_bean_rel );
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

    RBB::associate( $b1, RBB::RB_HAVE_MANY, $r1 );
    RBB::associate( $b1, RBB::RB_HAVE_MANY, $r2 );
    RBB::associate( $b2, RBB::RB_HAVE_MANY, $r1 );
    RBB::associate( $b2, RBB::RB_HAVE_MANY, $r2 );

    $this->assertIdentical( count(R::related($b1, $test_type_2)), 2 );
    $this->assertIdentical( count(R::related($b2, $test_type_2)), 2 );
    $this->assertIdentical( count(R::related($r1, $test_type_1)), 2 );
    $this->assertIdentical( count(R::related($r2, $test_type_1)), 2 );

    // Test expected exception
    try {
      RBB::associate( $b1, RBB::RB_HAVE_MANY, $r1 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b1, RBB::RB_HAVE_MANY, $r2 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b2, RBB::RB_HAVE_MANY, $r1 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $b2, RBB::RB_HAVE_MANY, $r2 );
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
    $num_rel = 2;
    $hash    = md5( mt_rand(3, 13) );
    $test_type_1 = "t5";
    $test_type_2 = "t6";
    $test_bean   = R::dispense( $test_type_1 );
    $test_bean->hash = $hash;

    for ( $i = 0; $i < $num_rel; $i++ ) {
      $rel_bean = R::dispense( $test_type_2 );

      RBB::associate( $rel_bean, RBB::RB_BELONGS_TO, $test_bean );

      $this->assertEqual( $rel_bean->$test_type_1->hash, $test_bean->hash );

      try {
        RBB::associate( $rel_bean, RBB::RB_BELONGS_TO, $test_bean );
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
    // Test SELF-ONE-TO-ONE
    //
    // ------------------------------------------------------------------
    // Reuses $test_type_1 from above
    $hash = md5( mt_rand(3, 13) );
    list( $s1, $s2, $s3 ) = R::dispense( $test_type_1, 3 );
    $s1->hash = $hash;

    RBB::associate( $s1, RBB::RB_HAS_ONE_SELF, $s2 );

    $this->assertEqual( R::findOne($test_type_1, ' '.RBB::RB_SELF_REF.'_id = ? ', array($s1->id))->hash, $s2->hash );

    try {
      RBB::associate( $s1, RBB::RB_HAS_ONE_SELF, $s2 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $s1, RBB::RB_HAS_ONE_SELF, $s3 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $s2, RBB::RB_HAS_ONE_SELF, $s1 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    try {
      RBB::associate( $s2, RBB::RB_HAS_ONE_SELF, $s3 );
      $this->fail( 'Expected BeanBase_Exception_Relation' );
    } catch ( BeanBase_Exception_Relation $e ) {
      $this->pass();
    }

    unset( $s1, $s2, $s3 );

    // ==================================================================
    //
    // Test SELF-ONE-TO-MANY
    //
    // ------------------------------------------------------------------
    // Reuses $test_type_2 from above
    $hash = md5( mt_rand(3, 13) );
    $s1 = R::dispense( $test_type_2 );
    $s1->hash = $hash;
    $num_rel = 2;

    for ( $i = 0; $i < $num_rel; $i++ ) {
      $rel = R::dispense( $test_type_2 );

      RBB::associate( $s1, RBB::RB_HAS_MANY_SELF, $rel );

      $this->assertEqual( $rel->fetchAs($test_type_2)->{RBB::RB_SELF_REF}->hash, $s1->hash );

      try {
        RBB::associate( $s1, RBB::RB_HAS_MANY_SELF, $rel );
        $this->fail( 'Expected BeanBase_Exception_Relation' );
      } catch ( BeanBase_Exception_Relation $e ) {
        $this->pass();
      }

      unset( $rel );
    }

    $this->assertIdentical( count(R::find($test_type_2, ' '.RBB::RB_SELF_REF.'_id = ? ', array($s1->id))), $num_rel );

    unset( $s1 );

    // ==================================================================
    //
    // Test SELF-MANY-TO-MANY
    //
    // ------------------------------------------------------------------
    // TODO

    // ==================================================================
    //
    // Test SELF-BELONGS-TO
    //
    // ------------------------------------------------------------------
    $hash = md5( mt_rand(3, 13) );
    $type = "selfbelong";
    $s1   = R::dispense( $type );
    $num  = 2;
    $s1->hash = $hash;

    for ( $i = 0; $i < $num; $i++ ) {
      $rel = R::dispense( $type );

      RBB::associate( $rel, RBB::RB_BELONGS_TO_SELF, $s1 );

      $this->assertEqual( $rel->fetchAs($type)->{RBB::RB_SELF_REF}->hash, $s1->hash );

      try {
        RBB::associate( $rel, RBB::RB_BELONGS_TO_SELF, $s1 );
        $this->fail( 'Expected BeanBase_Exception_Relation' );
      } catch ( BeanBase_Exception_Relation $e ) {
        $this->pass();
      }

      unset( $rel );
    }

    $this->assertIdentical( count(R::find($type, ' '.RBB::RB_SELF_REF.'_id = ? ', array($s1->id))), $num );

    // ==================================================================
    //
    // Test relate() method
    //
    // TODO: self reference tests
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