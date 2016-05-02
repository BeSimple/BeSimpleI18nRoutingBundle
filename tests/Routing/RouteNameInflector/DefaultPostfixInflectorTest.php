<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteNameInflector;

use BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\DefaultPostfixInflector;

class DefaultPostfixInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testInflect()
    {
        $inflector = new DefaultPostfixInflector('en');

        $this->assertSame(
            'route.name',
            $inflector->inflect('route.name', 'en')
        );
        $this->assertSame(
            'route.name.nl',
            $inflector->inflect('route.name', 'nl')
        );
    }
}
