I18nRoutingBundle, generate your I18N Routes for Symfony2
=========================================================

If you have a website multilingual, this bundle avoids of copy paste your routes
for different languages. Additionally it allows to translate given routing parameters
between languages in Router#match and UrlGenerator#generate using either a Symfony Translator
or a Doctrine DBAL (+Cache) based backend.

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](src/Resources/meta/LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-packagist]

## Information

When you create an I18N route and you go on it with your browser, the locale will be updated.

## Installation

```bash
composer.phar require besimple/i18n-routing-bundle
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
    prefix:
        en: /website
        fr: /site
        de: /webseite
my_xml_i18n_routes:
    resource: "@MyWebsiteBundle/Resources/config/routing/i18n.xml"
    type: be_simple_i18n
```

You can optionally specify a prefix or translated prefixes as shown above.

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

use Symfony\Component\Routing\RouteCollection;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;

$generator = new I18nRouteGenerator();

$collection = new RouteCollection();
$collection->addCollection(
    $generator->generateRoutes(
        'homepage',
        array('en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen'),
        new Route('', array(
            '_controller' => 'MyWebsiteBundle:Frontend:index'
        ))
    )
);

return $collection;
```

### Controller annotations
 
Annotation loading is only supported for Symfony 2.5 and greater and needs to be enabled as followed.
```YAML
# app/config/config.yml
be_simple_i18n_routing:
    annotations: true
```

```PHP
use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

class NoPrefixController
{
    /**
     * @I18nRoute({ "en": "/welcome", "fr": "/bienvenue", "de": "/willkommen" }, name="homepage")
     */
    public function indexAction() { }
}
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

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$generator = new I18nRouteGenerator();

$collection = new RouteCollection();
$collection->add('hello', new Route('/hello/{name}', array(
    '_controller' => 'HelloBundle:Hello:index',
)));
$collection->addCollection(
    $generator->generateRoutes(
        'homepage',
        array('en' => '/welcome/{name}', 'fr' => '/bienvenue/{name}', 'de' => '/willkommen/{name}'),
        new Route('', array(
            '_controller' => 'MyWebsiteBundle:Frontend:index',
        ))
    )
);

return $collection;
```

### Advanced locale support

By default this bundle allows any locale to be used and there is no check if a locale is missing for a specific route. 
This is great but sometimes you may wish to be strict, let take a look at the following configuration:
```YAML
be_simple_i18n_routing:
  locales:
    supported: ['en', 'nl']
    filter: true
    strict: true
```

The `locales.supported` specifies which locales are supported.

The `locales.filter` option is responsible for filtering out any unknown locales so only routes for 'en' and 'nl' are available.

The `locales.strict` option when set to `true` is responsible for throwing a exception when a i18n route is found where the locale is unknown or where a locale is missing.
This option can also be set to `null` to disable locale is missing for a route exception and `false` to disable exceptions.

### Route naming

By default all routes that are imported are named '<route_name>.<locale>' but sometimes you may want to change this behaviour.
To do this you can specify a route name inflector service in your configuration as followed.
```YAML
be_simple_i18n_routing:
  route_name_inflector: 'my_route_name_inflector_service'
```
*The service must implement the `BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\RouteNameInflectorInterface` interface.*

There are currently 2 inflectors available by default [`be_simple_i18n_routing.route_name_inflector.postfix`](src/Routing/RouteGenerator/NameInflector/PostfixInflector.php) and [`be_simple_i18n_routing.route_name_inflector.default_postfix`](src/Routing/RouteGenerator/NameInflector/DefaultPostfixInflector.php).

#### Default postfix inflector
The default postfix inflector changed the behaviour of to only add a locale postfix when the locale is not the default locale.
A example configuration is as followed.
```YAML
be_simple_i18n_routing:
  route_name_inflector: 'my_route_name_inflector_service'
  locales:
    default_locale: '%kernel.default_locale%'
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

This bundle is under the MIT License (MIT). Please see [License File](src/Resources/meta/LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/BeSimple/i18n-routing-bundle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/BeSimple/BeSimpleI18nRoutingBundle/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/BeSimple/i18n-routing-bundle.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/BeSimple/i18n-routing-bundle
[link-travis]: https://travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle
