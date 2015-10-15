<?php

namespace NilPortugues\Api\Mappings;

interface HalJsonMapping extends ApiMapping
{
    /**
     * Returns an array of curies.
     *
     * @return array
     */
    public function getCuries();
}
