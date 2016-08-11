<?php

namespace NilPortugues\Tests\Api\Dummy;

use NilPortugues\Api\Mappings\HalMapping;
use NilPortugues\Api\Mappings\JsonApiMapping;
use NilPortugues\Tests\Api\Dummy\ComplexObject\Post;

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
        return 'Message';
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
            'self' => 'http://example.com/posts/{postId}',
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

    /**
     * Returns an array of properties that are mandatory to be passed in when doing create or update.
     *
     * @return array
     */
    public function getRequiredProperties()
    {
        return [];
    }
}
