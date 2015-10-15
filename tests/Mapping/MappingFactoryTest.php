<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/27/15
 * Time: 8:33 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Tests\Api\Mapping;

use NilPortugues\Api\Mapping\MappingException;
use NilPortugues\Api\Mapping\MappingFactory;
use NilPortugues\Tests\Api\Dummy\ComplexObject\Post;
use NilPortugues\Tests\Api\Dummy\PostApiMapping;

class MappingFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItCanBuildMappingsFromClass()
    {
        $mapping = MappingFactory::fromClass(PostApiMapping::class);

        $this->assertEquals(Post::class, $mapping->getClassName());
        $this->assertEquals('message', $mapping->getClassAlias());
        $this->assertEquals(['title' => 'headline', 'content' => 'body'], $mapping->getAliasedProperties());
        $this->assertEquals(['comments'], $mapping->getHiddenProperties());
        $this->assertEquals(['postId'], $mapping->getIdProperties());
        $this->assertEquals('http://example.com/posts/{postId}', $mapping->getResourceUrl());
        $this->assertEquals('http://example.com/posts/{postId}/author', $mapping->getRelatedUrl('author'));
        $this->assertEquals('http://example.com/posts/{postId}/relationships/author', $mapping->getRelationshipSelfUrl('author'));
    }

    public function testItCanBuildMappingsFromClassWillThrowExceptionIfAClassIsNotProvided()
    {
        $this->setExpectedException(MappingException::class);
        MappingFactory::fromClass('NotAClass');
    }

    public function testItCanBuildMappingsFromClassWillThrowExceptionIfClassDoesImplementApiMappingInterface()
    {
        $this->setExpectedException(MappingException::class);
        MappingFactory::fromClass('\DateTime');
    }

    public function testItCanBuildMappingsFromArray()
    {
        $mappedClass = [
            'class' => Post::class,
            'alias' => 'Message',
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
                'self' => 'http://example.com/posts/{postId}',
            ],
            'relationships' => [
                'author' => [
                    'related' => 'http://example.com/posts/{postId}/author',
                    'self' => 'http://example.com/posts/{postId}/relationships/author',
                ],
            ],
        ];

        $mapping = MappingFactory::fromArray($mappedClass);

        $this->assertEquals(Post::class, $mapping->getClassName());
        $this->assertEquals('message', $mapping->getClassAlias());
        $this->assertEquals(['title' => 'headline', 'content' => 'body'], $mapping->getAliasedProperties());
        $this->assertEquals(['comments'], $mapping->getHiddenProperties());
        $this->assertEquals(['postId'], $mapping->getIdProperties());
        $this->assertEquals('http://example.com/posts/{postId}', $mapping->getResourceUrl());
        $this->assertEquals('http://example.com/posts/{postId}/author', $mapping->getRelatedUrl('author'));
        $this->assertEquals('http://example.com/posts/{postId}/relationships/author', $mapping->getRelationshipSelfUrl('author'));
    }

    public function testItCanBuildMappingsFromArrayWillThrowExceptionIfAliasPropertyDoesNotExist()
    {
        $mappedClass = [
            'class' => Post::class,
            'alias' => 'Message',
            'aliased_properties' => [
                'I_do_not_exist' => 'headline',
                'content' => 'body',
            ],
            'hide_properties' => [
                'comments',
            ],
            'id_properties' => [
                'postId',
            ],
            'urls' => [
                'self' => 'http://example.com/posts/{postId}',
            ],
            'relationships' => [
                'author' => [
                    'related' => 'http://example.com/posts/{postId}/author',
                    'self' => 'http://example.com/posts/{postId}/relationships/author',
                ],
            ],
        ];

        $this->setExpectedException(MappingException::class);
        MappingFactory::fromArray($mappedClass);
    }

    public function testItCanBuildMappingsFromArrayWillThrowExceptionIfHidePropertyDoesNotExist()
    {
        $mappedClass = [
            'class' => Post::class,
            'alias' => 'Message',
            'aliased_properties' => [
                'title' => 'headline',
                'content' => 'body',
            ],
            'hide_properties' => [
                'I_do_not_exist',
            ],
            'id_properties' => [
                'postId',
            ],
            'urls' => [
                'self' => 'http://example.com/posts/{postId}',
            ],
            'relationships' => [
                'author' => [
                    'related' => 'http://example.com/posts/{postId}/author',
                    'self' => 'http://example.com/posts/{postId}/relationships/author',
                ],
            ],
        ];

        $this->setExpectedException(MappingException::class);
        MappingFactory::fromArray($mappedClass);
    }

    public function testItCanBuildMappingsFromArrayWillThrowExceptionIfRelationshipPropertyDoesNotExist()
    {
        $mappedClass = [
            'class' => Post::class,
            'alias' => 'Message',
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
                'self' => 'http://example.com/posts/{postId}',
            ],
            'relationships' => [
                'I_do_not_exist' => [
                    'related' => 'http://example.com/posts/{postId}/author',
                    'self' => 'http://example.com/posts/{postId}/relationships/author',
                ],
            ],
        ];

        $this->setExpectedException(MappingException::class);
        MappingFactory::fromArray($mappedClass);
    }

    public function testItWillThrowExceptionIfArrayHasNoClassKey()
    {
        $this->setExpectedException(MappingException::class);
        $mappedClass = [];
        MappingFactory::fromArray($mappedClass);
    }

    public function testItWillThrowExceptionIfArrayHasNoSelfUrlKey()
    {
        $this->setExpectedException(MappingException::class);
        $mappedClass = ['class' => Post::class, 'id_properties' => ['postId'], 'urls' => []];
        MappingFactory::fromArray($mappedClass);
    }
}
