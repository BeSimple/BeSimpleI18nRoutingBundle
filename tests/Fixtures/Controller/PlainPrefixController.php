<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

/**
 * @I18nRoute("color")
 */
class PlainPrefixController
{
    /**
     * @I18nRoute({"en": "/", "test": "/test"}, name="idx")
     */
    public function indexAction()
    {
    }

    /**
     * @I18nRoute("/plain", name="new")
     */
    public function newAction()
    {
    }
}
