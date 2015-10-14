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

use ReflectionClass;

/**
 * Class MappingFactory.
 */
class MappingFactory
{
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
        $mapping->setClassAlias((empty($mappedClass['alias'])) ? $className : $mappedClass['alias']);

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
        if (empty($mappedClass['class'])) {
            throw new MappingException(
                'Could not find "class" property. This is required for class to be mapped'
            );
        }

        return $mappedClass['class'];
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
        if (empty($mappedClass['urls']['self'])) {
            throw new MappingException(
                'Could not find "self" property under "urls". This is required in order to make the resource to be reachable.'
            );
        }

        return $mappedClass['urls']['self'];
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    private static function getIdProperties(array &$mappedClass)
    {
        return (!empty($mappedClass['id_properties'])) ? $mappedClass['id_properties'] : [];
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
        if (false === empty($mappedClass['aliased_properties'])) {
            $mapping->setPropertyNameAliases($mappedClass['aliased_properties']);
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
        if (false === empty($mappedClass['hide_properties'])) {
            $mapping->setHiddenProperties($mappedClass['hide_properties']);
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
        if (!empty($mappedClass['relationships'])) {
            foreach ($mappedClass['relationships'] as $propertyName => $urls) {
                if (false === in_array($propertyName, self::getClassProperties($className), true)) {
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
        if (false === empty($mappedClass['curies'])) {
            $mapping->setCuries($mappedClass['curies']);
        }
    }

    /**
     * @param array $mappedClass
     *
     * @return mixed
     */
    private static function getOtherUrls(array $mappedClass)
    {
        if (!empty($mappedClass['urls']['self'])) {
            unset($mappedClass['urls']['self']);
        }

        return $mappedClass['urls'];
    }
}
