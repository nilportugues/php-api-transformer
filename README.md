# API Transformer

[![Build Status]
(https://travis-ci.org/nilportugues/php-api-transformer.svg)]
(https://travis-ci.org/nilportugues/php-api-transformer) 
[![Scrutinizer Code Quality]
(https://scrutinizer-ci.com/g/nilportugues/api-transformer/badges/quality-score.png?b=master)]
(https://scrutinizer-ci.com/g/nilportugues/api-transformer/?branch=master)
[![SensioLabsInsight]
(https://insight.sensiolabs.com/projects/b4e5056d-c552-407e-ae21-2da685e07c06/mini.png)]
(https://insight.sensiolabs.com/projects/b4e5056d-c552-407e-ae21-2da685e07c06)
[![Latest Stable Version]
(https://poser.pugx.org/nilportugues/api-transformer/v/stable)]
(https://packagist.org/packages/nilportugues/api-transformer) 
[![Total Downloads]
(https://poser.pugx.org/nilportugues/api-transformer/downloads)]
(https://packagist.org/packages/nilportugues/api-transformer)
[![License]
(https://poser.pugx.org/nilportugues/api-transformer/license)]
(https://packagist.org/packages/nilportugues/api-transformer) 
[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://paypal.me/nilportugues)
## Purpose
This library provides the core functionality for API transformation and is a base library for many other packages. 

By itself it's not usable at all. Check the projects using it below.

## Used by

Currently the following transformers make use of this library as foundation:

- [nilportugues/json](https://github.com/nilportugues/json-transformer)
- [nilportugues/jsend](https://github.com/nilportugues/jsend-transformer)
- [nilportugues/haljson](https://github.com/nilportugues/hal-json-transformer)
- [nilportugues/json-api](https://github.com/nilportugues/jsonapi-transformer)




## Installation

Use [Composer](https://getcomposer.org) to install the package:

```json
$ composer require nilportugues/api-transformer
```


## How it works

Loading must be done using the `Mapper` class, as it expects an array containing a defined structure, or a class name implementing `ApiMapping`. 

There are 2 styles for flexibility when integrating the library into PHP frameworks. 

While I discourage having 2 styles for mapping it is well possible to have them side by side in the very same configuration file. The `Mapper` class that loads all Mappings does internal transformation for both, so client does not have to worry. 

### Usage

```php
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\AcmeProject\Infrastructure\Api\Mappings\PostApiMapping;

$arrayConfig = include 'mappings.php';
$classConfig = [
	PostApiMapping::class,
];

$mappings = array_merge($classConfig, $arrayConfig);

//Now $mapper can be passed to a Transformer.
$mapper = new Mapper($mappings);
```

<br>

## Creating the Mapping files

### Implementing ApiMapping (Prefered method)

To create a Mapping you may implement the following interfaces:

- `ApiMapping`: transform your data into plain JSON for API consumtion or the JSend API format.
- `JsonApiMapping` to transform your data into the JSONAPI 1.0 standard.
- `HalMapping` to transform your data to HAL+JSON and HAL+XML API standards.


As expected you may implement many interfaces to support multiple API formats.


```php
<?php

namespace NilPortugues\AcmeProject\Infrastructure\Api\Mappings;

use NilPortugues\AcmeProject\Blog\Domain\Post;
use NilPortugues\Api\Mappings\HalMapping;
use NilPortugues\Api\Mappings\JsonApiMapping;

class PostApiMapping implements JsonApiMapping, HalMapping
{
    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return Post::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'Posting'; //If none is used 'Post' will be used instead.
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasedProperties()
    {
        return [
            'title' => 'headline',
            'content' => 'body',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getHideProperties()
    {
        return [
           'comments',
       ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdProperties()
    {
        return [
            'postId',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls()
    {
        return [
          // Mandatory
          'self' => 'http://example.com/posts/{postId}',
          // Optional
          'comments' => 'http://example.com/posts/{postId}/comments',
        ];
    }

    /**
     * Returns an array of curies.
     *
     * @return array
     */
    public function getCuries()
    {
        return [
            'name' => 'example',
            'href' => 'http://example.com/docs/rels/{rel}',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships()
    {
        return [
            'author' => [
                'related' => 'http://example.com/posts/{postId}/author',
                'self' => 'http://example.com/posts/{postId}/relationships/author',
            ],
        ];
    }
}

```

### Mapping using an array

```php
// mappings.php

return  [
  [
      'class' => Post::class,
      'alias' => 'Posting', //If none is used 'Post' will be used instead.
      'aliased_properties' => [
          'title' => 'headline',
          'content' => 'body',
      ],
      'hide_properties' => [
		  'comments',
      ],
      'id_properties' => [
          'postId',
      ],
      'urls' => [
          // Mandatory
          'self' => 'http://example.com/posts/{postId}',
          // Optional
          'comments' => 'http://example.com/posts/{postId}/comments',
      ],
      // (Optional) Used by HAL+JSON / HAL+XML
      'curies' => [
          'name' => 'example',
          'href' => 'http://example.com/docs/rels/{rel}',
      ],
      // (Optional) Used by JSONAPI
      'relationships' => [
          'author' => [
            'related' => 'http://example.com/posts/{postId}/author',
            'self' => 'http://example.com/posts/{postId}/relationships/author',
          ],
      ]
  ],
];
```

## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/api-transformer/pulls).



## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/api-transformer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/api-transformer).



## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/api-transformer/issues/new)



## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/api-transformer/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
