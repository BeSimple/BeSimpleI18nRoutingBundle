<?php
namespace BeSimple\I18nRoutingBundle\Routing\Exception;

class MissingLocaleException extends \RuntimeException implements ExceptionInterface
{
    public static function shouldSupportLocale(array $missingLocales)
    {
        return new self(sprintf(
            'Expected locale "%s" to be provided.',
            implode('", "', $missingLocales)
        ));
    }
}
