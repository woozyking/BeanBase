<?php

abstract class BaseTestCase extends UnitTestCase {

  protected $_selectors = array( 'default' );

  public function setUp() {
    R::setup( 'sqlite:'.SQLite::FILE, SQLite::USER, SQLite::PASS );

    if ( Selector::MySQL ) {
      $this->_selectors[] = 'MySQL';

      R::addDatabase( 'MySQL', 'mysql:host='.MySQL::HOST.';dbname='.MySQL::DB, MySQL::USER, MySQL::PASS, false );
    }

    if ( Selector::PgSQL ) {
      $this->_selectors[] = 'PgSQL';

      R::addDatabase( 'PgSQL', 'pgsql:host='.PgSQL::HOST.';dbname='.PgSQL::DB.'', PgSQL::USER, PgSQL::PASS, false );
    }

    R::nuke();
  }

  public function tearDown() {
    R::nuke();
    R::close();
  }

}