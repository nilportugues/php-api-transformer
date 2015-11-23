<?php

namespace NilPortugues\Api\Mappings;

interface HalMapping extends ApiMapping
{
    /**
     * Returns an array of curies.
     *
     * @return array
     */
    public function getCuries();
}
