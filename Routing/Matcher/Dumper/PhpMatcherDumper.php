<?php

namespace BeSimple\I18nRoutingBundle\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper as BasePhpMatcherDumper;

class PhpMatcherDumper extends BasePhpMatcherDumper
{
    protected function addMatcher()
    {
        $code = array();

        foreach ($this->routes->all() as $name => $route) {
            if (method_exists($route, 'isI18n') && $route->isI18n()) {
                preg_match('/^(.+)_[^_]+$/', $name, $match);
                $name = $match[1];
            }

            $compiledRoute = $route->compile();

            $conditions = array();

            if ($req = $route->getRequirement('_method')) {
                $conditions[] = sprintf("isset(\$this->context['method']) && preg_match('#^(%s)$#xi', \$this->context['method'])", $req);
            }

            if ($compiledRoute->getStaticPrefix()) {
                $conditions[] = sprintf("0 === strpos(\$url, '%s')", $compiledRoute->getStaticPrefix());
            }

            $conditions[] = sprintf("preg_match('%s', \$url, \$matches)", $compiledRoute->getRegex());

            $conditions = implode(' && ', $conditions);

            $code[] = sprintf(<<<EOF
        if ($conditions) {
            return array_merge(\$this->mergeDefaults(\$matches, %s), array('_route' => '%s'));
        }

EOF
            , str_replace("\n", '', var_export($compiledRoute->getDefaults(), true)), $name);
        }

        $code = implode("\n", $code);

        return <<<EOF

    public function match(\$url)
    {
        \$url = \$this->normalizeUrl(\$url);

$code
        return false;
    }

EOF;
    }
}