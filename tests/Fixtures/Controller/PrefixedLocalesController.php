<?php

namespace BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller;

use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

/**
 * @I18nRoute({ "en": "/en", "nl": "/nl", "fr": "/fr" })
 */
class PrefixedLocalesController
{
    /**
     * @I18nRoute("/", name="idx")
     */
    public function indexAction()
    {
    }

    /**
     * @I18nRoute({ "en": "/edit" }, name="edit")
     */
    public function editAction()
    {
    }

    /**
     * @I18nRoute({ "en": "/new", "nl": "/nieuw", "fr": "/nouveau" }, name="new")
     */
    public function newAction()
    {
    }
}
