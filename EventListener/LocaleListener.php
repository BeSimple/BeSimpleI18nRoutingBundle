<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BeSimple\I18nRoutingBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Initializes the locale based on the current request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LocaleListener implements EventSubscriberInterface
{

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $_locale = $request->query->get('_locale');
        if($_locale){
            $request->setLocale($_locale);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _locale
            KernelEvents::REQUEST => array(array('onKernelRequest', 15))
        );
    }

    private function setLocale(Request $request, $locale)
    {
        $request->setLocale($locale);
    }
}
