<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

class MissingLocalesController
{
    /**
     * @I18nRoute(name="foo")
     */
    public function fooAction()
    {
    }
}
