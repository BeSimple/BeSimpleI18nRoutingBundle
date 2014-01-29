I18nRoutingBundle, generate your I18N Routes for Symfony2
=========================================================

If you have a website multilingual, this bundle avoids of copy paste your routes
for different languages. Additionally it allows to translate given routing parameters
between languages in Router#match and UrlGenerator#generate using either a Symfony Translator
or a Doctrine DBAL (+Cache) based backend.

[![Build Status](https://secure.travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle.png?branch=master)](http://travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle)

## Information

When you create an I18N route and you go on it with your browser, the locale will be updated.

## Installation

```js
//composer.json
"require": {
    //...
    "besimple/i18n-routing-bundle": "dev-master"
}
```

```php
//app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        //...
        new BeSimple\I18nRoutingBundle\BeSimpleI18nRoutingBundle(),
    );
}
```

### Update your configuration

```yaml
# app/config/config.yml
be_simple_i18n_routing: ~
```

## Create your routing

To define internationalized routes in XML or YAML, you need to import the
routing file by using the ``be_simple_i18n`` type:

```yaml
my_yaml_i18n_routes:
    resource: "@MyWebsiteBundle/Resources/config/routing/i18n.yml"
    type: be_simple_i18n
my_xml_i18n_routes:
    resource: "@MyWebsiteBundle/Resources/config/routing/i18n.xml"
    type: be_simple_i18n
```

### Yaml routing file

```yaml
homepage:
    locales:  { en: "/welcome", fr: "/bienvenue", de: "/willkommen" }
    defaults: { _controller: MyWebsiteBundle:Frontend:index }
```

### XML routing file

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://besim.pl/schema/i18n_routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://besim.pl/schema/i18n_routing http://besim.pl/schema/i18n_routing/routing-1.0.xsd">

    <route id="homepage">
        <locale key="en">/welcome</locale>
        <locale key="fr">/bienvenue</locale>
        <locale key="de">/willkommen</locale>
        <default key="_controller">MyWebsiteBundle:Frontend:index</default>
    </route>
</routes>
```

Note that the XML file uses a different namespace than when using the core
loader: ``http://besim.pl/schema/i18n_routing``.

### PHP routing file

```php
<?php

use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();
$route      = new I18nRoute('homepage',
    array('en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen'),
    array('_controller' => 'MyWebsiteBundle:Frontend:index')
);
$collection->addCollection($route->getCollection());

return $collection;
```

### You can insert classic route in your routing

#### Yaml routing file

```yaml
homepage:
    locales:  { en: "/en/", fr: "/fr/", de: "/de/" }
    defaults: { _controller: HelloBundle:Frontend:homepage }

welcome:
    locales:  { en: "/welcome/{name}", fr: "/bienvenue/{name}", de: "/willkommen/{name}" }
    defaults: { _controller: MyWebsiteBundle:Frontend:welcome }
```

#### XML routing file

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<routes xmlns="http://besim.pl/schema/i18n_routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://besim.pl/schema/i18n_routing http://besim.pl/schema/i18n_routing/routing-1.0.xsd">

    <route id="hello" pattern="/hello/{name}">
        <default key="_controller">HelloBundle:Hello:index</default>
    </route>
        <route id="homepage">
        <locale key="en">/welcome/{name}</locale>
        <locale key="fr">/bienvenue/{name}</locale>
        <locale key="de">/willkommen/{name}</locale>
        <default key="_controller">MyWebsiteBundle:Frontend:index</default>
    </route>
</routes>
```

#### PHP routing file

```php
<?php

use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
$collection = new RouteCollection();
$collection->add('hello', new Route('/hello/{name}', array(
    '_controller' => 'HelloBundle:Hello:index',
)));
$route = new I18nRoute('homepage',
    array('en' => '/welcome/{name}', 'fr' => '/bienvenue/{name}', 'de' => '/willkommen/{name}'),
    array('_controller' => 'MyWebsiteBundle:Frontend:index',)
);
$collection->addCollection($route->getCollection());

return $collection;
```

## Generate a route in your templates

### Specify a locale

#### Twig

    {{ path('homepage.en') }}
    {{ path('homepage', { 'locale': 'en' }) }}
    {{ path('homepage.fr') }}
    {{ path('homepage', { 'locale': 'fr' }) }}
    {{ path('homepage.de') }}
    {{ path('homepage', { 'locale': 'de' }) }}

#### PHP

```php
<?php echo $view['router']->generate('homepage.en') ?>
<?php echo $view['router']->generate('homepage', array('locale' => 'en')) ?>
<?php echo $view['router']->generate('homepage.fr') ?>
<?php echo $view['router']->generate('homepage', array('locale' => 'fr')) ?>
<?php echo $view['router']->generate('homepage.de') ?>
<?php echo $view['router']->generate('homepage', array('locale' => 'de')) ?>
```

### Use current locale of user

#### Twig

    {{ path('homepage') }}

#### PHP

```php
<?php echo $view['router']->generate('homepage') ?>
```

## Translating the route attributes

If the static parts of your routes are translated you get to the point really
fast when dynamic parts such as product slugs, category names or other dynamic
routing parameters should be translated. The bundle provides 2 implementations.

After configuring the backend you want to use (see below for each one), you
can define a to be translated attribute in your route defaults:

```yaml
product_view:
    locales: { en: "/product/{slug}", de: "/produkt/{slug}" }
    defaults: { _controller: "ShopBundle:Product:view", _translate: "slug" }
product_view2:
    locales: { en: "/product/{category}/{slug}", de: "/produkt/{category}/{slug}" }
    defaults:
        _controller: "ShopBundle:Product:view"
        _translate: ["slug", "category"]
```

The same goes with generating routes, now backwards:

    {{ path("product_view", {"slug": product.slug, "translate": "slug"}) }}
    {{ path("product_view2", {"slug": product.slug, "translate": ["slug", "category]}) }}

The reverse translation is only necessary if you have the "original" values
in your templates. If you have access to the localized value of the current
locale then you can just pass this and do not hint to translate it with the
"translate" key.

### Doctrine DBAL Backend

Configure the use of the DBAL backend

```yaml
# app/config/config.yml
be_simple_i18n_routing:
    attribute_translator:
        type: doctrine_dbal
        connection: default # Doctrine DBAL connection name. Using null (default value) will use the default connection
        cache: apc
```

The Doctrine Backend has the following table structure:

```sql
CREATE TABLE routing_translations (
    id INT NOT NULL,
    route VARCHAR(255) NOT NULL,
    locale VARCHAR(255) NOT NULL,
    attribute VARCHAR(255) NOT NULL,
    localized_value VARCHAR(255) NOT NULL,
    original_value VARCHAR(255) NOT NULL,
    UNIQUE INDEX UNIQ_291BA3522C420794180C698FA7AEFFB (route, locale, attribute),
    INDEX IDX_291BA352D951F3E4 (localized_value),
    PRIMARY KEY(id)
) ENGINE = InnoDB;
```

Lookups are made through the combination of route name, locale and attribute
of the route to be translated.

Every lookup is cached in a Doctrine\Common\Cache\Cache instance that you
should configure to be APC, Memcache or Xcache for performance reasons.

If you are using Doctrine it automatically registers a listener for SchemaTool
to create the routing_translations table for your database backend, you only
have to call:

    ./app/console doctrine:schema:update --dump-sql
    ./app/console doctrine:schema:update --force

### Translator backend

This implementation uses the Symfony2 translator to translate the attributes.
The translation domain will be created using the pattern `<route name>_<attribute name>`

```yaml
# app/config/config.yml
be_simple_i18n_routing:
    attribute_translator:
        type: translator
```

### Custom backend

If you want to use a different implementation, simply create a service implementing
`BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface`.

```yaml
# app/config/config.yml
be_simple_i18n_routing:
    attribute_translator:
        type: service
        id: my_attribute_translator
```

## License

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
