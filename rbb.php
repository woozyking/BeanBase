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

interface BeanBase_Const {

  const VERSION = "1.0";

}

/**
 * Relation constants
 *
 * @package BeanBase
 * @subpackage Constants
 */
interface BeanBase_Const_Relation {

  // Relation Codes
  const RB_HAS_ONE    = "simple_has_one";
  const RB_HAS_MANY   = "simple_has_many";
  const RB_HAVE_MANY  = "simple_have_many";
  const RB_BELONGS_TO = "simple_belongs_to";
  const RB_HAS_ONE_SELF    = "self_has_one";
  const RB_HAS_MANY_SELF   = "self_has_many";
  const RB_HAVE_MANY_SELF  = "self_have_many";
  const RB_BELONGS_TO_SELF = "self_belongs_to";

  // Self Relation Constants
  const RB_SELF_REF = "referential";

}

/**
 * CRUD constants
 *
 * @package BeanBase
 * @subpackage Constants
 */
interface BeanBase_Const_CRUD {

  const RB_CREATED = "created";
  const RB_UPDATED = "updated";
  const RB_DELETED = "deleted";
  const RB_RELATION = "relation";

}

/**
 * The BeanBase utility class
 *
 * @package BeanBase
 * @subpackage Util
 */
class RBB implements BeanBase_Const_Relation, BeanBase_Const_CRUD, BeanBase_Const {

  // ==================================================================
  //
  // Soft CURDs (avoids actual write to DB)
  //
  // ------------------------------------------------------------------
  /**
   * Create a new bean with given type, data
   *
   * @param  string $type   The bean type (table name) to be created
   * @param  array  $data   Data kv-array, default: null
   *
   * @return RedBean_OODBBean The created bean
   */
  public static function create( $type, array $data=null ) {
    $bean = R::dispense( $type );

    if ( empty($data) ) {
      return $bean;
    }

    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    $bean = $bean->import( $data );

    return $bean;
  }

  /**
   * Read a bean with given ID and type
   *
   * @param  integer  $id   The bean ID
   * @param  string   $type The bean type
   *
   * @return RedBean_OODBBean The loaded bean
   */
  public static function read( $id, $type ) {
    $bean = R::load( $type, $id);

    if ( !$bean->id ) {
      $m = "Cannot find bean by ID = ".$id." in type = ".$type;

      throw new BeanBase_Exception_CRUD( $m, BeanBase_Exception_CRUD::READ );
    }

    return $bean;
  }

  /**
   * Update a bean with optional data
   *
   * This method does not validate the fed in data ($data)
   *
   * @param  RedBean_OODBBean $bean   The bean to be updated
   * @param  array            $data   Data kv-array
   *
   * @return RedBean_OODBBean         The updated bean
   */
  public static function update( RedBean_OODBBean $bean, array $data=null ) {
    if ( empty($data) ) {
      return $bean;
    }

    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    $bean->import( $data );

    return $bean;
  }

  // ==================================================================
  //
  // Relational Operations
  //
  // ------------------------------------------------------------------
  /**
   * Relate a bean with potential rel_bean contained in data, and a given filter.
   *
   * Since RBB::associate() method writes back to DB, this method would do it too.
   *
   * @param  RedBean_OODBBean $bean   The bean
   * @param  array            $data   The data
   * @param  array            $filter KV array with a format of rel_type => code
   */
  public static function relate( RedBean_OODBBean $bean, array $data, array $filter ) {
    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    if ( !self::is_assoc($filter) ) {
      throw new InvalidArgumentException( 'Filter array must be associative' );
    }

    foreach ( $filter as $type => $code ) {
      $key = $type."_id";

      if ( isset($data[$key]) || array_key_exists($key, $data) ) {
        if ( is_array($data[$key]) ) {
          foreach ( $data[$key] as $id ) {
            $rel = self::read( $id, $type );

            self::associate( $bean, $code, $rel );
          }
        } else {
          $rel = self::read( $data[$key], $type );

          self::associate( $bean, $code, $rel );
        }
      }
    }
  }

  /**
   * Associate bean and rel_bean. This method writes back to DB after association has been established.
   *
   * @todo   RB_HAVE_MANY_SELF implementation
   * @todo   Fully implement all associations mentioned in http://www.redbeanphp.com/manual/docs/connectingbeans01
   *
   * @param  RedBean_OODBBean  $bean     The bean
   * @param  const             $code     The constant representing one of the defined relationship codes
   * @param  RedBean_OODBBean  $rel_bean The rel_bean to be associated
   */
  public static function associate( RedBean_OODBBean $bean, $code, RedBean_OODBBean $rel_bean) {
    $bean_type = self::get_bean_type( $bean );
    $rel_type  = self::get_bean_type( $rel_bean );

    switch ( $code ) {
      case self::RB_HAS_ONE: // ONE-TO-ONE: http://redbeanphp.com/manual/association_api
        $v1 = R::relatedOne( $bean, $rel_type );
        $v2 = R::relatedOne( $rel_bean, $bean_type );

        if ( $v1 || $v2 ) {
          throw new BeanBase_Exception_Relation( 'Relationship already exists in either or both beans',
            BeanBase_Exception_Relation::HAS_ONE );
        }

        R::associate( $bean, $rel_bean );

        break;
      case self::RB_HAS_MANY: // ONE-TO-MANY: http://redbeanphp.com/manual/adding_lists
        if ( !$rel_bean->$bean_type ) {
          $rel_bean->$bean_type = $bean;
        } else {
          throw new BeanBase_Exception_Relation( 'Relationship already established',
              BeanBase_Exception_Relation::HAS_MANY );
        }

        break;
      case self::RB_HAVE_MANY: // MANY-TO-MANY: http://redbeanphp.com/manual/association_api
        if ( R::areRelated( $bean, $rel_bean ) ) {
          throw new BeanBase_Exception_Relation( 'Two beans already associated',
            BeanBase_Exception_Relation::HAVE_MANY );
        }

        R::associate( $bean, $rel_bean );

        break;
      case self::RB_BELONGS_TO: // MANY-TO-ONE: reversed ONE-TO-MANY
        if ( !$bean->$rel_type ) {
          $bean->$rel_type = $rel_bean;
        } else {
          throw new BeanBase_Exception_Relation( 'Parent already exists',
            BeanBase_Exception_Relation::BELONGS_TO );
        }

        break;
      case self::RB_HAS_ONE_SELF: // self ONE-TO-ONE
        if ( $bean_type != $rel_type ) {
          throw new BeanBase_Exception_Relation( 'Self Referential operations require both beans have the same type',
            BeanBase_Exception_Relation::RB_HAS_ONE_SELF );
        }

        // Very heavy checks
        $v1 = R::findOne( $bean_type, ' '.self::RB_SELF_REF.'_id = ? ', array($bean->id) );
        $v2 = $rel_bean->fetchAs($bean_type)->{self::RB_SELF_REF};
        $v3 = R::findOne( $bean_type, ' '.self::RB_SELF_REF.'_id = ? ', array($rel_bean->id) );
        $v4 = $bean->fetchAs($bean_type)->{self::RB_SELF_REF};

        if ( $v1 || $v2 || $v3 || $v4 ) {
          throw new BeanBase_Exception_Relation( 'Self reference already exist in either or both beans',
            BeanBase_Exception_Relation::HAS_ONE_SELF );
        }

        $rel_bean->{self::RB_SELF_REF} = $bean;

        break;
      case self::RB_HAS_MANY_SELF: // self ONE-TO-MANY
        if ( $bean_type != $rel_type ) {
          throw new BeanBase_Exception_Relation( 'Self Referential operations require both beans have the same type',
            BeanBase_Exception_Relation::HAS_MANY_SELF );
        }

        // See if parent already exist
        if ( $rel_bean->fetchAs($bean_type)->{self::RB_SELF_REF} ) {
          throw new BeanBase_Exception_Relation( 'Bean ID='.$rel_bean->id.' in Type='.$bean_type.' already has a self reference',
            BeanBase_Exception_Relation::HAS_MANY_SELF );
        }

        $rel_bean->{self::RB_SELF_REF} = $bean;

        break;
      case self::RB_HAVE_MANY_SELF:
        if ( $bean_type != $rel_type ) {
          throw new BeanBase_Exception_Relation( 'Self Referential operations require both beans have the same type',
            BeanBase_Exception_Relation::HAVE_MANY_SELF );
        }

        // TODO

        break;
      case self::RB_BELONGS_TO_SELF: // self MANY-TO-ONE
        if ( $bean_type != $rel_type ) {
          throw new BeanBase_Exception_Relation( 'Self Referential operations require both beans have the same type',
            BeanBase_Exception_Relation::BELONGS_TO_SELF );
        }

        // See if parent already exist
        if ( $bean->fetchAs($bean_type)->{self::RB_SELF_REF} ) {
          throw new BeanBase_Exception_Relation( 'Bean ID='.$bean->id.' in Type='.$bean_type.' already has a self parent',
            BeanBase_Exception_Relation::BELONGS_TO_SELF );
        }

        $bean->{self::RB_SELF_REF} = $rel_bean;

        break;
      default:
        throw new BeanBase_Exception_Relation( 'Unknown error when trying to establish relationship between beans',
          BeanBase_Exception_Relation::UNKNOWN );
    }

    if ( self::is_modified($bean) ) {
      R::store( $bean );
    }

    if ( self::is_modified($rel_bean) ) {
      R::store( $rel_bean );
    }
  }

  /**
   * Get related bean(s) with given target bean object, relation code, with optional related type and sql array
   *
   * @todo   RB_HAVE_MANY_SELF implementation
   *
   * @param  RedBean_OODBBean $bean     The target bean
   * @param  const            $code     The relation constant
   * @param  string           $rel_type Required for simple relations, not used for self referential relations. Default: null
   * @param  string           $query    Optional query used in RB_HAVE_MANY case. Default: null
   * @return mixed                      Returns a bean or an array of beans on success; null or empty array otherwise
   */
  public static function get_related( RedBean_OODBBean $bean, $code, $rel_type=null, $query=null ) {
    $bean_type = self::get_bean_type( $bean );

    switch ( $code ) {
      case self::RB_HAS_ONE: // could be null
        if ( !empty($rel_type) ) {
          return R::relatedOne( $bean, $rel_type );
        }

        return null;
      case self::RB_HAS_MANY: // could be empty array
        if ( !empty($rel_type) ) {
          $own = "own".ucfirst( $rel_type );

          return $bean->$own;
        }

        return array();
      case self::RB_HAVE_MANY: // could be empty array
        if ( !empty($rel_type) ) {
          if ( !empty($query) ) {
            return R::related( $bean, $rel_type, $query );
          }

          return R::related( $bean, $rel_type );
        }

        return array();
      case self::RB_BELONGS_TO: // could be null
        if ( !empty($rel_type) ) {
          return $bean->$rel_type;
        }

        return null;
      case self::RB_HAS_ONE_SELF: // could be null
        return R::findOne( $bean_type, ' '.self::RB_SELF_REF.'_id = ? ', array($bean->id) );
      case self::RB_HAS_MANY_SELF: // could be empty array
        return R::find( $bean_type, ' '.self::RB_SELF_REF.'_id = ? ', array($bean->id) );
      case self::RB_HAVE_MANY_SELF: // could be empty array
        // TODO

        return array();
      case self::RB_BELONGS_TO_SELF: // could be null
        return $bean->fetchAs($bean_type)->{self::RB_SELF_REF};
      default:
        return null;
    }
  }

  public static function get_rel_code( RedBean_OODBBean $bean, RedBean_OODBBean $rel_bean ) {
    // TODO

  }

  public static function break_relation( RedBean_OODBBean $bean, $code, RedBean_OODBBean $rel_bean ) {
    // TODO
  }

  // TODO, method to access by relationship
  // TODO, method to break relationships

  // ==================================================================
  //
  // Other Utils
  //
  // ------------------------------------------------------------------
  /**
   * Get the given bean's type (table name)
   *
   * @param  RedBean_OODBBean $bean The bean
   * @return string                 The type name
   */
  public static function get_bean_type( RedBean_OODBBean $bean ) {
    return $bean->getMeta( 'type' );
  }

  /**
   * Determines whether the bean is modified and unsaved
   * @todo  Implement before_store trigger somewhere
   *
   * @param  RedBean_OODBBean  $bean The bean
   * @return boolean           True if the bean is modified and unsaved; false otherwise
   */
  public static function is_modified( RedBean_OODBBean $bean ) {
    return $bean->getMeta( 'tainted' );
  }

  /**
   * Checks whether a given bean is complete, against a keys array
   *
   * @deprecated This is deprecated in favor of RBB::check_complete() method
   *             which throws a more specific exception message with which field(s)
   *             is missing
   *
   * @param  array   $data   The data kv-array
   * @param  array   $keys   The keys array
   *
   * @return boolean         True if the data is complete, otherwise false
   */
  public static function is_complete( array $data, array $keys ) {
    foreach ( $keys as $key ) {
      if ( !isset($data[$key]) || !array_key_exists($key, $data) ) {
        return false;
      }
    }

    return true;
  }

  public static function check_complete( array $data=null, array $keys ) {
    if ( empty($data) ) {
        throw new BeanBase_Exception( 'Data null or empty array', BeanBase_Exception::INCOMPLETE );
    }

    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    foreach ( $keys as $key ) {
      if ( !isset($data[$key]) || !array_key_exists($key, $data) ) {
        throw new BeanBase_Exception( '$key missing', BeanBase_Exception::INCOMPLETE );
      }
    }
  }

  /**
   * Filter out unwanted key-value pairs from $data, by a given array of unwanted keys
   *
   * Works the opposite way of keep_only()
   *
   * @param  array  $data Data kv-array
   * @param  array  $keys Key array that contains the keys that are to be removed from $data
   * @return array        Filtered kv-array
   */
  public static function strip_out( array $data, array $keys ) {
    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    foreach ( $keys as $key ) {
      if ( isset($data[$key]) || array_key_exists($key, $data) ) {
        unset( $data[$key] );
      }
    }

    return $data;
  }

  /**
   * Filter out unwanted from $data, by a given array of wanted keys
   *
   * Works the opposite way of strip_out()
   *
   * @param  array $data   Data kv-array
   * @param  array $keys   Key array that contains the only keys needed from $data
   * @return array         Filtered kv-array
   */
  public static function keep_only( array $data, array $keys ) {
    if ( !self::is_assoc($data) ) {
      throw new InvalidArgumentException( 'Data array must be associative' );
    }

    $filtered = array();

    foreach ( $keys as $key ) {
      if ( isset($data[$key]) || array_key_exists($key, $data) ) {
        $filtered[$key] = $data[$key];
      }
    }

    return $filtered;
  }

  /**
   * Set a timestamp to desired bean property
   *
   * @param  RedBean_OODBBean $bean        The bean
   * @param  string           $property    The property of the bean
   * @param  DateTime         $timestamp   DateTime object
   * @param  string           $time_format Time format, default: 'Y-m-d H:i:s'
   *
   * @return RedBean_OODBBean              The modified bean
   */
  public static function insert_timestamp( RedBean_OODBBean $bean, $property, DateTime $timestamp=null, $time_format='Y-m-d H:i:s' ) {
    if ( !is_string($property) ) {
      throw new InvalidArgumentException( 'Timestamp property must be a string' );
    }

    if ( null === $timestamp ) {
      $timestamp = new DateTime( 'now' );
    }

    $bean->$property = $timestamp->format( $time_format );

    return $bean;
  }

  /**
   * Set unique meta for the given bean
   *
   * @deprecated Due to its many restrictions, this is deprecated
   *             in favor of RBB::check_unique()
   *
   * @param RedBean_OODBBean $bean   The Bean
   * @param array            $unique Array with unique field keys
   */
  public static function set_unique( RedBean_OODBBean $bean, array $unique ) {
    $bean->setMeta( 'buildcommand.unique', array($unique) ) ;

    return $bean;
  }

  // public static function check_unique( $type, array $data=null ) {
  //   if ( empty($data) ||  )

  //   foreach ( $data as $key => $val ) {
  //     $bean = R::findOne( $type, ' ' )
  //   }
  // }

  /**
   * Checks if an array is associative (string keys) or sequential (numeric keys)
   *
   * Found at http://stackoverflow.com/a/4254008
   *
   * @param  array   $array The array to be checked
   *
   * @return boolean        True if this is associative, otherwise false
   */
  public static function is_assoc( array $array ) {
    return (bool)count( array_filter(array_keys($array), 'is_string') );
  }

}

/**
 * BeanBase Base Exception
 *
 * @package BeanBase
 * @subpackage Exception
 */
class BeanBase_Exception extends Exception {

  const INCOMPLETE = "INCOMPLETE";
  const UNIQUE     = "UNIQUE";
  const UNKNOWN    = "UNKNOWN";

  /**
   * Override default constructor that makes message required
   * and adding a type string
   *
   * @param string     $message  The error message
   * @param string     $type     Specific error type
   * @param integer    $code     Error code, default: 0
   * @param Exception  $previous Previous exception trace, default: null
   */
  public function __construct( $message, $type, $code=0, Exception $previous=null ) {
    parent::__construct( "(Error Type - {$type}) {$message}", $code, $previous );
  }

  /**
   * Override default __toString() magic method. Hides
   * unecessary information
   *
   * @return string The custom exception string
   */
  public function __toString() {
    return get_called_class().": ".$this->message;
  }

}

/**
 * BeanBase CRUD Exception
 *
 * @package BeanBase
 * @subpackage Exception
 */
class BeanBase_Exception_CRUD extends BeanBase_Exception {

  const CREATE = "CREATE";
  const READ   = "READ";
  const UPDATE = "UPDATE";
  const DELETE = "DELETE";

}

/**
 * BeanBase Relation Exception
 *
 * @package BeanBase
 * @subpackage Exception
 */
class BeanBase_Exception_Relation extends BeanBase_Exception {

  const HAS_ONE    = "ONE-TO-ONE";
  const HAS_MANY   = "ONE-TO-MANY";
  const HAVE_MANY  = "MANY-TO-MANY";
  const BELONGS_TO = "BELONGS-TO";
  const HAS_ONE_SELF    = "SELF-ONE-TO-ONE";
  const HAS_MANY_SELF   = "SELF-ONE-TO-MANY";
  const HAVE_MANY_SELF  = "SELF-MANY-TO-MANY";
  const BELONGS_TO_SELF = "SELF-BELONGS-TO";

}
