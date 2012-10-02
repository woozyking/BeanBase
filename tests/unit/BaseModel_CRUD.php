<?php

class PostModel extends BaseModel {

  public $type = "post";

  protected $_post_fields = array(
      'title',
      'content'
    );

  protected $_asso_filter = array(
      "super"   => RBB::RB_HAS_ONE,
      "comment" => RBB::RB_HAS_MANY,
      "user"    => RBB::RB_BELONGS_TO,
      "tag"     => RBB::RB_HAVE_MANY
    );

  protected $_unique_fields = array(
      'title'
    );

}

class SuperModel extends BaseModel {

  public $type = "super";
}

class CommentModel extends BaseModel {

  public $type = "comment";

}

class UserModel extends BaseModel {

  public $type = "user";

}

class TagModel extends BaseModel {

  public $type = "tag";

}

class BaseModel_CRUD extends BaseTestCase {

  public function test_CRUD() {
    foreach ( $this->_selectors as $selector ) {
      $this->_smoke_crud( $selector );
    }
  }

  private function _smoke_crud( $db_type ) {
    R::selectDatabase( $db_type );

    $post    = new PostModel();
    $super   = new SuperModel();
    $comment = new CommentModel();
    $user    = new UserModel();
    $tag     = new TagModel();

    // ==================================================================
    //
    // Test POST (CREATE)
    //
    // ------------------------------------------------------------------
    for ( $i = 0; $i < 5; $i++ ) {
      $r[] = sha1( mt_rand(3, 13) );
    }

    $super_bean   = $super->post(array('identity'=>$r[0]));
    $comment_bean = $comment->post(array('identity'=>$r[1]));
    $user_bean    = $user->post(array('identity'=>$r[2]));
    $tag_bean     = $tag->post(array('identity'=>$r[3]));

    $request = array(
      'title' => 'This is a title',
      'content' => 'This is content',
      'identity' => $r[4],
      RBB::RB_RELATION => array(
        $super->type.'_id' => $super_bean->id,
        $comment->type.'_id' => $comment_bean->id,
        $user->type.'_id' => $user_bean->id,
        $tag->type.'_id' => $tag_bean->id,
        ),
      );

    // _post_fields test
    try {
      $post_bean = $post->post();
      $this->fail( 'Expected BeanBase_Exception' );
    } catch ( BeanBase_Exception $e ) {
      $this->pass();
    }

    try {
      $post_bean = $post->post( array('content' => 'This is content') );
      $this->fail( 'Expected BeanBase_Exception' );
    } catch ( BeanBase_Exception $e ) {
      $this->pass();
    }

    $post_bean = $post->post( $request );

    $this->assertIdentical( $post_bean->identity, $request['identity'] );

    // _asso_filter test
    $this->assertIdentical( R::relatedOne($post_bean, $super->type)->identity, $super_bean->identity );

    $own = "own".ucfirst($comment->type);
    $this->assertIdentical( $post_bean->{$own}[1]->identity, $comment_bean->identity );

    $this->assertIdentical( $post_bean->user->identity, $user_bean->identity );

    $shared = "shared".ucfirst($tag->type);
    $this->assertIdentical( $post_bean->{$shared}[1]->identity, $tag_bean->identity );

    // _unique_fields test
    try {
      $unique_test_bean = $post->post( array(
        'title' => 'This is a title',
        'content' => 'This is content',)
      );
      $this->fail( 'Expected RedBean_Exception_SQL' );
    } catch ( RedBean_Exception_SQL $e ) {
      $this->pass();
    }

    $post_bean_id = $post_bean->id;

    unset( $post_bean, $unique_test_bean );

    // ==================================================================
    //
    // Test GET (read)
    //
    // ------------------------------------------------------------------
    $post_bean = $post->get( $post_bean_id );

    $this->assertIdentical( $post_bean->identity, $request['identity'] );

    $post_bean_id = $post_bean->id;

    unset( $post_bean );

    // ==================================================================
    //
    // Test PUT (UPDATE)
    //
    // ------------------------------------------------------------------
    $identity = sha1('updated');
    $data = array(
        'title' => $request['title'], // unchanged, test repetitive data handler in put()
        'identity' => $identity,
        'insertion' => 'This is inserted field'
      );

    $post_bean = $post->put( $post_bean_id, $data );

    $this->assertIdentical( $post_bean->title, $data['title'] ); // just to see if repetitive data handler works
    $this->assertIdentical( $post_bean->identity, $identity );
    $this->assertIdentical( $post_bean->insertion, $data['insertion'] );

    // ==================================================================
    //
    // Test DELETE (delete)
    //
    // ------------------------------------------------------------------
    // Test soft delete
    $soft = $post->delete( $post_bean->id, true );
    $hard = $comment->delete( $comment_bean->id );

    $this->assertTrue( $soft->deleted );
    $this->assertNull( $hard );

    // Test if relational things work too after delete
    $own = "own".ucfirst($comment->type);
    $this->assertTrue( empty($post->get($post_bean->id)->$own) );
  }

}