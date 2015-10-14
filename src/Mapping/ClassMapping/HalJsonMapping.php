<?php

namespace NilPortugues\Api\Mapping\ClassMapping;

interface HalJsonMapping extends ApiMappingInterface
{
    /**
     * Returns an array of curies.
     *
     * @return array
     */
    public function getCuries();
}
