<?php

namespace NilPortugues\Api\Mappings;

interface JsonApiMapping
{
    /**
     * Returns an array containing the relationship mappings as an array.
     * Key for each relationship defined must match a property of the mapped class.
     *
     * @return array
     */
    public function getRelationships();
}
