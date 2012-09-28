<?php

/**
 * Copyright (c) 2012 ruli.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     BeanBase
 * @author      ruli <runzhou.li@gmail.com>
 * @copyright   2012 ruli.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        https://github.com/ruli/BeanBase
 */

/**
 * BeanBase Base Model
 *
 * Modified to extend CI_Model
 *
 * @abstract
 * @package BeanBase
 * @subpackage Model
 */
class MY_Model extends CI_Model {

  /**
   * The bean type name
   *
   * @var string Bean type
   */
  protected $_type = "";

  /**
   * Association filter
   *
   * @var array Data kv-array with a format of bean_type => association_code
   */
  protected $_asso_filter = array(
    // 'bean_type' => RB_HAS_ONE,
    // 'bean_type' => RB_HAS_MANY,
    // 'bean_type' => RB_HAVE_MANY,
    // 'bean_type' => RB_BELONGS_TO
  );

  /**
   * Reserved fields which (normally) should not be altered
   * Default reserved fields:
   *   deleted - flag indicating whether the bean is 'soft' deleted
   *   created - timestamp indicating when bean is created
   *   updated - timestamp indicating when bean is updated
   *
   * @var array String keys represent reserved fields
   */
  protected $_reserved_fields = array(
    // Default fields
    RB_DELETED, // only be altered by delete() and recover()
    RB_CREATED, // only be altered by post()
    RB_UPDATED, // only be altered by update()
  );

  /**
   * These fields must be met when trying to create a new bean of the current type
   *
   * @var array String keys represent the required fields when creating a new bean
   */
  protected $_post_fields = array(); // required create fields

  /**
   * These fields must be met when trying to update an existing bean
   *
   * @var array String keys represent the required fields when updating an existing bean
   */
  protected $_put_fields = array(); // required update fields

  /**
   * Fields that should be unique in a bean type
   *
   * @var array String keys represent unique fields
   */
  protected $_unique_fields = array();

  public function __construct() {
    parent::__construct();
  }

  // ==================================================================
  //
  // Getters (accessors) and setters (mutators)
  //
  // ------------------------------------------------------------------
  /**
   * Get the type of the current model (also, of the bean and of the data table
   * )
   * @return string The type name
   */
  public function get_type() {
    return $this->_type;
  }

  /**
   * Get association filter
   *
   * @return array The association filter
   */
  public function get_asso_filter() {
    return $this->_asso_filter;
  }

  /**
   * Get reserved fields
   *
   * @return array The reserved fields
   */
  public function get_reserved_fields() {
    return $this->_reserved_fields;
  }

  /**
   * Get required POST (create) fields
   *
   * @return array The required POST fields
   */
  public function get_post_fields() {
    return $this->_post_fields;
  }

  /**
   * Get required PUT (update) fields
   *
   * @return array The required PUT fields
   */
  public function get_put_fields() {
    return $this->_put_fields;
  }

  /**
   * Get unique fields
   *
   * @return array The unique fields
   */
  public function get_unique_fields() {
    return $this->_unique_fields;
  }

  public function set_type( $type ) {
    $this->_type = $type;
  }

  public function set_asso_filter( array $asso_filter ) {
    $this->_asso_filter = $asso_filter;
  }

  public function set_reserved_fields( array $reserved_fields ) {
    $this->_reserved_fields = $reserved_fields;
  }

  // ==================================================================
  //
  // CRUDs
  //
  // ------------------------------------------------------------------
  /**
   * POST (create) a new bean and store that into the database
   *
   * This method has certain level of data filtering but it is
   * its caller's responsibility to make sure the request_data
   * is sanitized
   *
   * @param  array $request_data Data array with all the information needed to create the data
   *
   * @return RedBean_OODBBean    The created and stored bean
   */
  public function post( array $request_data ) {
    // Exclude the reserved fields from posted data
    if ( !empty($this->_reserved_fields) ) {
      $request_data = array_diff_key( $request_data, $this->_reserved_fields );
    }

    // Create bean
    $bean = RBB::create( $this->_type, $request_data );

    // Check whether the data is unique
    if ( !empty($this->_unique_fields) ) {
      $bean = RBB::set_unique( $bean, $this->_unique_fields );
      //$bean->setMeta( 'buildcommand.unique', array($this->_unique_fields) ) ;
      //RBB::uniqueness_check( $bean, $this->_unique_fields );
    }

    // Check whether the data is complete
    if ( !empty($this->_post_fields) ) {
      RBB::completeness_check( $bean, $this->_post_fields );
    }

    // Add the updated timestamp
    if ( in_array(C::RB_CREATED, $this->_reserved_fields) ) {
      $bean = RBB::insert_time_stamp( $bean, C::RB_CREATED );
    }

    // Process association filter if applicable
    if ( !empty($this->_asso_filter) ) {
      RBB::relate( $bean, $request_data, $this->_asso_filter );
    } else {
      if ( RBB::is_modified($bean) ) {
        R::store( $bean );
      }
    }

    // Return created bean
    return $bean;
  }

  /**
   * GET (read) a bean with given ID
   *
   * @param  integer $id          The ID of the bean
   *
   * @return RedBean_OODBBean     The fetched bean
   */
  public function get( $id ) {
    return RBB::read( $id, $this->_type );
  }

  /**
   * PUT (update) a bean with given ID
   *
   * @param  integer  $id           The ID of the bean
   * @param  array    $request_data The data array
   *
   * @return RedBean_OODBBean       The updated bean
   */
  public function put( $id, array $request_data ) {
    $bean = $this->get( $id );

    // Exclude the reserved fields from given data
    if ( !empty($this->_reserved_fields) ) {
      $request_data = array_diff_key( $request_data, $this->_reserved_fields );
    }

    // Check whether the data is unique
    if ( !empty($this->_unique_fields) ) {
      $bean = RBB::set_unique( $bean, $this->_unique_fields );
      //$bean->setMeta( 'buildcommand.unique', array($this->_unique_fields) ) ;
      //RBB::uniqueness_check( $bean, $this->_unique_fields );
    }

    // Check whether the request data is complete
    if ( !empty($this->_put_fields) ) {
      RBB::completeness_check( $bean, $this->_post_fields );
    }

    // Add the updated timestamp
    if ( in_array(C::RB_UPDATED, $this->_reserved_fields) ) {
      $bean = RBB::insert_time_stamp( $bean, C::RB_UPDATED );
    }

    // Process association filter if applicable
    // Either way store the actual bean
    if ( !empty($this->_asso_filter) ) {
      RBB::relate( $bean, $request_data, $this->_asso_filter );
    } else {
      if ( RBB::is_modified($bean) ) {
        R::store( $bean );
      }
    }

    // Return created bean
    return $bean;
  }

  public function delete( $id, $soft=false ) {
    $bean = RBB::read( $id, $this->_type );

    if ( $soft ) {
      $bean->{C::RB_DELETED} = true;

      if ( RBB::is_modified($bean) ) {
        R::store( $bean );
      }
    } else {
      R::trash( $bean );
    }
  }

  // ==================================================================
  //
  // Count, Batch, Query
  //
  // ------------------------------------------------------------------
  /**
   * The number of beans in this type
   *
   * @return int Number of beans
   */
  public function count() {
    return R::count( $this->_type );
  }

  /**
   * Count number of beans by WHERE clause (only available since RedBean PHP 3.3)
   * @todo   Add RedBean version check
   *
   * @return int Number of beans
   */
  public function count_by( $col, $val ) {
    return R::count( $this->_type, 'WHERE '.$col.'=?', array($val) );
  }

  /**
   * Batch get beans with an offset, limit and an optional order by string
   *
   * For general pagination purposes. For specific pagination with search phrase
   * use search() method
   *
   * @param  int    $offset Offset (page number - 1)
   * @param  int    $limit  Limit (per page)
   * @param  string $order  Order by, default value 'id'
   * @return mixed          Array of beans
   */
  public function batch_get( $offset, $limit, $order='id' ) {
    return R::findAll( $this->_type, ' ORDER BY '.$order.' LIMIT '.$offset.', '.$limit );
  }

}

/* core\MY_Model.php */