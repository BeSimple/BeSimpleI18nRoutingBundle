Route Generation
================

Using a specify locale
----------------------

.. configuration-block::

    .. code-block:: html+jinja

        {{ path('homepage.en') }}
        {{ path('homepage', { 'locale': 'en' }) }}

        {{ path('homepage.fr') }}
        {{ path('homepage', { 'locale': 'fr' }) }}

        {{ path('homepage.de') }}
        {{ path('homepage', { 'locale': 'de' }) }}

    .. code-block:: html+php

        <?php echo $view['router']->generate('homepage.en') ?>
        <?php echo $view['router']->generate('homepage', array('locale' => 'en')) ?>
        <?php echo $view['router']->generate('homepage.fr') ?>
        <?php echo $view['router']->generate('homepage', array('locale' => 'fr')) ?>
        <?php echo $view['router']->generate('homepage.de') ?>
        <?php echo $view['router']->generate('homepage', array('locale' => 'de')) ?>


.. note::

    When using the locale to generate the route make sure you use the ``locale`` parameter and not ``_locale``.


Using the current locale
------------------------

.. configuration-block::

    .. code-block:: html+jinja

        {{ path('homepage') }}

    .. code-block:: html+php

        <?php echo $view['router']->generate('homepage') ?>
