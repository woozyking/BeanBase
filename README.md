# BeanBase

> *Best effort* smoke testing using Travis-CI (see my travis yaml file for environment setup details): [![Build Status](https://secure.travis-ci.org/woozyking/BeanBase.png)](http://travis-ci.org/woozyking/BeanBase)

The one purpose of creating BeanBase is to make [RedBeanPHP: Powerful ORM](http://redbeanphp.com/) __even easier__ to use...[Skip to wiki (API docs and manual)](https://github.com/ruli/BeanBase/wiki)

A slight taste with some use cases:

> 1. The user should be able to create a category, and some articles.
> 2. The user should be able to assign any number of articles under a category.
> 3. The user should be able to fetch all articles that belongs to the same category.

Quick Implementation:
```php
// Mock data for articles
$art_data_1 = array(
  'title' => 'Article 1',
  'body' => 'The content of this article...'
);

$art_data_2 = array(
  'title' => 'Article 2',
  'body' => 'The content of another article...'
);

// Create the article
$art_bean_1 = RBB::create( 'article', $art_data_1 );
$art_bean_2 = RBB::create( 'article', $art_data_2 );

// Mock data for a category
$cat_data = array(
  'name' => 'Category 1'
);

// Create the category
$cat_bean = RBB::create( 'category', $cat_data );

// Establish association, we decide that the relation is
// ONE-TO-MANY, Which means one category owns many articles
// or many articles belong to one category
// RBB::associate() stores beans as well
RBB::associate( $art_bean_1, RBB::RB_BELONGS_TO, $cat_bean );
RBB::associate( $cat_bean, RBB::RB_HAS_MANY, $art_bean_2 );

// Fetch articles by given category
$articles = RBB::get_related( $cat_bean, RBB::RB_HAS_MANY, 'article' );

echo "<pre>";
var_dump( $articles ); // which will result in an array of article bean objects
echo "</pre>";
```

Ready? [Dive in!](https://github.com/ruli/BeanBase/wiki)
