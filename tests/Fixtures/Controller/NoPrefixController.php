<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

class NoPrefixController
{
    /**
     * @I18nRoute({ "en": "/", "nl": "/nl/" })
     */
    public function indexAction()
    {
    }

    /**
     * @I18nRoute({ "en": "/new", "nl": "/nieuw" }, name="new_action")
     */
    public function newAction()
    {
    }
}
