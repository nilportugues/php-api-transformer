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
    public function getClass() : string
    {
        return Post::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias() : string
    {
        return 'Message';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasedProperties() : array
    {
        return [
            'title' => 'headline',
            'content' => 'body',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getHideProperties() : array
    {
        return [
            'comments',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdProperties() : array
    {
        return [
            'postId',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls() : array
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
    public function getCuries() : array
    {
        return [
            'name' => 'example',
            'href' => 'http://example.com/docs/rels/{rel}',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships() : array
    {
        return [
            'author' => [
                'related' => 'http://example.com/posts/{postId}/author',
                'self' => 'http://example.com/posts/{postId}/relationships/author',
            ],
        ];
    }
}
