BeanBase
========

A way to start using RedBean PHP ORM

Components
========

* A Base Model (models\base.php): Aims to be a very generic model to be extended.

* An Utility Class (utils\beanbase.php): So far this only includes operations that wrap around R::someThing() for convenience (to certain extent). Ultimately the goal is to have this utility class as *the* BeanBase that includes things that RedBean PHP doesn't offer, such as Query Builder to generate SQL strings to be used with R::exec().

Others
========

License, documentation and all other stuff will be available as soon as I get Unit Tests done along with detailed usage example.

However, don't worry about license, it's going to be one of those "please use at your own risk, for free" licenses.