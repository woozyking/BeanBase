<?php

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 ruli <runzhou.li@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package     BeanBase
 * @author      ruli <runzhou.li@gmail.com>
 * @copyright   2012 ruli.
 * @license     http://www.opensource.org/licenses/mit-license.php  MIT License
 * @link        https://github.com/ruli/BeanBase
 * @version     1.0
 */

/**
 * The association code for ONE-TO-ONE relationship
 *
 * @package BeanBase
 * @subpackage Constants
 */
define( 'RB_HAS_ONE', 0 ); // ONE-TO-ONE

/**
 * The association code for ONE-TO-MANY relationship
 *
 * @package BeanBase
 * @subpackage Constants
 */
define( 'RB_HAS_MANY', 1 ); // ONE-TO-MANY

/**
 * The association code for MANY-TO-MANY relationship
 *
 * @package BeanBase
 * @subpackage Constants
 */
define( 'RB_HAVE_MANY', 2 ); // MANY-TO-MANY

/**
 * The association code for MANY-TO-ONE relationship
 *
 * @package BeanBase
 * @subpackage Constants
 */
define( 'RB_BELONGS_TO', 3 ); // MANY-TO-ONE

/**
 * BeanBase Exception
 *
 * @package BeanBase
 * @subpackage Exceptions
 */
class RbbException extends Exception {

  // ==================================================================
  //
  // Association Error Codes
  //
  // ------------------------------------------------------------------
  /**
   * ONE-TO-ONE Error
   *
   * @var integer
   */
  public static $ONE_TO_ONE   = 1;

  /**
   * ONE-TO-MANY Error
   *
   * @var integer
   */
  public static $ONE_TO_MANY  = 2;

  /**
   * MANY-TO-MANY Error
   *
   * @var integer
   */
  public static $MANY_TO_MANY = 4;

  /**
   * MANY-TO-ONE Error
   *
   * @var integer
   */
  public static $BELONGS_TO   = 8;

  // ==================================================================
  //
  // CRUD Error Codes
  //
  // ------------------------------------------------------------------
  /**
   * CREATE Error
   *
   * @var integer
   */
  public static $CREATE = 16;

  /**
   * READ Error
   *
   * @var integer
   */
  public static $READ   = 32;

  /**
   * UPDATE Error
   *
   * @var integer
   */
  public static $UPDATE = 64;

  /**
   * DELETE Error
   *
   * @var integer
   */
  public static $DELETE = 128;

  // ==================================================================
  //
  // Other Error Codes
  //
  // ------------------------------------------------------------------
  /**
   * Invalid Bean Error
   *
   * @var integer
   */
  public static $INVALID_BEAN = 256;

  /**
   * Incomplete Error
   *
   * @var integer
   */
  public static $INCOMPLETE = 512;

  /**
   * Unique Error
   * @var integer
   */
  public static $UNIQUE = 1024;

  /**
   * Unknown Error
   *
   * @var integer
   */
  public static $UNKNOWN = 65536;

  /**
   * Makes $message and $code required.
   *
   * @param string    $message  Error message
   * @param int       $code     Error Code
   * @param Exception $previous Previous error in the trace
   */
  public function __construct( $message, $code, Exception $previous = null ) {
    parent::__construct( $message, $code, $previous );
  }

}

/**
 *  RedBean Base Utility Class
 *
 * @package    BeanBase
 * @subpackage Utils
 */
class RBB {

  // ==================================================================
  //
  // Soft CURDs (avoids actual write to DB)
  //
  // ------------------------------------------------------------------
  /**
   * Create a new bean with given type, data and an optional filter.
   *
   * This method uses array_diff_key( $data, $filter ) to filter out unwanted keys mentioned in $filter
   *
   * @param  string $type   The bean type (table name) to be created
   * @param  mixed  $data   Data kv-array
   * @param  array  $filter Contains all the keys to be filtered out of $data
   *
   * @return RedBean_OODBBean The created bean
   */
  public static function create( $type, $data, $filter=array() ) {
    $bean = R::dispense( $type );

    if ( !empty($filter) ) {
      $data = array_diff_key( $data, $filter );
    }

    $bean = $bean->import( $data );

    if ( !isset($bean) ) {
      throw new RbbException( 'Cannot create bean', RbbException::$UNKNOWN );
    }

    return $bean;
  }

  /**
   * Read a bean with given ID and type
   *
   * @param  int    $id   The bean ID
   * @param  string $type The bean type
   *
   * @return RedBean_OODBBean The loaded bean
   *
   * @throws RbbException If a bean cannot be loaded
   */
  public static function read( $id, $type ) {
    $bean = R::load( $id, $type );

    if ( !$bean->id ) {
      $m = "Cannot find bean by ID = ".$id." in type = ".$type;

      throw new RbbException( $m, RbbException::$READ );
    }

    return $bean;
  }

  /**
   * Update a bean with given data and an optional filter
   *
   * This method uses array_diff_key( $data, $filter ) to filter out unwanted keys mentioned in $filter
   *
   * This method does not validate the fed in data ($data)
   *
   * @param  RedBean_OODBBean $bean   The bean to be updated
   * @param  mixed            $data   Data kv-array
   * @param  array            $filter Contains all the keys to be filtered out of $data
   *
   * @return RedBean_OODBBean         The updated bean
   *
   * @throws RbbException If a bean is not valid
   */
  public static function update( $bean, $data, $filter ) {
    if ( !isset($bean) || (!$bean instanceof RedBean_OODBBean) ) {
      $m = "Bean not valid";

      throw new RbbException( $m, RbbException::$INVALID_BEAN );
    }

    $data = array_diff_key( $data, $filter );

    return $bean->import( $data );
  }

  // ==================================================================
  //
  // Relational Operations
  //
  // ------------------------------------------------------------------

  public static function relate( $bean, $data, $filter ) {
    foreach ( $filter as $rel_type => $code ) {
      $id_key = $rel_type.'_id';

      if ( array_key_exists($id_key, $data) ) {
        $rel_bean = self::read( $data[$id_key] );

        self::associate( $bean, $rel_bean, $code );
      }
    }
  }

  /**
   * Associate bean and rel_bean. This method writes back to DB after association has been established.
   *
   * @todo   Fully implement all associations mentioned in http://www.redbeanphp.com/manual/docs/connectingbeans01
   *
   * @param  RedBean_OODBBean  $bean     The bean (the pivot)
   * @param  RedBean_OODBBean  $rel_bean The bean to be associated
   * @param  const             $code     The constant representing one of the defined relationship codes
   *
   * @throws RbbException
   */
  public static function associcate( $bean, $rel_bean, $code) {
    if ( !isset($bean, $rel_bean) || (!$bean instanceof RedBean_OODBBean) || (!rel_bean instanceof RedBean_OODBBean) ) {
      throw new RbbException( 'Bean not valid', RbbException::$INVALID_BEAN );
    }

    switch ( $code ) {
      case RB_HAS_ONE: // ONE-TO-ONE: http://redbeanphp.com/manual/association_api
        if ( !isset(R::relatedOne($bean, $rel_bean->_type)) or !isset( R::relatedOne($rel_bean, $bean->_type)) ) {
          throw new RbbException( 'Relationship already exists with one or both of beans', RbbException::$ONE_TO_ONE );
        }

        R::associate( $bean, $rel_bean );

        break;
      case RB_HAS_MANY: // ONE-TO-MANY: http://redbeanphp.com/manual/adding_lists
        $own_phrase = 'own'.ucfirst( $rel_bean->_type );

        // An ugly work around of not being able to use $array[] = new_entry
        // TODO: Need a better solution
        if ( isset($bean->$own_phrase) ) {
          if ( in_array($rel_bean, $bean->$own_phrase) ) {
            throw new RbbException( 'Relationship already established', RbbException::$ONE_TO_MANY );
          }

          array_push( $bean->$own_phrase, $rel_bean );
        } else {
          $bean->$own_phrase = array( $rel_bean );
        }

        R::store( $bean );

        break;
      case RB_HAVE_MANY: // MANY-TO-MANY: http://redbeanphp.com/manual/association_api
        if ( R::areRelated( $bean, $rel_bean ) ) {
          throw new RbbException( 'Two beans already associated', RbbException::$MANY_TO_MANY );
        }

        R::associate( $bean, $rel_bean );

        break;
      case RB_BELONGS_TO: // MANY-TO-ONE: reversed ONE-TO-MANY
        $rel_type = $rel_bean->_type;

        if ( isset($bean->$rel_type) ) {
          throw new RbbException( 'Parent already exists', RbbException::$BELONGS_TO );
        }

        $own_phrase = 'own'.ucfirst( $bean->_type );

        if ( isset($rel_bean->$own_phrase) ) {
          if ( in_array($bean, $rel_bean->$own_phrase) ) {
            throw new RbbException( 'Relationship already established', RbbException::$BELONGS_TO );
          }

          array_push( $rel_bean->$own_phrase, $bean );
        } else {
          $rel_bean->$own_phrase = array( $bean );
        }

        R::store( $rel_bean );

        break;
      default:
        throw new RbbException( 'Unknown error when trying to establish relationship between beans', RbbException::$UNKNOWN );
    }
  }

  // ==================================================================
  //
  // Other Utils
  //
  // ------------------------------------------------------------------
  /**
   * Checks whether a given data array is complete, against a key array filter
   *
   * @param  mixed  $data   Data kv-array
   * @param  array  $filter Key array that contains all the required keys
   */
  public static function completeness_check( $data, $filter ) {
    foreach ( $filter as $key ) {
      if ( !isset($data[$key]) || empty($data[$key]) ) {
        throw new RbbException( 'Missing '.$key.' from given data', RbbException::$INCOMPLETE );
      }
    }
  }

  /**
   * Checks whether a bean is unique, against a key array filter
   *
   * Make sure to use this before saving the bean into the database
   *
   * @param  RedBean_OODBBean  $bean   The bean to be checked
   * @param  array             $filter Key array that contains all the unique fields to be verified
   */
  public static function uniqueness_check( $data, $type, $filter ) {
    $filtered = self::strip_data( $data, $filter );

    foreach ($filtered as $key => $value) {
      $verify = R::findOne( $type, $key.'=?', array($value) );

      if ( isset($verify) ) {
        throw new RbbException( 'A bean already exists with '.$key.' = '.$value, RbbException::$UNIQUE );
      }
    }
  }

  /**
   * Strip a data array against a filter
   *
   * @param  mixed $data   Data kv-array
   * @param  array $filter Key array that contains the only keys needed from $data
   * @return mixed         Filtered kv-array with no kv's that are not mentioned by $filter
   */
  public static function strip_data( $data, $filter ) {
    $filtered = array();

    foreach ($filter as $key ) {
      if ( array_key_exists($key, $data) ) {
        $filtered[$key] = $data[$key];
      }
    }

    return $filtered;
  }

}

/**
 *  A base model to work with
 *
 * @package BeanBase
 * @subpackage Models
 */
class BaseModel {

  /**
   * The bean type name
   *
   * @var string Bean type
   */
  protected $_type = "";

  /**
   * The primary key(s) of a bean type
   *
   * @var array Primary Key(s)
   */
  protected $_pk = array( 'id' );


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

  // ==================================================================
  //
  // Restrictional Definitions
  //
  // ------------------------------------------------------------------
  /**
   * Reserved fields which (normally) should not be altered
   * Default reserved fields:
   *   _deleted - flag indicating whether the bean is 'soft' deleted
   *   _created - timestamp indicating when bean is created
   *   _updated - timestamp indicating when bean is updated
   *   _type    - bean type name for access convenience
   *
   * @var array String keys represent reserved fields
   */
  protected $_reserved_fields = array(
    '_deleted', // only be altered by delete() and recover()
    '_created', // only be altered by post()
    '_updated', // only be altered by update()
    '_type'     // only be altered by post()
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

  // ==================================================================
  //
  // CRUDs
  //
  // ------------------------------------------------------------------

  public function post( $request_data ) {
    // Check whether the data is complete
    if ( !empty($this->_post_fields) ) {
      RBB::completeness_check( $request_data, $this->_post_fields );
    }

    // Check whether the data is unique
    if ( !empty($this->_unique_fields) ) {
      RBB::uniqueness_check( $request_data, $this->_type, $this->_unique_fields );
    }

    // Create bean
    $bean = RBB::create( $this->_type, $request_data );

    // Process association filter if applicable
    if ( !empty($this->_asso_filter) ) {
      RBB::relate( $bean, $request_data, $this->_asso_filter );
    } else {
      R::store( $bean );
    }

    // Return created bean
    return $bean;
  }

  public function get( $id ) {
    return RBB::read( $id, $this->_type );
  }

  public function put( $id, $request_data ) {
    // Check whether the request data is complete
    if ( !empty($this->_put_fields) ) {
      RBB::completeness_check( $request_data, $this->_post_fields );
    }

    // Check whether the data is unique
    if ( !empty($this->_unique_fields) ) {
      RBB::uniqueness_check( $request_data, $this->_type, $this->_unique_fields );
    }

    $bean = $this->get( $id );

    // Process association filter if applicable
    if ( !empty($this->_asso_filter) ) {
      RBB::relate( $bean, $request_data, $this->_asso_filter );
    } else {
      R::store( $bean );
    }

    // Return created bean
    return $bean;
  }

  public function delete( $id ) {
    $bean = RBB::read( $id, $this->_type );

    R::trash( $bean );
  }

  // ==================================================================
  //
  // Count, Batch, Query
  //
  // ------------------------------------------------------------------


}