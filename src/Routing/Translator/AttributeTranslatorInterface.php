<?php

namespace BeSimple\I18nRoutingBundle\Routing\Translator;

/**
 * Translate attributes of routes using a service, for example a database.
 *
 * Translatable attributes are detected by looking for the defaults value "_translate".
 * It can either be an array or a string of an attribute to translate:
 *
 * @example:
 *
 * my_route:
 *   locales: { en: /welcome/{name}, de: /willkommen/{name} }
 *   defaults: { _controller: "MyBundle:Hello:world", _translate: "name" }
 *
 * my_route2:
 *   locales: { en: /products/{category}/{slug}, de: /produkte/{category}/{slug} }
 *   defaults:
 *     _controller: "MyBundle:Hello:world"
 *     _translate: ["category", "slug"]
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
interface AttributeTranslatorInterface
{
    /**
     * Translate a route attribute.
     *
     * Always returns a value, if no translation is found the original value.
     *
     * @param string $route
     * @param string $locale
     * @param string $attribute
     * @param string $localizedValue
     * @return string
     */
    public function translate($route, $locale, $attribute, $localizedValue);

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
    public function reverseTranslate($route, $locale, $attribute, $originalValue);
}
