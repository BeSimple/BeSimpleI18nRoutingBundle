<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator\NameInflector;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\DefaultPostfixInflector;

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
