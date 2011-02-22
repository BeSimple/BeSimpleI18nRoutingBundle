<?php

namespace BeSimple\I18nRoutingBundle\Routing\Generator\Dumper;

use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper as BasePhpGeneratorDumper;
use BeSimple\I18nRoutingBundle\Routing\Route;

class PhpGeneratorDumper extends BasePhpGeneratorDumper
{
    protected function addGenerator()
    {
        $methods = array();

        foreach ($this->routes->all() as $name => $route) {
            $compiledRoute = $route->compile();

            $variables    = str_replace("\n", '', var_export($compiledRoute->getVariables(), true));
            $defaults     = str_replace("\n", '', var_export($route->getDefaults(), true));
            $requirements = str_replace("\n", '', var_export($compiledRoute->getRequirements(), true));
            $tokens       = str_replace("\n", '', var_export($compiledRoute->getTokens(), true));

            $escapedName  = str_replace('.', '__', $name);

            $methods[] = <<<EOF
    protected function get{$escapedName}RouteInfo()
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
        if (!isset(self::\$declaredRouteNames[\$name])) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', \$name));
        }

        \$escapedName = str_replace('.', '__', \$name);

        list(\$variables, \$defaults, \$requirements, \$tokens) = \$this->{'get'.\$escapedName.'RouteInfo'}();

        return \$this->doGenerate(\$variables, \$defaults, \$requirements, \$tokens, \$parameters, \$name, \$absolute);
    }

    public function generateI18n(\$name, \$locale, array \$parameters, \$absolute = false)
    {
        try {
            return \$this->generate(\$name.'_'.\$locale, \$parameters, \$absolute);
        } catch (\InvalidArgumentException \$e) {
            throw new \InvalidArgumentException(sprintf('I18nRoute "%s" (%s) does not exist.', \$name, \$locale));
        }
    }

$methods
EOF;
    }
}