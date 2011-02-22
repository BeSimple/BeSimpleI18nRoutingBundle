<?php

namespace BeSimple\I18nRoutingBundle\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper as BasePhpMatcherDumper;
use BeSimple\I18nRoutingBundle\Routing\Route;

class PhpMatcherDumper extends BasePhpMatcherDumper
{
    protected function addMatcher()
    {
        $code = array();

        foreach ($this->routes->all() as $name => $route) {
            if ($route instanceof Route && $route->isI18n()) {
                preg_match('/^(.+)_[^_]+$/', $name, $match);
                $name = $match[1];
            }

            $compiledRoute = $route->compile();

            $conditions = array();

            if ($req = $route->getRequirement('_method')) {
                $conditions[] = sprintf("isset(\$this->context['method']) && preg_match('#^(%s)$#xi', \$this->context['method'])", $req);
            }

            $hasTrailingSlash = false;
            if (!count($compiledRoute->getVariables()) && false !== preg_match('#^(.)\^(?P<url>.*?)\$\1#', $compiledRoute->getRegex(), $m)) {
                if (substr($m['url'], -1) === '/' && $m['url'] !== '/') {
                    $conditions[] = sprintf("rtrim(\$url, '/') === '%s'", rtrim(str_replace('\\', '', $m['url']), '/'));
                    $hasTrailingSlash = true;
                } else {
                    $conditions[] = sprintf("\$url === '%s'", str_replace('\\', '', $m['url']));
                }

                $matches = 'array()';
            } else {
                if ($compiledRoute->getStaticPrefix()) {
                    $conditions[] = sprintf("0 === strpos(\$url, '%s')", $compiledRoute->getStaticPrefix());
                }

                $regex = $compiledRoute->getRegex();
                if ($pos = strpos($regex, '/$')) {
                    $regex = substr($regex, 0, $pos) . '/?$' . substr($regex, $pos+2);
                    $conditions[] = sprintf("preg_match('%s', \$url, \$matches)", $regex);
                    $hasTrailingSlash = true;
                } else {
                    $conditions[] = sprintf("preg_match('%s', \$url, \$matches)", $regex);
                }

                $matches = '$matches';
            }

            $conditions = implode(' && ', $conditions);

            $code[] = <<<EOF
        if ($conditions) {
EOF;

            if ($hasTrailingSlash) {
                $code[] = sprintf(<<<EOF
            if (substr(\$url, -1) !== '/') {
                return array('_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction', 'url' => \$this->context['base_url'].\$url.'/', 'permanent' => true, '_route' => '%s');
            }
EOF
            , $name);
            }

            $code[] = sprintf(<<<EOF
            return array_merge(\$this->mergeDefaults($matches, %s), array('_route' => '%s'));
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