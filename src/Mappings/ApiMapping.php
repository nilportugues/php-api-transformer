<?php

namespace NilPortugues\Api\Mappings;

interface ApiMapping
{
    /**
     * Returns a string with the full class name, including namespace.
     *
     * @return string
     */
    public function getClass();

    /**
     * Returns a string representing the resource name as it will be shown after the mapping.
     *
     * @return string
     */
    public function getAlias();

    /**
     * Returns an array of properties that will be renamed.
     * Key is current property from the class. Value is the property's alias name.
     *
     * @return array
     */
    public function getAliasedProperties();

    /**
     * List of properties in the class that will be ignored by the mapping.
     *
     * @return array
     */
    public function getHideProperties();

    /**
     * Returns an array of properties that are used as an ID value.
     *
     * @return array
     */
    public function getIdProperties();

    /**
     * Returns a list of URLs. This urls must have placeholders to be replaced with the getIdProperties() values.
     *
     * @return array
     */
    public function getUrls();

    /**
     * Returns an array of properties that are mandatory to be passed in when doing create or update.
     *
     * @return array
     */
    public function getRequiredProperties();
}
