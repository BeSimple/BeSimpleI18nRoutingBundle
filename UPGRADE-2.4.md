# UPGRADE FROM 2.3 to 2.4

## Configuration

* The `locale` container parameter was used for the default locale this need to be specified from now on.

  After:  
  ```YAML  
  # app/config/config.yml
  be_simple_i18n_routing:
      locales:
          default_locale: "%locale%"
  ```

## Routing

* The `I18nRouteCollectionBuilder` class has been removed in favor of `I18nRouteGenerator`. 

  Before:  
  ```PHP
  use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollectionBuilder; 
  
  $builder = new I18nRouteCollectionBuilder();
  $builder->buildCollection(
      'homepage',
      array('en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen'),
      array('_controller' => 'MyWebsiteBundle:Frontend:index')
  );
  ```
  
  After:  
  ```PHP
  use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
  
  $generator = new I18nRouteGenerator();
  $generator->generateRoutes(
      'homepage',
      array('en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen'),
      new Route('', array(
          '_controller' => 'MyWebsiteBundle:Frontend:index'
      ))
  );  
  ```
  
* The `I18nRouteCollection` class has been removed in favor of using the Symfony `RouteCollection` with the `I18nRouteGenerator`.

  Before:
  ```PHP
  use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollection;
  
  $collection = new I18nRouteCollection();
  $collection->addResource(new FileResource($path));
  $collection->addPrefix($prefix);
  ```
  
  After:
  ```PHP
  use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
  
  $collection = new \Symfony\Component\Routing\RouteCollection();
  $collection->addResource(new FileResource($path));
  
  $generator = new I18nRouteGenerator();
  $collection = $generator->generateCollection($prefix, $collection);
  ```

## Loaders

* The `XmlFileLoader` and `YamlFileLoader` now required a `RouteGeneratorInterface` instance or null as a second constructor parameter instead of the now removed `BeSimple\I18nRoutingBundle\Routing\I18nRouteCollectionBuilder`. 

* The `XmlFileLoader` class no longer inherits from `Symfony\Component\Routing\Loader\XmlFileLoader`. You may need to change your code hints from `Symfony\Component\Routing\Loader\XmlFileLoader` to `Symfony\Component\Config\Loader\FileLoader`.
 
* The `YamlFileLoader` class no longer inherits from `Symfony\Component\Routing\Loader\YamlFileLoader`. You may need to change your code hints from `Symfony\Component\Routing\Loader\YamlFileLoader` to `Symfony\Component\Config\Loader\FileLoader`.
