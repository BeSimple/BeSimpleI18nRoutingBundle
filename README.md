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

## Documentation

The documentation for this bundle is available in the `docs` directory of the bundle:

* Read the [BeSimpleI18nRoutingBundle documentation](http://symfony.com/doc/master/bundles/KnpMenuBundle/index.html)

## License

This bundle is under the MIT License (MIT). Please see [License File](src/Resources/meta/LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/BeSimple/i18n-routing-bundle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/BeSimple/BeSimpleI18nRoutingBundle/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/BeSimple/i18n-routing-bundle.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/BeSimple/i18n-routing-bundle
[link-travis]: https://travis-ci.org/BeSimple/BeSimpleI18nRoutingBundle
