BeanBase
========

A way to start using RedBean PHP ORM

Components
==========

* RBB: An utility class that wraps on top of RedBean PHP. Aims to provide convenient methods to operate beans and their relationships.
* BaseModel: Aims to be a very generic model to be extended. Leverages the power of RBB and RedBean PHP ORM.

Integrations
============

CI (CodeIgniter)
----------------

> Notice: you must place [rb.php](http://redbeanphp.com/) in `CI/application/third_party` directory for BeanBase to work.

A way to start using RedBean (and BeanBase) with CodeIgniter Framework. Integration method provided by [Boris Strahija](http://creolab.hr/2011/02/redbean-codeigniter-take-2/).

What you'll get is out of the box RedBean and BeanBase support (R::xyz and RBB::abc) in your CI applications, as well as the masked BaseModel (renamed to MY_Model to fit CI standard) to simplify the creation of your CI models. At a glance:
```php
// A Something Model
class Something_model extends MY_Model {

  // The only required property to be overridden
  protected $_type = "something";

  // The rest could be blank and use as is
  // or add/override with your own sauce
}

// The Something Controller
class Something extends CI_Controller {

  public function __construct() {
    parent::__construct();

    // To save trees, alias Something_model as pm in this controller
    $this->load->model( 'Something_model', 's' );
  }

  public function new() {
    // Get user request data
    // Suppose it has everything we need
    // and nothing we don't
    $request_data = $this->input->post();

    // Create and store the bean with $request_data
    // Side notes: instead of create(), BaseModel CRUD methods embrace HTTP verbs
    $bean = $this->s->post( $request_data );

    // Prepare feedback data to be presented in view
    $wanted_filter = array( /* Keys that we're looking for to be kept for view */ );
    $feedback_data = $bean->export(); // export bean as an array
    $feedback_data = RBB::strip_data( $feedback_data, $wanted_filter ); // strip out unwanted and keep the wanted

    // Serve it with the (supposedly-already made) view
    $this->load->view('Something_view', $feedback_data);
  }

}
```

If you're looking for a more (CI) native solution in terms of a base model, please check out jamierumbelow's [codeigniter-base-model](https://github.com/jamierumbelow/codeigniter-base-model), which uses CI's Active Record class to its greatest extent.

Things To Be Done
=================

* Unit test
* Travis-CI
* Examples and Manual