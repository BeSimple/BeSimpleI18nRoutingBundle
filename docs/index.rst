Using BeSimpleI18nRoutingBundle
===============================

Welcome to BeSimpleI18nRoutingBundle -  generate I18N Routes simply and quickly

.. toctree::
    :hidden:

    self

Installation
------------

Step 1: Download the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require besimple/i18n-routing-bundle "^3.0"

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md

Step 2: Enable the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

Then, enable the bundle by adding the following line in the ``app/AppKernel.php``
file of your project:

.. code-block:: php

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new BeSimple\I18nRoutingBundle\BeSimpleI18nRoutingBundle(),
            );

            // ...
        }

        // ...
    }


Step 3: (optional) Configure the bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The bundle comes with a sensible default configuration, which is listed below.
If you skip this step, these defaults will be used.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        #  if true, enables the annotation route loader
        annotations: true

        locales:
            # the default locale, used when generating routes
            default_locale: null
            # the supported locales used by "filter" and "strict"
            supported: []
            #  if true, filter out any locales not in "supported"
            filter: false
            #  if true, filter out any locales not in "supported"
            strict: false

        attribute_translator:
            #  if true, enables the attribute translator
            enabled: false

            # the type of attribute translator to use
            type: translator
            # When defining type as "service" then
            # add the id parameter with the service id to use (e.g. id: "my_attribute_translator_service_id")
            # and ensure the service implements "\BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface"
            #
            # When defining type as "doctrine_dbal" then
            # optionally add the connection parameter to set the dbal connection name (e.g. connection: "connection_name")
            # optionally add a caching configuration using the cache parameter:
            #   type: array | apc | xcache | memcache
            #   when the cache type is "memcache" then (optionally) add the connection information:
            #       type: memcache
            #       host: 127.0.0.1
            #       port: 11211
            #       instance_class: Memcache
            #       class: \Doctrine\Common\Cache\MemcacheCache

        # the route name inflector service
        route_name_inflector: 'be_simple_i18n_routing.route_name_inflector.postfix'


Create your localized routes!
-----------------------------

Importing Routes
~~~~~~~~~~~~~~~~

To define internationalized routes, you need to import the routing file using the ``be_simple_i18n`` type:

.. code-block:: yaml

    # app/config/routing.yml

    my_yaml_i18n_routes:
        type: be_simple_i18n
        resource: "@AppBundle/Resources/config/routing/i18n.yml"

    my_xml_i18n_routes:
        type: be_simple_i18n
        resource: "@AppBundle/Resources/config/routing/i18n.xml"

    # For annotation support ensure that annotations is true in the configuration
    my_annotation_i18n_routes:
        resource: '@AppBundle/Controller/'
        type:     annotation

Defining Routes
~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # @AppBundle/Resources/config/routing/i18n.yml
        homepage:
            path:      /blog/{slug}
            defaults:  { _controller: AppBundle:Frontend:index }

    .. code-block:: xml

        <!-- @AppBundle/Resources/config/routing/i18n.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://besim.pl/schema/i18n_routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://besim.pl/schema/i18n_routing http://besim.pl/schema/i18n_routing/routing-1.0.xsd">

            <route id="homepage">
                <locale key="en">/welcome</locale>
                <locale key="fr">/bienvenue</locale>
                <locale key="de">/willkommen</locale>
                <default key="_controller">AppBundle:Frontend:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;

        $generator = new I18nRouteGenerator();

        $collection = new RouteCollection();
        $collection->addCollection(
            $generator->generateRoutes(
                'homepage',
                array(
                    'en' => '/welcome',
                    'fr' => '/bienvenue',
                    'de' => '/willkommen'
                ),
                new Route('', array(
                    '_controller' => 'AppBundle:Frontend:index'
                ))
            )
        );

        return $collection;

    .. code-block:: php-annotations

        // @AppBundle/Controller/BlogController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

        class FrontendController extends Controller
        {
            /**
             * @I18nRoute({ "en": "/welcome/{name}", "fr": "/bienvenue/{name}", "de": "/willkommen/{name}" }, name="homepage")
             */
            public function indexAction($name)
            {
                // ...
            }
        }

Using Normal Routes
~~~~~~~~~~~~~~~~~~~

Sometimes you have routes that don't need to be translated.
To allow this simply add the routes as followed.


.. configuration-block::

    .. code-block:: yaml

        # @AppBundle/Resources/config/routing/i18n.yml
        homepage:
            path:  "/{name}"
            defaults: { _controller: AppBundle:Hello:index }

        welcome:
            locales:  { en: "/welcome/{name}", fr: "/bienvenue/{name}", de: "/willkommen/{name}" }
            defaults: { _controller: AppBundle:Frontend:welcome }

    .. code-block:: xml

        <!-- @AppBundle/Resources/config/routing/i18n.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://besim.pl/schema/i18n_routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://besim.pl/schema/i18n_routing http://besim.pl/schema/i18n_routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/{name}">
                <default key="_controller">AppBundle:Hello:index</default>
            </route>
                <route id="homepage">
                <locale key="en">/welcome/{name}</locale>
                <locale key="fr">/bienvenue/{name}</locale>
                <locale key="de">/willkommen/{name}</locale>
                <default key="_controller">AppBundle:Frontend:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $generator = new I18nRouteGenerator();

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'AppBundle:Hello:index',
        )));
        $collection->addCollection(
            $generator->generateRoutes(
                'homepage',
                array('en' => '/welcome/{name}', 'fr' => '/bienvenue/{name}', 'de' => '/willkommen/{name}'),
                new Route('', array(
                    '_controller' => 'AppBundle:Frontend:index',
                ))
            )
        );

        return $collection;

    .. code-block:: php-annotations

        // @AppBundle/Controller/BlogController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

        class FrontendController extends Controller
        {
            /**
             * @I18nRoute("/{name}", name="hello")
             */
            public function helloAction($slug)
            {
                // ...
            }

            /**
             * @I18nRoute({ "en": "/welcome/{name}", "fr": "/bienvenue/{name}", "de": "/willkommen/{name}" }, name="homepage")
             */
            public function indexAction($name)
            {
                // ...
            }
        }

Prefixing Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to provide a "prefix" for the imported routes.
Combining this with normal routes will automatically localize them.


.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app:
            resource: '@AppBundle/Controller/'
            type: be_simple_i18n
            prefix:
                en: /website
                fr: /site
                de: /webseite

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@AppBundle/Resources/config/routing/i18n.xml" type="be_simple_i18n">
                <locale key="en">/english</locale>
                <locale key="de">/german</locale>
                <locale key="fr">/french</locale>
            </import>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
        use Symfony\Component\Routing\RouteCollection;

        $generator = new I18nRouteGenerator();

        $app = $loader->import('@AppBundle/Controller/', 'annotation');
        $app = $generator->generateCollection(array(
            'en' => '/english',
            'de' => '/german',
            'fr' => '/french',
        ), $app);

        $collection = new RouteCollection();
        $collection->addCollection($app);

        return $collection;


More Stuff
----------

.. toctree::
    :maxdepth: 1

    route_generation
    attribute_translation
    customize_router
