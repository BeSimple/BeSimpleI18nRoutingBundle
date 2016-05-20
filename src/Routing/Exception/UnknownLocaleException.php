<?php
namespace BeSimple\I18nRoutingBundle\Routing\Exception;

class UnknownLocaleException extends \RuntimeException implements ExceptionInterface
{
    public static function unexpectedLocale(array $unknownLocales, array $locales)
    {
        return new self(sprintf(
            'Unexpected locale "%s" found. Supported locales are "%s".',
            implode('", "', $unknownLocales),
            implode('", "', $locales)
        ));
    }
}
