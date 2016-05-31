<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

/**
 * @I18nRoute("/base")
 */
class NoLocalesController
{
    /**
     * @I18nRoute("/", name="index")
     */
    public function indexAction()
    {
    }

    /**
     * @I18nRoute("/new", name="new")
     */
    public function newAction()
    {
    }
}
