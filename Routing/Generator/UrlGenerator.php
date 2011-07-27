<?php

namespace BeSimple\I18nRoutingBundle\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class UrlGenerator extends BaseUrlGenerator
{
    public function generateI18n($name, $locale, $parameters = array(), $absolute = false)
    {
        try {
            return $this->generate($name.'.'.$locale, $parameters, $absolute);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('I18nRoute "%s" (%s) does not exist.', $name, $locale));
        }
    }
}
