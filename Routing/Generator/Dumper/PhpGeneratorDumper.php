<?php

namespace Bundle\I18nRoutingBundle\Routing\Generator\Dumper;

use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper as BasePhpGeneratorDumper;
use Bundle\I18nRoutingBundle\Routing\Route;

class PhpGeneratorDumper extends BasePhpGeneratorDumper
{
    protected function addGenerator()
    {
        $methods    = array();

        foreach ($this->routes->getRoutes() as $name => $route) {
            $compiledRoute = $route->compile();

            $variables = str_replace("\n", '', var_export($compiledRoute->getVariables(), true));
            $defaults = str_replace("\n", '', var_export($route->getDefaults(), true));
            $requirements = str_replace("\n", '', var_export($compiledRoute->getRequirements(), true));
            $tokens = str_replace("\n", '', var_export($compiledRoute->getTokens(), true));

            $methods[] = <<<EOF
    protected function get{$name}RouteInfo()
    {
        return array($variables, array_merge(\$this->defaults, $defaults), $requirements, $tokens);
    }

EOF
            ;
        }

        $methods = implode("\n", $methods);

        return <<<EOF

    public function generate(\$name, array \$parameters, \$absolute = false)
    {
        if (!method_exists(\$this, \$method = 'get'.\$name.'RouteInfo')) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', \$name));
        }

        list(\$variables, \$defaults, \$requirements, \$tokens) = \$this->\$method();

        return \$this->doGenerate(\$variables, \$defaults, \$requirements, \$tokens, \$parameters, \$name, \$absolute);
    }

    public function generateI18n(\$name, array \$parameters, \$locale, \$absolute = false)
    {
        try {
            return \$this->generate(\$locale.'_'.\$name, \$parameters, \$absolute);
        } catch (\InvalidArgumentException \$e) {
            throw new \InvalidArgumentException(sprintf('I18nRoute "%s" (%s) does not exist.', \$name, \$locale));
        }
    }

$methods
EOF;
    }
}