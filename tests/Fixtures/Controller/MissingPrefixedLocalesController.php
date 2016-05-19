<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

/**
 * @I18nRoute({ "en": "/en", "nl": "/nl" })
 */
class MissingPrefixedLocalesController
{
    /**
     * @I18nRoute({ "en": "/new", "nl": "/nieuw", "fr": "/nouveau" }, name="foo")
     */
    public function newAction()
    {
    }
}
