Tests
=====

> Notice: although the test suite comes with an rb.php, it does not mean that you should thoroughly rely on this version of RedBean.
> The better place to grab it is still at [RedBean Official Site](http://redbeanphp.com)

The tests are written in [SimpleTest framework](http://www.simpletest.org/).

Coverage Updates
----------------

* RBB - 90%
    * Lacking RBB::set_unique() test due to an issue with RedBean 3.3, [Discussion](https://groups.google.com/forum/?fromgroups=#!topic/redbeanorm/ysQejl2SWD4)
* BaseModel - 90%
    * Due to the same reason for RBB above