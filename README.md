BeanBase
========

A way to start using [RedBean PHP ORM](http://redbeanphp.com/)

> Notice: BeanBase requires [rb.php](http://redbeanphp.com/) but does not ship with it, so grab it now!

* Based on [RedBean PHP ORM](http://redbeanphp.com/)
* Aims to make RedBean even easier to use with BeanBase utility class
* Offers a BaseModel to start modeling your data with ease. Unlike FUSE (that's bundled with RedBean), this BaseModel is rather old fashioned which could be used in almost all PHP MVC frameworks (see [Integrations](#integrations)). Though you're definitely encouraged to checkout [FUSE](http://redbeanphp.com/manual/models_and_fuse) which is really neat and powerful.

Components
==========

* RBB: utility class that wraps on top of RedBean
* BaseModel: a base model that offers out of the box CRUD operations, relational operations, and other utilities to help with your DB interaction needs, by leveraging the power of RBB.

Integrations
============

* CI (CodeIgniter) at [BeanBase-CI](https://github.com/ruli/BeanBase-CI)

Testing
=======

Testing are done through [SimpleTest](http://www.simpletest.org/). Tests don't cover RedBean itself.

Things To Be Done
=================

* Travis-CI
* [Restler](http://luracast.com/products/restler/) integration
* Examples and Manual