<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/26/15
 * Time: 12:44 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Api\Mapping;

/**
 * Class Mapper.
 */
class Mapper
{
    /**
     * @var array
     */
    private $classMap = [];
    /**
     * @var array
     */
    private $aliasMap = [];

    /**
     * @param array $mappings
     *
     * @throws MappingException
     */
    public function __construct(array $mappings = null)
    {
        if (is_array($mappings)) {
            foreach ($mappings as $mappedClass) {
                $mapping = $this->buildMapping($mappedClass);

                if (false === empty($this->aliasMap[$mapping->getClassAlias()])) {

                    throw new MappingException(
                        sprintf(
                            'Class with name \'%s\' already present, used by \'%s\'. Please add an alias for \'%s\' or change an existing one.',
                            $mapping->getClassAlias(),
                            $this->aliasMap[$mapping->getClassAlias()],
                            $mapping->getClassName()
                        )
                    );
                }

                $this->classMap[ltrim($mapping->getClassName(), '\\')] = $mapping;
                $this->aliasMap[ltrim($mapping->getClassAlias(), '\\')] = $mapping->getClassName();
            }
        }
    }

    /**
     * @param string|array $mappedClass
     *
     * @return Mapping
     */
    protected function buildMapping($mappedClass)
    {
        return (is_string($mappedClass) && class_exists($mappedClass, true)) ?
            MappingFactory::fromClass($mappedClass) :
            MappingFactory::fromArray($mappedClass);
    }

    /**
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * @param array $array
     */
    public function setClassMap(array $array)
    {
        $this->classMap = $array;
    }
}
