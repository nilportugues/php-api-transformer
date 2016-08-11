<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/26/15
 * Time: 12:11 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Api\Mapping;

use NilPortugues\Api\Mappings\ApiMapping;
use NilPortugues\Api\Mappings\HalMapping;
use NilPortugues\Api\Mappings\JsonApiMapping;
use ReflectionClass;

/**
 * Class MappingFactory.
 */
class MappingFactory
{
    const CLASS_KEY = 'class';
    const ALIAS_KEY = 'alias';
    const ALIASED_PROPERTIES_KEY = 'aliased_properties';
    const REQUIRED_PROPERTIES_KEY = 'required_properties';
    const HIDE_PROPERTIES_KEY = 'hide_properties';
    const ID_PROPERTIES_KEY = 'id_properties';
    const URLS_KEY = 'urls';
    const CURIES_KEY = 'curies';
    const RELATIONSHIPS_KEY = 'relationships';
    const SELF_KEY = 'self';

    /**
     * @var array
     */
    protected static $classProperties = [];

    /**
     * @param string $className
     *
     * @throws MappingException
     *
     * @return Mapping
     *
     * @since 2.0.0
     */
    public static function fromClass($className)
    {
        /* @var ApiMapping|HalMapping|JsonApiMapping $instance */
        $className = '\\'.ltrim($className, '\\');
        if (!class_exists($className, true)) {
            throw new MappingException(
                \sprintf('Provided class %s could not be loaded.', $className)
            );
        }
        $instance = new $className();

        if (!in_array(ApiMapping::class, \class_implements($instance, true))) {
            throw new MappingException(
                \sprintf('Class %s must implement %s.', \ltrim($className, '\\'), ApiMapping::class)
            );
        }

        $mappedClass = [
            static::CLASS_KEY => $instance->getClass(),
            static::ALIAS_KEY => $instance->getAlias(),
            static::ALIASED_PROPERTIES_KEY => $instance->getAliasedProperties(),
            static::HIDE_PROPERTIES_KEY => $instance->getHideProperties(),
            static::ID_PROPERTIES_KEY => $instance->getIdProperties(),
            static::URLS_KEY => $instance->getUrls(),
            static::REQUIRED_PROPERTIES_KEY => $instance->getRequiredProperties(),
        ];

        if (\in_array(HalMapping::class, \class_implements($instance, true))) {
            $mappedClass[static::CURIES_KEY] = $instance->getCuries();
        }

        if (\in_array(JsonApiMapping::class, \class_implements($instance, true))) {
            $mappedClass[static::RELATIONSHIPS_KEY] = $instance->getRelationships();
        }

        return static::fromArray($mappedClass);
    }

    /**
     * @param array $mappedClass
     *
     * @throws MappingException
     *
     * @return Mapping
     */
    public static function fromArray(array &$mappedClass)
    {
        $className = static::getClass($mappedClass);
        $resourceUrl = static::getSelfUrl($mappedClass);
        $idProperties = static::getIdProperties($mappedClass);

        $mapping = new Mapping($className, $resourceUrl, $idProperties);
        $mapping->setClassAlias((empty($mappedClass[static::ALIAS_KEY])) ? $className : $mappedClass[static::ALIAS_KEY]);

        static::setAliasedProperties($mappedClass, $mapping, $className);
        static::setHideProperties($mappedClass, $mapping, $className);
        static::setRelationships($mappedClass, $mapping, $className);
        static::setCuries($mappedClass, $mapping);
        static::setProperties($mapping, $className);
        static::setRequiredProperties($mappedClass, $mapping, $className);

        $otherUrls = static::getOtherUrls($mappedClass);
        if (!empty($otherUrls)) {
            $mapping->setUrls($otherUrls);
        }

        return $mapping;
    }

    /**
     * @param array $mappedClass
     *
     * @throws MappingException
     *
     * @return mixed
     */
    protected static function getClass(array &$mappedClass)
    {
        if (empty($mappedClass[static::CLASS_KEY])) {
            throw new MappingException(
                'Could not find "class" property. This is required for class to be mapped'
            );
        }

        return $mappedClass[static::CLASS_KEY];
    }

    /**
     * @param array $mappedClass
     *
     * @throws MappingException
     *
     * @return mixed
     */
    protected static function getSelfUrl(array &$mappedClass)
    {
        if (empty($mappedClass[static::URLS_KEY][static::SELF_KEY])) {
            throw new MappingException(
                'Could not find "self" property under "urls". This is required in order to make the resource to be reachable.'
            );
        }

        return $mappedClass[static::URLS_KEY][static::SELF_KEY];
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    protected static function getIdProperties(array &$mappedClass)
    {
        return (!empty($mappedClass[static::ID_PROPERTIES_KEY])) ? $mappedClass[static::ID_PROPERTIES_KEY] : [];
    }

    /**
     * @param array   $mappedClass
     * @param Mapping $mapping
     * @param string  $className
     *
     * @throws MappingException
     */
    protected static function setAliasedProperties(array &$mappedClass, Mapping $mapping, $className)
    {
        if (false === empty($mappedClass[static::ALIASED_PROPERTIES_KEY])) {
            $mapping->setPropertyNameAliases($mappedClass[static::ALIASED_PROPERTIES_KEY]);
            foreach (\array_keys($mapping->getAliasedProperties()) as $propertyName) {
                if (false === \in_array($propertyName, static::getClassProperties($className), true)) {
                    throw new MappingException(
                        \sprintf(
                            'Could not alias property %s in class %s because it does not exist.',
                            $propertyName,
                            $className
                        )
                    );
                }
            }
        }
    }

    /**
     * Recursive function to get an associative array of class properties by
     * property name, including inherited ones from extended classes.
     *
     * @param string $className Class name
     *
     * @return array
     *
     * @link http://php.net/manual/es/reflectionclass.getproperties.php#88405
     */
    protected static function getClassProperties($className)
    {
        if (empty(static::$classProperties[$className])) {
            $ref = new ReflectionClass($className);
            $properties = [];
            foreach ($ref->getProperties() as $prop) {
                $f = $prop->getName();
                $properties[$f] = $prop;
            }

            if ($parentClass = $ref->getParentClass()) {
                $parentPropsArr = static::getClassProperties($parentClass->getName());
                if (\count($parentPropsArr) > 0) {
                    $properties = \array_merge($parentPropsArr, $properties);
                }
            }
            static::$classProperties[$className] = \array_keys($properties);
        }

        return static::$classProperties[$className];
    }

    /**
     * @param array   $mappedClass
     * @param Mapping $mapping
     * @param string  $className
     *
     * @throws MappingException
     */
    protected static function setHideProperties(array &$mappedClass, Mapping $mapping, $className)
    {
        if (false === empty($mappedClass[static::HIDE_PROPERTIES_KEY])) {
            $mapping->setHiddenProperties($mappedClass[static::HIDE_PROPERTIES_KEY]);
            foreach ($mapping->getHiddenProperties() as $propertyName) {
                if (false === \in_array($propertyName, static::getClassProperties($className), true)) {
                    throw new MappingException(
                        \sprintf(
                            'Could not hide property %s in class %s because it does not exist.',
                            $propertyName,
                            $className
                        )
                    );
                }
            }
        }
    }

    /**
     * @param array   $mappedClass
     * @param Mapping $mapping
     * @param string  $className
     *
     * @throws MappingException
     */
    protected static function setRelationships(array &$mappedClass, Mapping $mapping, $className)
    {
        if (!empty($mappedClass[static::RELATIONSHIPS_KEY])) {
            foreach ($mappedClass[static::RELATIONSHIPS_KEY] as $propertyName => $urls) {
                if (false === \in_array($propertyName, static::getClassProperties($className))) {
                    throw new MappingException(
                        \sprintf(
                            'Could not find property %s in class %s because it does not exist.',
                            $propertyName,
                            $className
                        )
                    );
                }

                $mapping->setRelationshipUrls($propertyName, $urls);
            }
        }
    }

    /**
     * @param array   $mappedClass
     * @param Mapping $mapping
     */
    protected static function setCuries(array &$mappedClass, Mapping $mapping)
    {
        if (false === empty($mappedClass[static::CURIES_KEY])) {
            $mapping->setCuries($mappedClass[static::CURIES_KEY]);
        }
    }

    /**
     * @param Mapping $mapping
     * @param string  $className
     *
     * @throws MappingException
     */
    protected static function setProperties(Mapping $mapping, $className)
    {
        $mapping->setProperties(static::getClassProperties($className));
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    protected static function getOtherUrls(array $mappedClass)
    {
        if (!empty($mappedClass[static::URLS_KEY][static::SELF_KEY])) {
            unset($mappedClass[static::URLS_KEY][static::SELF_KEY]);
        }

        return $mappedClass[static::URLS_KEY];
    }

    /**
     * @param array $mappedClass
     * @param Mapping $mapping
     * @param $className
     */
    protected static function setRequiredProperties(array &$mappedClass, Mapping $mapping, $className)
    {
        if (false === empty($mappedClass[static::REQUIRED_PROPERTIES_KEY])) {
            $mapping->setRequiredProperties($mappedClass[static::REQUIRED_PROPERTIES_KEY]);
            foreach (\array_keys($mapping->getRequiredProperties()) as $propertyName) {
                if (false === \in_array($propertyName, static::getClassProperties($className), true)) {
                    throw new MappingException(
                        \sprintf(
                            'Could not add required property %s in class %s because it does not exist.',
                            $propertyName,
                            $className
                        )
                    );
                }
            }
        }
    }
}
