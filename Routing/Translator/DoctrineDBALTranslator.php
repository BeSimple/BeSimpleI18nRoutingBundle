<?php

namespace BeSimple\I18nRoutingBundle\Routing\Translator;

use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

/**
 * Translate route attributes using Doctrine DBAL to access a routing_translations table.
 *
 * Caching is used to avoid database lookups, it is a requirement to use caching!
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DoctrineDBALTranslator implements AttributeTranslatorInterface
{
    /**
     * @var Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     *
     * @var Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * Prime the cache when using {@see addTranslation()} yes or no.
     *
     * @var bool
     */
    private $primeCache;

    public function __construct(Connection $connection, Cache $cache, $primeCache = true)
    {
        $this->connection = $connection;
        $this->cache      = $cache;
        $this->primeCache = $primeCache;
    }

    /**
     * Translate using Doctrine DBAL and a cache layer around it.
     *
     * @param string $route
     * @param string $locale
     * @param string $attribute
     * @param string $value
     * @return string
     */
    public function translate($route, $locale, $attribute, $value)
    {
        // values can potentially be large, so we hash them and prevent collisions
        $hashKey          = $route . "__" . $locale . "__" . $attribute . "__" . $value;
        $cacheKey         = "besimplei18nroute__" . sha1($hashKey);
        $translatedValues = $this->cache->fetch($cacheKey);
        if ($translatedValues && isset($translatedValues[$hashKey])) {
            return $translatedValues[$hashKey];
        }

        $query = "SELECT original_value FROM routing_translations ".
                 "WHERE route = ? AND locale = ? AND attribute = ? AND localized_value = ?";
        if ($translatedValue = $this->connection->fetchColumn($query, array($route, $locale, $attribute, $value))) {
            $value = $translatedValue;
        }

        $translatedValues[$hashKey] = $value;
        $this->cache->save($cacheKey, $translatedValues);

        return $value;
    }

    /**
     * Reverse Translate a value into its current locale.
     *
     * This feature can optionally be used when generating route urls by passing
     * the "translate" parameter to RouterInterface::generate() 
     * specifying which attributes should be translated.
     *
     * @param string $route
     * @param string $locale
     * @param string $attribute
     * @param string $originalValue
     * @return string
     */
    public function reverseTranslate($route, $locale, $attribute, $value)
    {
        // values can potentially be large, so we hash them and prevent collisions
        $hashKey  = $route . "__" . $locale . "__" . $attribute . "__" . $value;
        $cacheKey = "besimplei18nroute__reverse__" . sha1($hashKey);
        $reverseTranslatedValues = $this->cache->fetch($cacheKey);
        if ($reverseTranslatedValues && isset($reverseTranslatedValues[$hashKey])) {
            return $reverseTranslatedValues[$hashKey];
        }

        $query = "SELECT localized_value FROM routing_translations ".
                 "WHERE route = ? AND locale = ? AND attribute = ? AND original_value = ?";
        if ($lovalizedValue = $this->connection->fetchColumn($query, array($route, $locale, $attribute, $value))) {
            $value = $lovalizedValue;
        }

        $reverseTranslatedValues[$hashKey] = $value;
        $this->cache->save($cacheKey, $reverseTranslatedValues);

        return $value;
    }

    public function addTranslation($route, $locale, $attribute, $localizedValue, $originalValue)
    {
        $query = "SELECT id FROM routing_translations WHERE route = ? AND locale = ? AND attribute = ?";
        $id = $this->connection->fetchColumn($query, array($route, $locale, $attribute));

        if ($id) {
            $this->connection->update('routing_translations', array(
                'localized_value' => $localizedValue,
                'original_value' => $originalValue,
            ), array('id' => $id));
        } else {
            $this->connection->insert('routing_translations', array(
                'route'  => $route,
                'locale' => $locale,
                'attribute' => $attribute,
                'localized_value' => $localizedValue,
                'original_value' => $originalValue,
            ));
        }

        // prime the cache!
        if ($this->primeCache) {
            $hashKey  = $route . "__" . $locale . "__" . $attribute . "__" . $localizedValue;
            $cacheKey = "besimplei18nroute__" . sha1($hashKey);
            $translatedValues = $this->cache->fetch($cacheKey);
            if (!$translatedValues) {
                $translatedValues = array();
            }
            $translatedValues[$hashKey][$originalValue];
            $this->cache->save($cacheKey, $translatedValues);
        }
    }
}
