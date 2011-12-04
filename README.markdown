I18nRoutingBundle, generate your I18N Routes for Symfony2
=========================================================

If you have a website multilingual, this bundle avoids of copy paste your routes
for different languages. Additionally it allows to translate given routing parameters
between languages in Router#match and UrlGenerator#generate using either a Symfony Translator
or a Doctrine DBAL (+Cache) based backend.

[![Build Status](https://secure.travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle.png)](http://travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle)

## Information

When you create an I18N route and you go on it with your browser, the locale will be updated.

## Installation

### Add I18nRoutingBundle to your vendor/bundles dir

    git submodule add git://github.com/BeSimple/BeSimpleI18nRoutingBundle.git vendor/bundles/BeSimple/I18nRoutingBundle

### Add I18nRoutingBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new BeSimple\I18nRoutingBundle\BeSimpleI18nRoutingBundle(),
            // ...
        );
    }

### Register the BeSimple namespace

    // app/autoload.php
    $loader->registerNamespaces(array(
        'BeSimple' => __DIR__.'/../vendor/bundles',
        // your other namespaces
    ));

### Update your configuration

    // app/config/config.yml
    be_simple_i18n_routing: ~

## Create your routing

### Yaml routing file

    homepage:
        locales:  { en: "/welcome", fr: "/bienvenue", de: "/willkommen" }
        defaults: { _controller: MyWebsiteBundle:Frontend:index }

### XML routing file

    <?xml version="1.0" encoding="UTF-8" ?>

    <routes xmlns="http://www.symfony-project.org/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

        <route id="homepage">
            <locale key="en">/welcome</locale>
            <locale key="fr">/bienvenue</locale>
            <locale key="de">/willkommen</locale>
            <default key="_controller">MyWebsiteBundle:Frontend:index</default>
        </route>
    </routes>

### PHP routing file

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

### You can insert classic route in your routing

#### Yaml routing file

    hello:
        pattern:  "/hello/{name}"
        defaults: { _controller: HelloBundle:Hello:index }

    homepage:
        locales:  { en: "/welcome/{name}", fr: "/bienvenue/{name}", de: "/willkommen/{name}" }
        defaults: { _controller: MyWebsiteBundle:Frontend:index }

#### XML routing file

    <?xml version="1.0" encoding="UTF-8" ?>

    <routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

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

#### PHP routing file

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

## Generate route in your templates

### Specify a locale

#### Twig

    {{ path('homepage.en') }}
    {{ path('homepage', { 'locale': 'en' }) }}
    {{ path('homepage.fr') }}
    {{ path('homepage', { 'locale': 'fr' }) }}
    {{ path('homepage.de') }}
    {{ path('homepage', { 'locale': 'de' }) }}

#### PHP

    <?php echo $view['router']->generate('homepage.en') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'en')) ?>
    <?php echo $view['router']->generate('homepage.fr') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'fr')) ?>
    <?php echo $view['router']->generate('homepage.de') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'de')) ?>

### Use current locale of user

#### Twig

    {{ path('homepage') }}

#### PHP

    <?php echo $view['router']->generate('homepage') ?>

## Translating Route Parameters

If the static parts of your routes are translated you get to the point really fast when dynamic parts
such as product slugs, category names or other dynamic routing parameters should be translated.

You can configure translation in your config.yml:

    // app/config/config.yml
    be_simple_i18n_routing:
        connection: default # Doctrine DBAL connection name
        cache: apc
        #use_translations: true # If you want to use Symfony translator

After this you can now define a to be translated attribute in your route defaults:

    product_view:
        locales: { en: "/product/{slug}", de: "/produkt/{slug}" }
        defaults: { _controller: "ShopBundle:Product:view", _translate: "slug" }
    product_view2:
        locales: { en: "/product/{category}/{slug}", de: "/produkt/{category}/{slug}" }
        defaults:
            _controller: "ShopBundle:Product:view"
            _translate: ["slug", "category"]

The same goes with generating routes, now backwards:

    {{ path("product_view", {"slug": product.slug, "translate": "slug"}) }}
    {{ path("product_view2", {"slug": product.slug, "translate": ["slug", "category]}) }}

The reverse translation is only necessary if you have the "original" values in your templates.
If you have access to the localized value of the current locale then you can just pass this
and do not hint to translate it with the "translate" key.

### Doctrine DBAL Backend

The Doctrine Backend has the following table structure:

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

Lookups are made through the combination of route name, locale and attribute of the route
to be translated.

Every lookup is cached in a Doctrine\Common\Cache\Cache instance that you should configure
to be APC, Memcache or Xcache for performance reasons.

If you are using Doctrine it automatically registers a listener for SchemaTool to create
the routing_translations table for your database backend, you only have to call:

    ./app/console doctrine:schema:update --dump-sql
    ./app/console doctrine:schema:update --force

