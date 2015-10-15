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
use NilPortugues\Api\Mappings\HalJsonMapping;
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
    const HIDE_PROPERTIES_KEY = 'hide_properties';
    const ID_PROPERTIES_KEY = 'id_properties';
    const URLS_KEY = 'urls';
    const CURIES_KEY = 'curies';
    const RELATIONSHIPS_KEY = 'relationships';
    const SELF_KEY = 'self';

    /**
     * @param string $className
     *
     * @return Mapping
     *
     * @since 2.0.0
     */
    public static function fromClass($className)
    {
        /* @var ApiMapping|HalJsonMapping|JsonApiMapping $instance */
        $className = (string) $className;
        $instance = new $className();

        if (!in_array(ApiMapping::class, class_implements($instance, true))) {
            throw new MappingException(
                sprintf('Class %s must implement %s.', get_class($instance), ApiMapping::class)
            );
        }

        $mappedClass = [
            self::CLASS_KEY => $instance->getClass(),
            self::ALIAS_KEY => $instance->getAlias(),
            self::ALIASED_PROPERTIES_KEY => $instance->getAliasedProperties(),
            self::HIDE_PROPERTIES_KEY => $instance->getHideProperties(),
            self::ID_PROPERTIES_KEY => $instance->getIdProperties(),
            self::URLS_KEY => $instance->getUrls(),
        ];

        if (in_array(HalJsonMapping::class, class_implements($instance, true))) {
            $mappedClass[self::CURIES_KEY] = $instance->getCuries();
        }

        if (in_array(JsonApiMapping::class, class_implements($instance, true))) {
            $mappedClass[self::RELATIONSHIPS_KEY] = $instance->getRelationships();
        }

        return self::fromArray($mappedClass);
    }

    /**
     * @var array
     */
    private static $classProperties = [];

    /**
     * @param array $mappedClass
     *
     * @throws MappingException
     *
     * @return Mapping
     */
    public static function fromArray(array &$mappedClass)
    {
        $className = self::getClass($mappedClass);
        $resourceUrl = self::getSelfUrl($mappedClass);
        $idProperties = self::getIdProperties($mappedClass);

        $mapping = new Mapping($className, $resourceUrl, $idProperties);
        $mapping->setClassAlias((empty($mappedClass[self::ALIAS_KEY])) ? $className : $mappedClass[self::ALIAS_KEY]);

        self::setAliasedProperties($mappedClass, $mapping, $className);
        self::setHideProperties($mappedClass, $mapping, $className);
        self::setRelationships($mappedClass, $mapping, $className);
        self::setCuries($mappedClass, $mapping);

        $otherUrls = self::getOtherUrls($mappedClass);
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
    private static function getClass(array &$mappedClass)
    {
        if (empty($mappedClass[self::CLASS_KEY])) {
            throw new MappingException(
                'Could not find "class" property. This is required for class to be mapped'
            );
        }

        return $mappedClass[self::CLASS_KEY];
    }

    /**
     * @param array $mappedClass
     *
     * @throws MappingException
     *
     * @return mixed
     */
    private static function getSelfUrl(array &$mappedClass)
    {
        if (empty($mappedClass[self::URLS_KEY][self::SELF_KEY])) {
            throw new MappingException(
                'Could not find "self" property under "urls". This is required in order to make the resource to be reachable.'
            );
        }

        return $mappedClass[self::URLS_KEY][self::SELF_KEY];
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    private static function getIdProperties(array &$mappedClass)
    {
        return (!empty($mappedClass[self::ID_PROPERTIES_KEY])) ? $mappedClass[self::ID_PROPERTIES_KEY] : [];
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
        if (false === empty($mappedClass[self::ALIASED_PROPERTIES_KEY])) {
            $mapping->setPropertyNameAliases($mappedClass[self::ALIASED_PROPERTIES_KEY]);
            foreach (array_keys($mapping->getAliasedProperties()) as $propertyName) {
                if (false === in_array($propertyName, self::getClassProperties($className), true)) {
                    throw new MappingException(
                        sprintf(
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
    private static function getClassProperties($className)
    {
        if (empty(self::$classProperties[$className])) {
            $ref = new ReflectionClass($className);
            $properties = [];
            foreach ($ref->getProperties() as $prop) {
                $f = $prop->getName();
                $properties[$f] = $prop;
            }

            if ($parentClass = $ref->getParentClass()) {
                $parentPropsArr = self::getClassProperties($parentClass->getName());
                if (count($parentPropsArr) > 0) {
                    $properties = array_merge($parentPropsArr, $properties);
                }
            }
            self::$classProperties[$className] = array_keys($properties);
        }

        return self::$classProperties[$className];
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
        if (false === empty($mappedClass[self::HIDE_PROPERTIES_KEY])) {
            $mapping->setHiddenProperties($mappedClass[self::HIDE_PROPERTIES_KEY]);
            foreach ($mapping->getHiddenProperties() as $propertyName) {
                if (false === in_array($propertyName, self::getClassProperties($className), true)) {
                    throw new MappingException(
                        sprintf(
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
        if (!empty($mappedClass[self::RELATIONSHIPS_KEY])) {
            foreach ($mappedClass[self::RELATIONSHIPS_KEY] as $propertyName => $urls) {
                if (false === in_array($propertyName, self::getClassProperties($className))) {
                    throw new MappingException(
                        sprintf(
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
        if (false === empty($mappedClass[self::CURIES_KEY])) {
            $mapping->setCuries($mappedClass[self::CURIES_KEY]);
        }
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    private static function getOtherUrls(array $mappedClass)
    {
        if (!empty($mappedClass[self::URLS_KEY][self::SELF_KEY])) {
            unset($mappedClass[self::URLS_KEY][self::SELF_KEY]);
        }

        return $mappedClass[self::URLS_KEY];
    }
}
