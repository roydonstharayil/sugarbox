<?php

/**
 * blogUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class blogUrlMatcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(array $context = array(), array $defaults = array())
    {
        $this->context = $context;
        $this->defaults = $defaults;
    }

    public function match($url)
    {
        $url = $this->normalizeUrl($url);

        if (preg_match('#^/$#x', $url, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\DefaultController::indexAction',)), array('_route' => 'homepage'));
        }

        if (0 === strpos($url, '/blog') && preg_match('#^/blog$#x', $url, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Application\\BlogBundle\\Controller\\BlogController::indexAction',)), array('_route' => 'blog'));
        }

        if (0 === strpos($url, '/blog/new') && preg_match('#^/blog/new$#x', $url, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Application\\BlogBundle\\Controller\\BlogController::newAction',)), array('_route' => 'blog_new'));
        }

        return false;
    }
}
