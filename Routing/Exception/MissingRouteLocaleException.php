<?php
namespace BeSimple\I18nRoutingBundle\Routing\Exception;

/**
 * Exception thrown when a route has no locale or a prefix is missing a required locale.
 *
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 *
 * @api
 */
class MissingRouteLocaleException extends \InvalidArgumentException implements ExceptionInterface
{
}
