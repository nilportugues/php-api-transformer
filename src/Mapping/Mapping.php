<?php

namespace NilPortugues\Api\Mapping;

use NilPortugues\Api\Transformer\Helpers\RecursiveFormatterHelper;

class Mapping
{
    use NullableTrait;

    /** @var string */
    protected $className;
    /** @var string */
    protected $resourceUrlPattern;
    /** @var string */
    protected $classAlias;
    /** @var array */
    protected $aliasedProperties;
    /** @var array */
    protected $hiddenProperties;
    /** @var array */
    protected $idProperties;
    /** @var array */
    protected $relationships;
    /** @var array */
    protected $metaData;
    /** @var string */
    protected $selfUrl;
    /** @var array */
    protected $otherUrls;
    /** @var array */
    protected $relationshipSelfUrl;
    /** @var array */
    protected $filterKeys;
    /** @var array */
    protected $curies;
    /** @var array */
    protected $properties;
    /** @var array */
    protected $includedKeys;
    /** @var bool */
    protected $filteringIncluded;

    /**
     * Mapping constructor.
     *
     * @param string      $className
     * @param string|null $resourceUrlPattern
     * @param array       $idProperties
     */
    public function __construct(string $className, string $resourceUrlPattern = null, array $idProperties = [])
    {
        $this->className = $className;
        $this->resourceUrlPattern = $resourceUrlPattern;
        $this->idProperties = $idProperties;
    }

    /**
     * @return string
     */
    public function getClassAlias() : string
    {
        return $this->classAlias;
    }

    /**
     * @param string $aliasedClass
     *
     * @return $this
     */
    public function setClassAlias(string $aliasedClass)
    {
        $this->classAlias = RecursiveFormatterHelper::camelCaseToUnderscore(
            RecursiveFormatterHelper::namespaceAsArrayKey($aliasedClass)
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getIdProperties() : array
    {
        return (array) $this->idProperties;
    }

    /**
     * @param string $idProperty
     */
    public function addIdProperty(string $idProperty)
    {
        $this->idProperties[] = $idProperty;
    }

    /**
     * @param string $propertyName
     */
    public function hideProperty(string $propertyName)
    {
        $this->hiddenProperties[] = $propertyName;
    }

    /**
     * @param string $propertyName
     * @param string $propertyAlias
     */
    public function addPropertyAlias(string $propertyName, string $propertyAlias)
    {
        $this->aliasedProperties[$propertyName] = $propertyAlias;

        $this->updatePropertyMappings($propertyName, $propertyAlias);
    }

    /**
     * @param string $propertyName
     * @param string $propertyAlias
     */
    protected function updatePropertyMappings(string $propertyName, string $propertyAlias)
    {
        if (\in_array($propertyName, (array) $this->idProperties)) {
            $position = \array_search($propertyName, $this->idProperties, true);
            $this->idProperties[$position] = $propertyAlias;
        }

        $search = \sprintf('{%s}', $propertyName);
        $replace = \sprintf('{%s}', $propertyAlias);

        $this->selfUrl = \str_replace($search, $replace, $this->selfUrl);
        $this->resourceUrlPattern = \str_replace($search, $replace, $this->resourceUrlPattern);
        $this->otherUrls = \str_replace($search, $replace, $this->otherUrls);
    }

    /**
     * @param array $properties
     */
    public function setPropertyNameAliases(array $properties)
    {
        $this->aliasedProperties = \array_merge(
            (array) $this->aliasedProperties,
            $properties
        );

        foreach ($this->aliasedProperties as $propertyName => $propertyAlias) {
            $this->updatePropertyMappings($propertyName, $propertyAlias);
        }
    }

    /**
     * @return array
     */
    public function getProperties() : array
    {
        return (array) $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getClassName() : string
    {
        return (string) $this->className;
    }

    /**
     * @return string
     */
    public function getResourceUrl() : string
    {
        return (string) $this->resourceUrlPattern;
    }

    /**
     * @return array
     */
    public function getAliasedProperties() : array
    {
        return (array) $this->aliasedProperties;
    }

    /**
     * @return array
     */
    public function getHiddenProperties() : array
    {
        return (array) $this->hiddenProperties;
    }

    /**
     * @param array $hidden
     */
    public function setHiddenProperties(array $hidden)
    {
        $this->hiddenProperties = \array_merge(
            (array) $this->hiddenProperties,
            $hidden
        );
    }

    /**
     * @return array
     */
    public function getRelationships() : array
    {
        return (array) $this->relationships;
    }

    /**
     * @param array $relationships
     */
    public function addAdditionalRelationships(array $relationships)
    {
        $this->relationships = $relationships;
    }

    /**
     * @return array
     */
    public function getMetaData() : array
    {
        return (array) $this->metaData;
    }

    /**
     * @param array $metaData
     */
    public function setMetaData(array $metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function addMetaData(string $key, $value)
    {
        $this->metaData[$key] = $value;
    }

    /**
     * @return string
     */
    public function getSelfUrl() : string
    {
        return (string) $this->selfUrl;
    }

    /**
     * @param string $self
     */
    public function setSelfUrl(string $self)
    {
        $this->selfUrl = $self;
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function getRelatedUrl(string $propertyName) : string
    {
        return (!empty($this->relationshipSelfUrl[$propertyName]['related']))
            ? $this->relationshipSelfUrl[$propertyName]['related']
            : '';
    }

    /**
     * @return array
     */
    public function getFilterKeys() : array
    {
        return (array) $this->filterKeys;
    }

    /**
     * @param array $filterKeys
     */
    public function setFilterKeys(array $filterKeys)
    {
        $this->filterKeys = $filterKeys;
    }

    /**
     * @param string $propertyName
     * @param $urls
     *
     * @return $this
     */
    public function setRelationshipUrls(string $propertyName, $urls)
    {
        $this->relationshipSelfUrl[$propertyName] = $urls;

        return $this;
    }

    /**
     * @param $propertyName
     *
     * @return string
     */
    public function getRelationshipSelfUrl(string $propertyName)
    {
        return (!empty($this->relationshipSelfUrl[$propertyName]['self']))
            ? $this->relationshipSelfUrl[$propertyName]['self']
            : '';
    }

    /**
     * @param array $urls
     */
    public function setUrls(array $urls)
    {
        $this->otherUrls = $urls;
    }

    /**
     * @return array
     */
    public function getUrls() : array
    {
        return (array) $this->otherUrls;
    }

    /**
     * @return array
     */
    public function getCuries() : array
    {
        return (array) $this->curies;
    }

    /**
     * @param array $curies
     *
     * @throws MappingException
     */
    public function setCuries(array $curies)
    {
        if (empty($curies['name']) || empty($curies['href'])) {
            throw new MappingException('Curies must define "name" and "href" properties');
        }

        $this->curies = $curies;
    }

    /**
     * Used by JSON API included resource filtering.
     *
     * @param $resource
     */
    public function addIncludedResource($resource)
    {
        $this->includedKeys[] = $resource;
    }

    /**
     * Returns the allowed included resources.
     *
     * @return array
     */
    public function getIncludedResources() : array
    {
        return (array) $this->includedKeys;
    }

    /**
     * @param bool $filtering
     */
    public function filteringIncludedResources(bool $filtering = true)
    {
        $this->filteringIncluded = $filtering;
    }

    /**
     * Returns true if included resource filtering has been set, false otherwise.
     *
     * @return bool
     */
    public function isFilteringIncludedResources() : bool
    {
        return (null === $this->filteringIncluded) ? true : $this->filteringIncluded;
    }
}
