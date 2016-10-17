<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator\NameInflector;


use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\PostfixInflector;

class PostfixInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testInflect()
    {
        $inflector = new PostfixInflector();

        $this->assertSame(
            'route.name.be-simple-i18n.en',
            $inflector->inflect('route.name', 'en')
        );
        $this->assertSame(
            'route.name.be-simple-i18n.nl',
            $inflector->inflect('route.name', 'nl')
        );
    }
}
