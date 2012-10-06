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
 * @abstract
 * @package BeanBase
 * @subpackage Model
 */
class BaseModel {

  public $type = "";

  public $asso_filter = array(
    // 'bean_type' => RB_HAS_ONE,
    // 'bean_type' => RB_HAS_MANY,
    // 'bean_type' => RB_HAVE_MANY,
    // 'bean_type' => RB_BELONGS_TO
  );

  public $reserved_fields = array(
    // Default fields
    RBB::RB_DELETED,
    RBB::RB_CREATED,
    RBB::RB_UPDATED,
    RBB::RB_RELATION
  );

  public $post_fields = array(); // required create fields

  public $put_fields = array(); // required update fields

  public $unique_fields = array(); // properties that should be unique

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
  public function post( array $request_data=null ) {
    // Check data completeness
    if ( !empty($this->post_fields) ) {
      RBB::check_complete( $request_data, $this->post_fields );
    }

    if ( !empty($request_data) ) {
      // Separate out relations field
      $relations = null;

      if ( (isset($request_data[RBB::RB_RELATION]) || array_key_exists(RBB::RB_RELATION, $request_data))
            && RBB::is_assoc($request_data[RBB::RB_RELATION]) )
      {
        $relations = $request_data[RBB::RB_RELATION];
      }

      // Strip out reserved fields from data
      if ( !empty($this->reserved_fields) ) {
        $request_data = RBB::strip_out( $request_data, $this->reserved_fields );
      }

      $bean = RBB::create( $this->type, $request_data );

      if ( !empty($this->unique_fields) ) {
        $bean = RBB::set_unique( $bean, $this->unique_fields );
      }
    } else {
      $bean = RBB::create( $this->type );
    }

    $bean = RBB::insert_timestamp( $bean, RBB::RB_CREATED, new DateTime('now') );

    // Process association filter if applicable
    if ( !empty($relations) && !empty($this->asso_filter) ) {
      RBB::relate( $bean, $relations, $this->asso_filter );
    }

    if ( RBB::is_modified($bean) ) {
      R::store( $bean );
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
    return RBB::read( $id, $this->type );
  }

  /**
   * PUT (update) a bean with given ID
   *
   * @param  integer  $id           The ID of the bean
   * @param  array    $request_data The data array
   *
   * @return RedBean_OODBBean       The updated bean
   */
  public function put( $id, array $request_data=null ) {
    // Check data completeness
    if ( !empty($this->put_fields) ) {
      RBB::check_complete( $request_data, $this->put_fields );
    }

    // Get bean by id
    $bean = $this->get( $id );

    if ( !empty($request_data) ) {
      // Separate out relations field
      $relations = null;

      if ( (isset($request_data[RBB::RB_RELATION]) || array_key_exists(RBB::RB_RELATION, $request_data))
            && RBB::is_assoc($request_data[RBB::RB_RELATION]) )
      {
        $relations = $request_data[RBB::RB_RELATION];
      }

      // Strip out reserved fields from data
      // including RBB::RB_RELATION
      if ( !empty($this->reserved_fields) ) {
        $request_data = RBB::strip_out( $request_data, $this->reserved_fields );
      }

      // For update only
      // Repetitive data handler
      foreach ( $request_data as $key => $vale ) {
        if ( (isset($bean->$key) || property_exists($bean, $key)) && ($bean->$key == $request_data[$key]) ) {
          unset( $request_data[$key] );
        }
      }

      // Update with newly inserted data
      $bean = RBB::update( $bean, $request_data );

      // Insert timestamp
      $bean = RBB::insert_timestamp( $bean, RBB::RB_UPDATED, new DateTime('now') );

      if ( !empty($this->unique_fields) ) {
        $bean = RBB::set_unique( $bean, $this->unique_fields );
      }

      // Process association filter if applicable
      if ( !empty($relations) && !empty($this->asso_filter) ) {
        RBB::relate( $bean, $relations, $this->asso_filter );
      }
    }

    if ( RBB::is_modified($bean) ) {
      R::store( $bean );
    }

    return $bean;
  }

  public function delete( $id, $soft = false ) {
    $bean = $this->get( $id );

    if ( $soft ) {
      $bean->{RBB::RB_DELETED} = true;

      // Add the updated timestamp
      if ( in_array(RBB::RB_UPDATED, $this->reserved_fields) ) {
        $bean = RBB::insert_timestamp( $bean, RBB::RB_UPDATED, new DateTime('now') );
      }

      if ( RBB::is_modified($bean) ) {
        R::store( $bean );
      }

      return $bean;
    } else {
      R::trash( $bean );

      return null;
    }
  }

  // ==================================================================
  //
  // Utils
  //
  // ------------------------------------------------------------------
  /**
   * The number of beans in this type
   *
   * @return int Number of beans
   */
  public function count() {
    return R::count( $this->type );
  }

  /**
   * Count number of beans by WHERE clause (only available since RedBean PHP 3.3)
   * @todo   Add RedBean version check
   *
   * @return int Number of beans
   */
  public function count_by( $col, $val ) {
    return R::count( $this->type, 'WHERE '.$col.'=?', array($val) );
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
    return R::findAll( $this->type, ' ORDER BY '.$order.' LIMIT '.$limit.' OFFSET '.$offset );
  }

}
