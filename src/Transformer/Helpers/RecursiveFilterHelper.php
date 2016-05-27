<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/24/15
 * Time: 8:55 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Transformer\Helpers;

use NilPortugues\Serializer\Serializer;

final class RecursiveFilterHelper
{
    /**
     * Delete all keys except the ones considered identifier keys or defined in the filter.
     *
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param array                               $array
     * @param string                              $typeKey
     */
    public static function deletePropertiesNotInFilter(array &$mappings, array &$array, string $typeKey)
    {
        if (\array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $array)) {
            $newArray = [];
            $type = $array[Serializer::CLASS_IDENTIFIER_KEY];

            self::deleteMatchedClassNotInFilterProperties($mappings, $array, $typeKey, $type, $newArray);

            if (!empty($newArray)) {
                $array = $newArray;
            }
        }
    }

    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param array                               $array
     * @param string                              $typeKey
     * @param string                              $type
     * @param array                               $newArray
     */
    protected static function deleteMatchedClassNotInFilterProperties(
        array &$mappings,
        array &$array,
        string $typeKey,
        string $type,
        array &$newArray
    ) {
        if (\is_scalar($type) && $type === $typeKey) {
            $keepKeys = $mappings[$typeKey]->getFilterKeys();
            $idProperties = $mappings[$typeKey]->getIdProperties();

            $keepKeys = str_replace(
                array_values($mappings[$typeKey]->getAliasedProperties()),
                array_keys($mappings[$typeKey]->getAliasedProperties()),
                $keepKeys
            );

            if (!empty($keepKeys)) {
                self::filterKeys($mappings, $array, $typeKey, $newArray, $keepKeys, $idProperties);
            }
        }
    }

    /**
     * @param array  $mappings
     * @param array  $array
     * @param string $typeKey
     * @param array  $newArray
     * @param array  $keepKeys
     * @param array  $idProperties
     */
    protected static function filterKeys(
        array &$mappings,
        array &$array,
        string $typeKey,
        array &$newArray,
        array &$keepKeys,
        array &$idProperties
    ) {
        foreach ($array as $key => &$value) {
            if (self::isPreservableKey($key, $keepKeys, $idProperties)
                || false === in_array($key, $mappings[$typeKey]->getProperties())
            ) {
                $newArray[$key] = $value;
                if (\is_array($newArray[$key])) {
                    self::deletePropertiesNotInFilter($mappings, $newArray[$key], $typeKey);
                }
            }
        }
    }

    /**
     * @param string $key
     * @param array  $keepKeys
     * @param array  $idProperties
     *
     * @return bool
     */
    protected static function isPreservableKey(string $key, array $keepKeys, array $idProperties) : bool
    {
        return $key == Serializer::CLASS_IDENTIFIER_KEY
        || (\in_array($key, $keepKeys, true) || \in_array($key, $idProperties, true));
    }
}
