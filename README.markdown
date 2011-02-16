I18nRoutingBundle, generate your I18N Routes for Symfony2
=========================================================

If you have a website multilingual, this bundle avoids of copy paste your routes
for different languages.

## Information

When you create an i18n route and you go on it with your browser, the locale will be updated.

## Installation

### Add I18nRoutingBundle to your src/Bundle dir

    git submodule add git://github.com/francisbesset/I18nRoutingBundle.git src/Bundle/I18nRoutingBundle

### Add I18nRoutingBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\I18nRoutingBundle\I18nRoutingBundle(),
            // ...
        );
    }

### Update your config

#### Move router in app.config to i18n_routing.config

     // app/config/config.yml
     i18n_routing:
         router: { resource: "%kernel.root_dir%/config/routing.yml" }
     
     // app/config/config_dev.yml
     i18n_routing:
          router: { resource: "%kernel.root_dir%/config/routing_dev.yml" }

## Create your routing

### Yaml routing file

    homepage:
        locales: { en: /welcome, fr: /bienvenue, de: /willkommen }
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
    
    use Symfony\Component\Routing\RouteCollection;
    use Bundle\I18nRoutingBundle\Routing\I18nRoute;
    
    $collection = new RouteCollection();
    $route = new I18nRoute('homepage', array(
            'en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen',
        ), array('_controller' => 'MyWebsiteBundle:Frontend:index',)
    );
    $collection->addCollection($route->getCollection());
    
    return $collection;

### You can insert classic route in your routing

#### Yaml routing file

    hello:
        pattern:  /hello/:name
        defaults: { _controller: HelloBundle:Hello:index }
    
    homepage:
        locales: { en: /welcome, fr: /bienvenue, de: /willkommen }
        defaults: { _controller: MyWebsiteBundle:Frontend:index }

#### XML routing file

    <?xml version="1.0" encoding="UTF-8" ?>
    
    <routes xmlns="http://www.symfony-project.org/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">
    
        <route id="hello" pattern="/hello/:name">
            <default key="_controller">HelloBundle:Hello:index</default>
        </route>
    
        <route id="homepage">
                <locale key="en">/welcome</locale>
                <locale key="fr">/bienvenue</locale>
                <locale key="de">/willkommen</locale>
                <default key="_controller">MyWebsiteBundle:Frontend:index</default>
            </route>
    </routes>

#### PHP routing file

    <?php
    
    use Symfony\Component\Routing\RouteCollection;
    use Bundle\I18nRoutingBundle\Routing\Route;
    use Bundle\I18nRoutingBundle\Routing\I18nRoute;
    
    $collection = new RouteCollection();
    $collection->add('hello', new Route('/hello/:name', array(
        '_controller' => 'HelloBundle:Hello:index',
    )));
    $route = new I18nRoute('homepage', array(
            'en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen',
        ), array('_controller' => 'MyWebsiteBundle:Frontend:index',)
    );
    $collection->addCollection($route->getCollection());
    
    return $collection;

## Generate route in your templates

### Twig

    {% path homepage_en %}
    {% path homepage with ['locale': en] %}
    {% path homepage_fr %}
    {% path homepage with ['locale': fr] %}
    {% path homepage_de %}
    {% path homepage with ['locale': de] %}

### PHP

    <?php echo $view['router']->generate('homepage_en') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'en')) ?>
    <?php echo $view['router']->generate('homepage_fr') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'fr')) ?>
    <?php echo $view['router']->generate('homepage_de') ?>
    <?php echo $view['router']->generate('homepage', array('locale' => 'de')) ?>

