I18nRoutingBundle, generate your I18N Routes for Symfony2
=========================================================

If you have a website multilingual, this bundle avoids of copy paste your routes
for different languages.

## Information

When you create an I18N route and you go on it with your browser, the locale will be updated.

## Installation

### Add I18nRoutingBundle to your src/Bundle dir

    git submodule add git://github.com/francisbesset/I18nRoutingBundle.git src/BeSimple/I18nRoutingBundle

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
        'BeSimple' => __DIR__.'/../src',
        // your other namespaces
    ));

### Update your configuration

    // app/config/config.yml
    be_simple_i18n_routing: ~

## Create your routing

### Yaml routing file

    homepage:
        locales:  { en: /welcome, fr: /bienvenue, de: /willkommen }
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
        pattern:  /hello/{name}
        defaults: { _controller: HelloBundle:Hello:index }
    
    homepage:
        locales:  { en: /welcome/{name}, fr: /bienvenue/{name}, de: /willkommen/{name} }
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
