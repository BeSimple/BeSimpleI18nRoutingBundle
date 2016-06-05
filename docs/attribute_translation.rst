Route Attribute Translation
===========================

If the static parts of your routes are translated you get to the point really
fast when dynamic parts such as product slugs, category names or other dynamic
routing parameters should be translated. The bundle provides 2 implementations.

After configuring the backend you want to use (see below for each one), you
can define a to be translated attribute in your route defaults:

.. code-block:: yaml

    product_view:
        locales: { en: "/product/{slug}", de: "/produkt/{slug}" }
        defaults: { _controller: "ShopBundle:Product:view", _translate: "slug" }

    product_view2:
        locales: { en: "/product/{category}/{slug}", de: "/produkt/{category}/{slug}" }
        defaults:
            _controller: "ShopBundle:Product:view"
            _translate: ["slug", "category"]

The same goes with generating routes, now backwards:

.. code-block:: html+jinja

    {{ path("product_view", {"slug": product.slug, "translate": "slug"}) }}
    {{ path("product_view2", {"slug": product.slug, "translate": ["slug", "category]}) }}

The reverse translation is only necessary if you have the "original" values
in your templates. If you have access to the localized value of the current
locale then you can just pass this and do not hint to translate it with the
"translate" key.

Doctrine DBAL Backend
---------------------

Configure the use of the DBAL backend

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        attribute_translator:
            type: doctrine_dbal
            connection: default # Doctrine DBAL connection name. Using null (default value) will use the default connection
            cache: apc

The Doctrine Backend has the following table structure:

.. code-block:: sql

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

Lookups are made through the combination of route name, locale and attribute
of the route to be translated.

Every lookup is cached in a Doctrine\Common\Cache\Cache instance that you
should configure to be APC, Memcache or Xcache for performance reasons.

If you are using Doctrine it automatically registers a listener for SchemaTool
to create the routing_translations table for your database backend, you only
have to call:

.. code-block:: bash

    ./app/console doctrine:schema:update --dump-sql
    ./app/console doctrine:schema:update --force

Translator backend
------------------

This implementation uses the Symfony2 translator to translate the attributes.
The translation domain will be created using the pattern `<route name>_<attribute name>`

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        attribute_translator:
            type: translator

Custom backend
~~~~~~~~~~~~~~

If you want to use a different implementation, simply create a service implementing
`BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface`.

.. code-block:: yaml

    # app/config/config.yml
    be_simple_i18n_routing:
        attribute_translator:
            type: service
            id: my_attribute_translator
