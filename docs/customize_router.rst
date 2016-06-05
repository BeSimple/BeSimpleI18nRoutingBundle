Customizing the Router
======================

Route Naming
------------

By default all routes that are imported are named '<route_name>.<locale>' but sometimes you may want to change this behaviour.
To do this you can specify a route name inflector service in your configuration as followed.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        # Service must implement the `BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\RouteNameInflectorInterface`
        route_name_inflector: "my_route_name_inflector_service"

There are currently 2 inflectors available by default ``be_simple_i18n_routing.route_name_inflector.postfix`` and ``be_simple_i18n_routing.route_name_inflector.default_postfix``.

Default Postfix Inflector
~~~~~~~~~~~~~~~~~~~~~~~~~

The default postfix inflector changed the behaviour to only add a locale postfix when the locale is not the default locale.
For this to work correctly you must configure a ``default_locale``.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        route_name_inflector: 'be_simple_i18n_routing.route_name_inflector.default_postfix'
        locales:
            default_locale: '%kernel.default_locale%'


Route Filtering
---------------

During the development of your system you may want to filter out a (not fully) implemented locale.
This can be done why configuring the supported locales and enabling filtering.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        locales:
            supported: ['en', 'nl']
            filter: true

The above configuration will only generate routes for the ``en`` and ``nl`` locale and will ignore any other locales.


Strict Route Locales
--------------------

During development it can be nice to ensure that all routes are localized.
This can be done why configuring the supported locales and enabling strict localization.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        locales:
            supported: ['en', 'nl']
            strict: true

The ``strict`` options has 3 settings:

- ``true`` for throwing a exception when a i18n route is found where the locale is unknown or where a locale is missing.
- ``null`` for throwing a exception where a locale is missing.
- ``false`` for disabling strict routes (default)
