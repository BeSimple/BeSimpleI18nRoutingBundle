<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteNameInflector;

use BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\PostfixInflector;

class PostfixInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testInflect()
    {
        $inflector = new PostfixInflector();

        $this->assertSame(
            'route.name.en',
            $inflector->inflect('route.name', 'en')
        );
        $this->assertSame(
            'route.name.nl',
            $inflector->inflect('route.name', 'nl')
        );
    }
}
