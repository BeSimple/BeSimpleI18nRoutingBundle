<?php

namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Exception\MissingLocaleException;
use BeSimple\I18nRoutingBundle\Routing\Exception\MissingRouteLocaleException;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGeneratorInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class is partially a copy of @see \Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader.
 */
class AnnotatedRouteControllerLoader extends AnnotationClassLoader
{
    /**
     * @var RouteGeneratorInterface
     */
    private $routeGenerator;

    public function __construct(Reader $reader, RouteGeneratorInterface $routeGenerator = null)
    {
        parent::__construct($reader);

        $this->routeGenerator = $routeGenerator ?: new I18nRouteGenerator();
        $this->setRouteAnnotationClass('BeSimple\\I18nRoutingBundle\\Routing\\Annotation\\I18nRoute');
    }

    protected function addRoute(RouteCollection $collection, $annot, $globals, \ReflectionClass $class, \ReflectionMethod $method)
    {
        /** @var \BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute $annot */
        $name = $annot->getName();
        if (null === $name) {
            $name = $this->getDefaultRouteName($class, $method);
        }

        $defaults = array_replace($globals['defaults'], $annot->getDefaults());
        foreach ($method->getParameters() as $param) {
            if (!isset($defaults[$param->getName()]) && $param->isDefaultValueAvailable()) {
                $defaults[$param->getName()] = $param->getDefaultValue();
            }
        }
        $requirements = array_replace($globals['requirements'], $annot->getRequirements());
        $options = array_replace($globals['options'], $annot->getOptions());
        $schemes = array_merge($globals['schemes'], $annot->getSchemes());
        $methods = array_merge($globals['methods'], $annot->getMethods());

        $host = $annot->getHost();
        if (null === $host) {
            $host = $globals['host'];
        }

        $condition = $annot->getCondition();
        if (null === $condition && isset($globals['condition'])) {
            $condition = $globals['condition'];
        }

        $path = '';
        $localesWithPaths = $annot->getLocales();
        if (is_scalar($localesWithPaths)) {
            $routePath = $localesWithPaths;

            if (!is_array($globals['locales'])) {
                // This is a normal route
                $path = $globals['locales'].$localesWithPaths;
                $localesWithPaths = null;
            } else {
                // Global contains the locales
                $localesWithPaths = array();
                foreach ($globals['locales'] as $locale => $localePath) {
                    $localesWithPaths[$locale] = $localePath.$routePath;
                }
            }
        } elseif (is_array($localesWithPaths) && !empty($globals['locales'])) {
            if (!is_array($globals['locales'])) {
                // Global is a normal prefix
                foreach ($localesWithPaths as $locale => $localePath) {
                    $localesWithPaths[$locale] = $globals['locales'].$localePath;
                }
            } else {
                foreach ($localesWithPaths as $locale => $localePath) {
                    if (!isset($globals['locales'][$locale])) {
                        throw new MissingLocaleException(sprintf('Locale "%s" for controller %s::%s is expected to be part of the global configuration at class level.', $locale, $class->getName(), $method->getName()));
                    }
                    $localesWithPaths[$locale] = $globals['locales'][$locale].$localePath;
                }
            }
        } elseif (!is_array($localesWithPaths)) {
            throw new MissingRouteLocaleException(sprintf('Missing locales for controller %s::%s', $class->getName(), $method->getName()));
        }

        $route = $this->createRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $this->configureRoute($route, $class, $method, $annot);

        if (null === $localesWithPaths) {
            // Standard route
            $collection->add($name, $route);

            return;
        }

        $collection->addCollection(
                $this->routeGenerator->generateRoutes($name, $localesWithPaths, $route)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getGlobals(\ReflectionClass $class)
    {
        $globals = array(
            'locales' => '',
            'requirements' => array(),
            'options' => array(),
            'defaults' => array(),
            'schemes' => array(),
            'methods' => array(),
            'host' => '',
            'condition' => '',
        );

        /** @var \BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute $annot */
        if ($annot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass)) {
            if (null !== $annot->getLocales()) {
                $globals['locales'] = $annot->getLocales();
            }

            if (null !== $annot->getRequirements()) {
                $globals['requirements'] = $annot->getRequirements();
            }

            if (null !== $annot->getOptions()) {
                $globals['options'] = $annot->getOptions();
            }

            if (null !== $annot->getDefaults()) {
                $globals['defaults'] = $annot->getDefaults();
            }

            if (null !== $annot->getSchemes()) {
                $globals['schemes'] = $annot->getSchemes();
            }

            if (null !== $annot->getMethods()) {
                $globals['methods'] = $annot->getMethods();
            }

            if (null !== $annot->getHost()) {
                $globals['host'] = $annot->getHost();
            }

            if (null !== $annot->getCondition()) {
                $globals['condition'] = $annot->getCondition();
            }
        }

        return $globals;
    }

    /**
     * Configures the _controller default parameter of a given Route instance.
     *
     * @param Route             $route  A route instance
     * @param \ReflectionClass  $class  A ReflectionClass instance
     * @param \ReflectionMethod $method A ReflectionClass method
     * @param mixed             $annot  The annotation class instance
     *
     * @throws \LogicException When the service option is specified on a method
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        $route->setDefault('_controller', $class->getName().'::'.$method->getName());
    }

    /**
     * @inheritdoc
     *
     * @see \Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader::getDefaultRouteName
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace(array(
            '/(bundle|controller)_/',
            '/action(_\d+)?$/',
            '/__/',
        ), array(
            '_',
            '\\1',
            '_',
        ), $routeName);
    }
}
