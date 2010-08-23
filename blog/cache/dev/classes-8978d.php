<?php
namespace Symfony\Component\Routing;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
interface RouterInterface extends UrlMatcherInterface, UrlGeneratorInterface { }
namespace Symfony\Component\Routing;
use Symfony\Component\Routing\Loader\LoaderInterface;
class Router implements RouterInterface {
    protected $matcher;
    protected $generator;
    protected $options;
    protected $defaults;
    protected $context;
    protected $loader;
    protected $collection;
    protected $resource;
    public function __construct(LoaderInterface $loader, $resource, array $options = array(), array $context = array(), array $defaults = array()) {
        $this->loader = $loader;
        $this->resource = $resource;
        $this->context = $context;
        $this->defaults = $defaults;
        $this->options = array(
            'cache_dir'              => null,
            'debug'                  => false,
            'generator_class'        => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'generator_base_class'   => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'generator_cache_class'  => 'ProjectUrlGenerator',
            'matcher_class'          => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'matcher_base_class'     => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'matcher_dumper_class'   => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'matcher_cache_class'    => 'ProjectUrlMatcher',
        );
                if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the following options: \'%s\'.', implode('\', \'', $diff))); }
        $this->options = array_merge($this->options, $options); }
    public function getRouteCollection() {
        if (null === $this->collection) {
            $this->collection = $this->loader->load($this->resource); }
        return $this->collection; }
    public function setContext(array $context = array()) {
        $this->context = $context; }
    public function setDefaults(array $defaults = array()) {
        $this->defaults = $defaults; }
    public function generate($name, array $parameters = array(), $absolute = false) {
        return $this->getGenerator()->generate($name, $parameters, $absolute); }
    public function match($url) {
        return $this->getMatcher()->match($url); }
    public function getMatcher() {
        if (null !== $this->matcher) {
            return $this->matcher; }
        if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
            return $this->matcher = new $this->options['matcher_class']($this->getRouteCollection(), $this->context, $this->defaults); }
        $class = $this->options['matcher_cache_class'];
        if ($this->needsReload($class)) {
            $dumper = new $this->options['matcher_dumper_class']($this->getRouteCollection());
            $options = array(
                'class'      => $class,
                'base_class' => $this->options['matcher_base_class'],
            );
            $this->updateCache($class, $dumper->dump($options)); }
        require_once $this->getCacheFile($class);
        return $this->matcher = new $class($this->context, $this->defaults); }
    public function getGenerator() {
        if (null !== $this->generator) {
            return $this->generator; }
        if (null === $this->options['cache_dir'] || null === $this->options['generator_cache_class']) {
            return $this->generator = new $this->options['generator_class']($this->getRouteCollection(), $this->context, $this->defaults); }
        $class = $this->options['generator_cache_class'];
        if ($this->needsReload($class)) {
            $dumper = new $this->options['generator_dumper_class']($this->getRouteCollection());
            $options = array(
                'class'      => $class,
                'base_class' => $this->options['generator_base_class'],
            );
            $this->updateCache($class, $dumper->dump($options)); }
        require_once $this->getCacheFile($class);
        return $this->generator = new $class($this->context, $this->defaults); }
    protected function updateCache($class, $dump) {
        $this->writeCacheFile($this->getCacheFile($class), $dump);
        if ($this->options['debug']) {
            $this->writeCacheFile($this->getCacheFile($class, 'meta'), serialize($this->getRouteCollection()->getResources())); } }
    protected function needsReload($class) {
        $file = $this->getCacheFile($class);
        if (!file_exists($file)) {
            return true; }
        if (!$this->options['debug']) {
            return false; }
        $metadata = $this->getCacheFile($class, 'meta');
        if (!file_exists($metadata)) {
            return true; }
        $time = filemtime($file);
        $meta = unserialize(file_get_contents($metadata));
        foreach ($meta as $resource) {
            if (!$resource->isUptodate($time)) {
                return true; } }
        return false; }
    protected function getCacheFile($class, $extension = 'php') {
        return $this->options['cache_dir'].'/'.$class.'.'.$extension; }
    protected function writeCacheFile($file, $content) {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
            chmod($file, 0644);
            return; }
        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file)); } }
namespace Symfony\Component\Routing\Matcher;
interface UrlMatcherInterface {
    public function match($url); }
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class UrlMatcher implements UrlMatcherInterface {
    protected $routes;
    protected $defaults;
    protected $context;
    public function __construct(RouteCollection $routes, array $context = array(), array $defaults = array()) {
        $this->routes = $routes;
        $this->context = $context;
        $this->defaults = $defaults; }
    public function match($url) {
        $url = $this->normalizeUrl($url);
        foreach ($this->routes->getRoutes() as $name => $route) {
            $compiledRoute = $route->compile();
                        if (isset($this->context['method']) && (($req = $route->getRequirement('_method')) && !in_array(strtolower($this->context['method']), array_map('strtolower', (array) $req)))) {
                continue; }
                        if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($url, $compiledRoute->getStaticPrefix())) {
                continue; }
            if (!preg_match($compiledRoute->getRegex(), $url, $matches)) {
                continue; }
            return array_merge($this->mergeDefaults($matches, $route->getDefaults()), array('_route' => $name)); }
        return false; }
    protected function mergeDefaults($params, $defaults) {
        $parameters = array_merge($this->defaults, $defaults);
        foreach ($params as $key => $value) {
            if (!is_int($key)) {
                $parameters[$key] = urldecode($value); } }
        return $parameters; }
    protected function normalizeUrl($url) {
                if ('/' !== substr($url, 0, 1)) {
            $url = '/'.$url; }
                if (false !== $pos = strpos($url, '?')) {
            $url = substr($url, 0, $pos); }
                return preg_replace('#/+#', '/', $url); } }
namespace Symfony\Component\Routing\Generator;
interface UrlGeneratorInterface {
    public function generate($name, array $parameters, $absolute = false); }
namespace Symfony\Component\Routing\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class UrlGenerator implements UrlGeneratorInterface {
    protected $routes;
    protected $defaults;
    protected $context;
    protected $cache;
    public function __construct(RouteCollection $routes, array $context = array(), array $defaults = array()) {
        $this->routes = $routes;
        $this->context = $context;
        $this->defaults = $defaults;
        $this->cache = array(); }
    public function generate($name, array $parameters, $absolute = false) {
        if (null === $route = $this->routes->getRoute($name)) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', $name)); }
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $route->compile(); }
        return $this->doGenerate($this->cache[$name]->getVariables(), $route->getDefaults(), $route->getRequirements(), $this->cache[$name]->getTokens(), $parameters, $name, $absolute); }
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute) {
        $defaults = array_merge($this->defaults, $defaults);
        $tparams = array_merge($defaults, $parameters);
                if ($diff = array_diff_key($variables, $tparams)) {
            throw new \InvalidArgumentException(sprintf('The "%s" route has some missing mandatory parameters (%s).', $name, implode(', ', $diff))); }
        $url = '';
        $optional = true;
        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                if (false === $optional || !isset($defaults[$token[3]]) || (isset($parameters[$token[3]]) && $parameters[$token[3]] != $defaults[$token[3]])) {
                                        if (isset($requirements[$token[3]]) && !preg_match('#^'.$requirements[$token[3]].'$#', $tparams[$token[3]])) {
                        throw new \InvalidArgumentException(sprintf('Parameter "%s" for route "%s" must match "%s" ("%s" given).', $token[3], $name, $requirements[$token[3]], $tparams[$token[3]])); }
                    $url = $token[1].urlencode($tparams[$token[3]]).$url;
                    $optional = false; } } elseif ('text' === $token[0]) {
                $url = $token[1].$token[2].$url;
                $optional = false; } else {
                                if ($segment = call_user_func_array(array($this, 'generateFor'.ucfirst(array_shift($token))), array_merge(array($optional, $tparams), $token))) {
                    $url = $segment.$url;
                    $optional = false; } } }
        if (!$url) {
            $url = '/'; }
                if ($extra = array_diff_key($parameters, $variables, $defaults)) {
            $url .= '?'.http_build_query($extra); }
        $url = (isset($this->context['base_url']) ? $this->context['base_url'] : '').$url;
        if ($absolute && isset($this->context['host'])) {
            $url = 'http'.(isset($this->context['is_secure']) && $this->context['is_secure'] ? 's' : '').'://'.$this->context['host'].$url; }
        return $url; } }
namespace Symfony\Component\Routing\Loader;
abstract class Loader implements LoaderInterface {
    protected $resolver;
    public function getResolver() {
        return $this->resolver; }
    public function setResolver(LoaderResolver $resolver) {
        $this->resolver = $resolver; }
    public function import($resource) {
        $this->resolve($resource)->load($resource); }
    public function resolve($resource) {
        $loader = false;
        if ($this->supports($resource)) {
            $loader = $this; } elseif (null !== $this->resolver) {
            $loader = $this->resolver->resolve($resource); }
        if (false === $loader) {
            throw new \InvalidArgumentException(sprintf('Unable to load the "%s" routing resource.', is_string($resource) ? $resource : (is_object($resource) ? get_class($resource) : 'RESOURCE'))); }
        return $loader; } }
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Routing\RouteCollection;
class DelegatingLoader extends Loader {
    protected $resolver;
    public function __construct(LoaderResolverInterface $resolver) {
        $this->resolver = $resolver; }
    public function load($resource) {
        $loader = $this->resolver->resolve($resource);
        if (false === $loader) {
            throw new \InvalidArgumentException(sprintf('Unable to load the "%s" routing resource.', is_string($resource) ? $resource : (is_object($resource) ? get_class($resource) : 'RESOURCE'))); }
        return $loader->load($resource); }
    public function supports($resource) {
        foreach ($this->resolver->getLoaders() as $loader) {
            if ($loader->supports($resource)) {
                return true; } }
        return false; } }
namespace Symfony\Component\Routing\Loader;
class LoaderResolver implements LoaderResolverInterface {
    protected $loaders;
    public function __construct(array $loaders = array()) {
        $this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader); } }
    public function resolve($resource) {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                return $loader; } }
        return false; }
    public function addLoader(LoaderInterface $loader) {
        $this->loaders[] = $loader;
        $loader->setResolver($this); }
    public function getLoaders() {
        return $this->loaders; } }
namespace Symfony\Bundle\FrameworkBundle\Routing;
use Symfony\Component\Routing\Loader\LoaderResolver as BaseLoaderResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\LoaderInterface;
class LoaderResolver extends BaseLoaderResolver {
    protected $services;
    protected $container;
    public function __construct(ContainerInterface $container, array $loaders = array()) {
        parent::__construct($loaders);
        $this->container = $container;
        foreach ($container->findTaggedServiceIds('routing.loader') as $id => $attributes) {
            $this->services[] = $id; } }
    public function resolve($resource) {
        if (count($this->services)) {
            while ($id = array_shift($this->services)) {
                $this->addLoader($this->container->get($id)); } }
        return parent::resolve($resource); } }
namespace Symfony\Bundle\FrameworkBundle\Routing;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameConverter;
use Symfony\Component\Routing\Loader\DelegatingLoader as BaseDelegatingLoader;
use Symfony\Component\Routing\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
class DelegatingLoader extends BaseDelegatingLoader {
    protected $converter;
    protected $logger;
    public function __construct(ControllerNameConverter $converter, LoggerInterface $logger = null, LoaderResolverInterface $resolver) {
        $this->converter = $converter;
        $this->logger = $logger;
        parent::__construct($resolver); }
    public function load($resource) {
        $collection = parent::load($resource);
        foreach ($collection->getRoutes() as $name => $route) {
            if ($controller = $route->getDefault('_controller')) {
                try {
                    $controller = $this->converter->fromShortNotation($controller); } catch (\Exception $e) {
                    throw new \RuntimeException(sprintf('%s (for route "%s" in resource "%s")', $e->getMessage(), $name, is_string($resource) ? $resource : 'RESOURCE'), $e->getCode(), $e); }
                $route->setDefault('_controller', $controller); } }
        return $collection; } }
namespace Symfony\Component\HttpFoundation;
class ParameterBag {
    protected $parameters;
    public function __construct(array $parameters = array()) {
        $this->replace($parameters); }
    public function all() {
        return $this->parameters; }
    public function replace(array $parameters = array()) {
        $this->parameters = $parameters; }
    public function add(array $parameters = array()) {
        $this->parameters = array_replace($this->parameters, $parameters); }
    public function get($key, $default = null) {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default; }
    public function set($key, $value) {
        $this->parameters[$key] = $value; }
    public function has($key) {
        return array_key_exists($key, $this->parameters); }
    public function delete($key) {
        unset($this->parameters[$key]); }
    public function getAlpha($key, $default = '') {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default)); }
    public function getAlnum($key, $default = '') {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default)); }
    public function getDigits($key, $default = '') {
        return preg_replace('/[^[:digit:]]/', '', $this->get($key, $default)); }
    public function getInt($key, $default = 0) {
        return (int) $this->get($key, $default); } }
namespace Symfony\Component\HttpFoundation;
class HeaderBag {
    protected $headers;
    protected $cacheControl;
    protected $type;
    public function __construct(array $headers = array(), $type = null) {
        $this->replace($headers);
        if (null !== $type && !in_array($type, array('request', 'response'))) {
            throw new \InvalidArgumentException(sprintf('The "%s" type is not supported by the HeaderBag constructor.', $type)); }
        $this->type = $type; }
    public function all() {
        return $this->headers; }
    public function replace(array $headers = array()) {
        $this->cacheControl = null;
        $this->headers = array();
        foreach ($headers as $key => $values) {
            $this->set($key, $values); } }
    public function get($key, $first = true) {
        $key = strtr(strtolower($key), '_', '-');
        if (!array_key_exists($key, $this->headers)) {
            return $first ? null : array(); }
        if ($first) {
            return count($this->headers[$key]) ? $this->headers[$key][0] : ''; } else {
            return $this->headers[$key]; } }
    public function set($key, $values, $replace = true) {
        $key = strtr(strtolower($key), '_', '-');
        if (!is_array($values)) {
            $values = array($values); }
        if (true === $replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $values; } else {
            $this->headers[$key] = array_merge($this->headers[$key], $values); } }
    public function has($key) {
        return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers); }
    public function contains($key, $value) {
        return in_array($value, $this->get($key, false)); }
    public function delete($key) {
        unset($this->headers[strtr(strtolower($key), '_', '-')]); }
    public function getCacheControl() {
        if (null === $this->cacheControl) {
            $this->cacheControl = new CacheControl($this, $this->get('Cache-Control'), $this->type); }
        return $this->cacheControl; }
    public function setCookie($name, $value, $domain = null, $expires = null, $path = '/', $secure = false, $httponly = true) {
                if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name)); }
        if (preg_match("/[,; \t\r\n\013\014]/", $value)) {
            throw new \InvalidArgumentException(sprintf('The cookie value "%s" contains invalid characters.', $name)); }
        if (!$name) {
            throw new \InvalidArgumentException('The cookie name cannot be empty'); }
        $cookie = sprintf('%s=%s', $name, urlencode($value));
        if ('request' === $this->type) {
            return $this->set('Cookie', $cookie); }
        if (null !== $expires) {
            if (is_numeric($expires)) {
                $expires = (int) $expires; } elseif ($expires instanceof \DateTime) {
                $expires = $expires->getTimestamp(); } else {
                $expires = strtotime($expires);
                if (false === $expires || -1 == $expires) {
                    throw new \InvalidArgumentException(sprintf('The "expires" cookie parameter is not valid.', $expires)); } }
            $cookie .= '; expires='.substr(\DateTime::createFromFormat('U', $expires, new \DateTimeZone('UTC'))->format('D, d-M-Y H:i:s T'), 0, -5); }
        if ($domain) {
            $cookie .= '; domain='.$domain; }
        if ('/' !== $path) {
            $cookie .= '; path='.$path; }
        if ($secure) {
            $cookie .= '; secure'; }
        if ($httponly) {
            $cookie .= '; httponly'; }
        $this->set('Set-Cookie', $cookie, false); }
    public function getDate($key, \DateTime $default = null) {
        if (null === $value = $this->get($key)) {
            return $default; }
        if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value)); }
        return $date; }
    static public function normalizeHeaderName($key) {
        return strtr(strtolower($key), '_', '-'); } }
namespace Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\SessionStorage\NativeSessionStorage;
class Request {
    public $attributes;
    public $request;
    public $query;
    public $server;
    public $files;
    public $cookies;
    public $headers;
    protected $languages;
    protected $charsets;
    protected $acceptableContentTypes;
    protected $pathInfo;
    protected $requestUri;
    protected $baseUrl;
    protected $basePath;
    protected $method;
    protected $format;
    protected $session;
    static protected $formats;
    public function __construct(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null) {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server); }
    public function initialize(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null) {
        $this->request = new ParameterBag(null !== $request ? $request : $_POST);
        $this->query = new ParameterBag(null !== $query ? $query : $_GET);
        $this->attributes = new ParameterBag(null !== $attributes ? $attributes : array());
        $this->cookies = new ParameterBag(null !== $cookies ? $cookies : $_COOKIE);
        $this->files = new ParameterBag($this->convertFileInformation(null !== $files ? $files : $_FILES));
        $this->server = new ParameterBag(null !== $server ? $server : $_SERVER);
        $this->headers = new HeaderBag($this->initializeHeaders(), 'request');
        $this->languages = null;
        $this->charsets = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null; }
    static public function create($uri, $method = 'get', $parameters = array(), $cookies = array(), $files = array(), $server = array()) {
        $defaults = array(
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Symfony/X.X',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
        );
        if (in_array(strtolower($method), array('post', 'put', 'delete'))) {
            $request = $parameters;
            $query = array();
            $defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded'; } else {
            $request = array();
            $query = $parameters;
            if (false !== $pos = strpos($uri, '?')) {
                $qs = substr($uri, $pos + 1);
                parse_str($qs, $params);
                $query = array_merge($params, $query); } }
        $queryString = false !== ($pos = strpos($uri, '?')) ? html_entity_decode(substr($uri, $pos + 1)) : '';
        parse_str($queryString, $qs);
        if (is_array($qs)) {
            $query = array_replace($qs, $query); }
        $server = array_replace($defaults, $server, array(
            'REQUEST_METHOD'       => strtoupper($method),
            'PATH_INFO'            => '',
            'REQUEST_URI'          => $uri,
            'QUERY_STRING'         => $queryString,
        ));
        return new self($query, $request, array(), $cookies, $files, $server); }
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null) {
        $dup = clone $this;
        $dup->initialize(
            null !== $query ? $query : $this->query->all(),
            null !== $request ? $request : $this->request->all(),
            null !== $attributes ? $attributes : $this->attributes->all(),
            null !== $cookies ? $cookies : $this->cookies->all(),
            null !== $files ? $files : $this->files->all(),
            null !== $server ? $server : $this->server->all()
        );
        return $dup; }
    public function __clone() {
        $this->query      = clone $this->query;
        $this->request    = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies    = clone $this->cookies;
        $this->files      = clone $this->files;
        $this->server     = clone $this->server;
        $this->headers    = clone $this->headers; }
    public function overrideGlobals() {
        $_GET = $this->query->all();
        $_POST = $this->request->all();
        $_SERVER = $this->server->all();
        $_COOKIES = $this->cookies->all();
        foreach ($this->headers->all() as $key => $value) {
            $_SERVER['HTTP_'.strtoupper(str_replace('-', '_', $key))] = implode(', ', $value); }
                        $_REQUEST = array_merge($_GET, $_POST); }
                        public function get($key, $default = null) {
        return $this->query->get($key, $this->attributes->get($key, $this->request->get($key, $default))); }
    public function getSession() {
        if (null === $this->session) {
            $this->session = new Session(new NativeSessionStorage()); }
        $this->session->start();
        return $this->session; }
    public function hasSession() {
        return '' !== session_id(); }
    public function setSession(Session $session) {
        $this->session = $session; }
    public function getScriptName() {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', '')); }
    public function getPathInfo() {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo(); }
        return $this->pathInfo; }
    public function getBasePath() {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath(); }
        return $this->basePath; }
    public function getBaseUrl() {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl(); }
        return $this->baseUrl; }
    public function getScheme() {
        return ($this->server->get('HTTPS') == 'on') ? 'https' : 'http'; }
    public function getPort() {
        return $this->server->get('SERVER_PORT'); }
    public function getHttpHost() {
        $host = $this->headers->get('HOST');
        if (!empty($host)) {
            return $host; }
        $scheme = $this->getScheme();
        $name   = $this->server->get('SERVER_NAME');
        $port   = $this->server->get('SERVER_PORT');
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return $name; } else {
            return $name.':'.$port; } }
    public function getRequestUri() {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri(); }
        return $this->requestUri; }
    public function getUri() {
        $qs = $this->getQueryString();
        if (null !== $qs) {
            $qs = '?'.$qs; }
        return $this->getScheme().'://'.$this->getHost().':'.$this->getPort().$this->getScriptName().$this->getPathInfo().$qs; }
    public function getQueryString() {
        if (!$qs = $this->server->get('QUERY_STRING')) {
            return null; }
        $parts = array();
        foreach (explode('&', $qs) as $segment) {
            $tmp = explode('=', urldecode($segment), 2);
            $parts[urlencode($tmp[0])] = urlencode($tmp[1]); }
        ksort($parts);
        return http_build_query($parts); }
    public function isSecure() {
        return (
            (strtolower($this->server->get('HTTPS')) == 'on' || $this->server->get('HTTPS') == 1)
            ||
            (strtolower($this->headers->get('SSL_HTTPS')) == 'on' || $this->headers->get('SSL_HTTPS') == 1)
            ||
            (strtolower($this->headers->get('X_FORWARDED_PROTO')) == 'https')
        ); }
    public function getHost() {
        if ($host = $this->headers->get('X_FORWARDED_HOST')) {
            $elements = explode(',', $host);
            return trim($elements[count($elements) - 1]); } else {
            return $this->headers->get('HOST', $this->server->get('SERVER_NAME', $this->server->get('SERVER_ADDR', ''))); } }
    public function setMethod($method) {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', 'GET'); }
    public function getMethod() {
        if (null === $this->method) {
            switch ($this->server->get('REQUEST_METHOD', 'GET')) {
                case 'POST':
                    $this->method = strtoupper($this->request->get('_method', 'POST'));
                    break;
                case 'PUT':
                    $this->method = 'PUT';
                    break;
                case 'DELETE':
                    $this->method = 'DELETE';
                    break;
                case 'HEAD':
                    $this->method = 'HEAD';
                    break;
                default:
                    $this->method = 'GET'; } }
        return $this->method; }
    public function getMimeType($format) {
        if (null === static::$formats) {
            static::initializeFormats(); }
        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null; }
    public function getFormat($mimeType) {
        if (null === static::$formats) {
            static::initializeFormats(); }
        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format; } }
        return null; }
    public function setFormat($format, $mimeTypes) {
        if (null === static::$formats) {
            static::initializeFormats(); }
        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : array($mimeTypes); }
    public function getRequestFormat() {
        if (null === $this->format) {
            $this->format = $this->get('_format', 'html'); }
        return $this->format; }
    public function setRequestFormat($format) {
        $this->format = $format; }
    public function isMethodSafe() {
        return in_array(strtolower($this->getMethod()), array('get', 'head')); }
    public function getETags() {
        return preg_split('/\s*,\s*/', $this->headers->get('if_none_match'), null, PREG_SPLIT_NO_EMPTY); }
    public function isNoCache() {
        return $this->headers->getCacheControl()->isNoCache() || 'no-cache' == $this->headers->get('Pragma'); }
    public function getPreferredLanguage(array $locales = null) {
        $preferredLanguages = $this->getLanguages();
        if (null === $locales) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null; }
        if (!$preferredLanguages) {
            return $locales[0]; }
        $preferredLanguages = array_values(array_intersect($preferredLanguages, $locales));
        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0]; }
    public function getLanguages() {
        if (null !== $this->languages) {
            return $this->languages; }
        $languages = $this->splitHttpAcceptHeader($this->headers->get('Accept-Language'));
        foreach ($languages as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                                                                                if (count($codes) > 1) {
                        $lang = $codes[1]; } } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]); } else {
                            $lang .= '_'.strtoupper($codes[$i]); } } } }
            $this->languages[] = $lang; }
        return $this->languages; }
    public function getCharsets() {
        if (null !== $this->charsets) {
            return $this->charsets; }
        return $this->charsets = $this->splitHttpAcceptHeader($this->headers->get('Accept-Charset')); }
    public function getAcceptableContentTypes() {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes; }
        return $this->acceptableContentTypes = $this->splitHttpAcceptHeader($this->headers->get('Accept')); }
    public function isXmlHttpRequest() {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With'); }
    public function splitHttpAcceptHeader($header) {
        if (!$header) {
            return array(); }
        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
                        if ($pos = strpos($value, ';')) {
                $q     = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = trim(substr($value, 0, $pos)); } else {
                $q = 1; }
            if (0 < $q) {
                $values[trim($value)] = $q; } }
        arsort($values);
        return array_keys($values); }
    protected function prepareRequestUri() {
        $requestUri = '';
        if ($this->headers->has('X_REWRITE_URL')) {
                        $requestUri = $this->headers->get('X_REWRITE_URL'); } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
                        $requestUri = $this->server->get('UNENCODED_URL'); } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
                        $schemeAndHttpHost = $this->getScheme().'://'.$this->getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost)); } } elseif ($this->server->has('ORIG_PATH_INFO')) {
                        $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ($this->server->get('QUERY_STRING')) {
                $requestUri .= '?'.$this->server->get('QUERY_STRING'); } }
        return $requestUri; }
    protected function prepareBaseUrl() {
        $baseUrl = '';
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME'); } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF'); } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME');         } else {
                                    $path    = $this->server->get('PHP_SELF', '');
            $file    = $this->server->get('SCRIPT_FILENAME', '');
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index; } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos)); }
                $requestUri = $this->getRequestUri();
        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
                        return $baseUrl; }
        if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
                        return rtrim(dirname($baseUrl), '/'); }
        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos); }
        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
                        return ''; }
                                if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl)); }
        return rtrim($baseUrl, '/'); }
    protected function prepareBasePath() {
        $basePath = '';
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return ''; }
        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl); } else {
            $basePath = $baseUrl; }
        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath); }
        return rtrim($basePath, '/'); }
    protected function preparePathInfo() {
        $baseUrl = $this->getBaseUrl();
        if (null === ($requestUri = $this->getRequestUri())) {
            return ''; }
        $pathInfo = '';
                if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos); }
        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
                        return ''; } elseif (null === $baseUrl) {
            return $requestUri; }
        return (string) $pathInfo; }
    protected function convertFileInformation(array $taintedFiles) {
        $files = array();
        foreach ($taintedFiles as $key => $data) {
            $files[$key] = $this->fixPhpFilesArray($data); }
        return $files; }
    protected function initializeHeaders() {
        $headers = array();
        foreach ($this->server->all() as $key => $value) {
            if ('http_' === strtolower(substr($key, 0, 5))) {
                $headers[substr($key, 5)] = $value; } }
        return $headers; }
    static protected function initializeFormats() {
        static::$formats = array(
            'txt'  => 'text/plain',
            'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'css'  => 'text/css',
            'json' => array('application/json', 'application/x-json'),
            'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
            'rdf'  => 'application/rdf+xml',
            'atom' => 'application/atom+xml',
        ); }
    static protected function fixPhpFilesArray($data) {
        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        $keys = array_keys($data);
        sort($keys);
        if ($fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data; }
        $files = $data;
        foreach ($fileKeys as $k) {
            unset($files[$k]); }
        foreach (array_keys($data['name']) as $key) {
            $files[$key] = self::fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key],
                'type'     => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key],
            )); }
        return $files; } }
namespace Symfony\Component\HttpFoundation;
class Response {
    public $headers;
    protected $content;
    protected $version;
    protected $statusCode;
    protected $statusText;
    static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );
    public function __construct($content = '', $status = 200, $headers = array()) {
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
        $this->headers = new HeaderBag($headers, 'response'); }
    public function __toString() {
        $content = '';
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html'); }
                $content .= sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\n";
                foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $content .= "$name: $value\n"; } }
        $content .= "\n".$this->getContent();
        return $content; }
    public function __clone() {
        $this->headers = clone $this->headers; }
    public function sendHeaders() {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html'); }
                header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));
                foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value); } } }
    public function sendContent() {
        echo $this->content; }
    public function send() {
        $this->sendHeaders();
        $this->sendContent(); }
    public function setContent($content) {
        $this->content = $content; }
    public function getContent() {
        return $this->content; }
    public function setProtocolVersion($version) {
        $this->version = $version; }
    public function getProtocolVersion() {
        return $this->version; }
    public function setStatusCode($code, $text = null) {
        $this->statusCode = (int) $code;
        if ($this->statusCode < 100 || $this->statusCode > 599) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code)); }
        $this->statusText = false === $text ? '' : (null === $text ? self::$statusTexts[$this->statusCode] : $text); }
    public function getStatusCode() {
        return $this->statusCode; }
    public function isCacheable() {
        if (!in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false; }
        if ($this->headers->getCacheControl()->isNoStore() || $this->headers->getCacheControl()->isPrivate()) {
            return false; }
        return $this->isValidateable() || $this->isFresh(); }
    public function isFresh() {
        return $this->getTtl() > 0; }
    public function isValidateable() {
        return $this->headers->has('Last-Modified') || $this->headers->has('ETag'); }
    public function setPrivate($value) {
        $value = (Boolean) $value;
        $this->headers->getCacheControl()->setPublic(!$value);
        $this->headers->getCacheControl()->setPrivate($value); }
    public function mustRevalidate() {
        return $this->headers->getCacheControl()->mustRevalidate() || $this->headers->getCacheControl()->mustProxyRevalidate(); }
    public function getDate() {
        if (null === $date = $this->headers->getDate('Date')) {
            $date = new \DateTime();
            $this->headers->set('Date', $date->format(DATE_RFC2822)); }
        return $date; }
    public function getAge() {
        if ($age = $this->headers->get('Age')) {
            return $age; }
        return max(time() - $this->getDate()->format('U'), 0); }
    public function expire() {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge()); } }
    public function getExpires() {
        return $this->headers->getDate('Expires'); }
    public function setExpires(\DateTime $date = null) {
        if (null === $date) {
            $this->headers->delete('Expires'); } else {
            $this->headers->set('Expires', $date->format(DATE_RFC2822)); } }
    public function getMaxAge() {
        if ($age = $this->headers->getCacheControl()->getSharedMaxAge()) {
            return $age; }
        if ($age = $this->headers->getCacheControl()->getMaxAge()) {
            return $age; }
        if (null !== $this->getExpires()) {
            return $this->getExpires()->format('U') - $this->getDate()->format('U'); }
        return null; }
    public function setMaxAge($value) {
        $this->headers->getCacheControl()->setMaxAge($value); }
    public function setSharedMaxAge($value) {
        $this->headers->getCacheControl()->setSharedMaxAge($value); }
    public function getTtl() {
        if ($maxAge = $this->getMaxAge()) {
            return $maxAge - $this->getAge(); }
        return null; }
    public function setTtl($seconds) {
        $this->setSharedMaxAge($this->getAge() + $seconds); }
    public function setClientTtl($seconds) {
        $this->setMaxAge($this->getAge() + $seconds); }
    public function getLastModified() {
        return $this->headers->getDate('LastModified'); }
    public function setLastModified(\DateTime $date = null) {
        if (null === $date) {
            $this->headers->delete('Last-Modified'); } else {
            $this->headers->set('Last-Modified', $date->format(DATE_RFC2822)); } }
    public function getEtag() {
        return $this->headers->get('ETag'); }
    public function setEtag($etag = null, $weak = false) {
        if (null === $etag) {
            $this->headers->delete('Etag'); } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"'.$etag.'"'; }
            $this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag); } }
    public function setNotModified() {
        $this->setStatusCode(304);
        $this->setContent(null);
                foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->delete($header); } }
    public function setRedirect($url, $status = 302) {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.'); }
        $this->setStatusCode($status);
        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status)); }
        $this->headers->set('Location', $url);
        $this->setContent(sprintf('<html><head><meta http-equiv="refresh" content="1;url=%s"/></head></html>', htmlspecialchars($url, ENT_QUOTES))); }
    public function hasVary() {
        return (Boolean) $this->headers->get('Vary'); }
    public function getVary() {
        if (!$vary = $this->headers->get('Vary')) {
            return array(); }
        return preg_split('/[\s,]+/', $vary); }
    public function isNotModified(Request $request) {
        $lastModified = $request->headers->get('If-Modified-Since');
        $notModified = false;
        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->get('Last-Modified') == $lastModified); } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->get('Last-Modified'); }
        if ($notModified) {
            $this->setNotModified(); }
        return $notModified; }
        public function isInvalid() {
        return $this->statusCode < 100 || $this->statusCode >= 600; }
    public function isInformational() {
        return $this->statusCode >= 100 && $this->statusCode < 200; }
    public function isSuccessful() {
        return $this->statusCode >= 200 && $this->statusCode < 300; }
    public function isRedirection() {
        return $this->statusCode >= 300 && $this->statusCode < 400; }
    public function isClientError() {
        return $this->statusCode >= 400 && $this->statusCode < 500; }
    public function isServerError() {
        return $this->statusCode >= 500 && $this->statusCode < 600; }
    public function isOk() {
        return 200 === $this->statusCode; }
    public function isForbidden() {
        return 403 === $this->statusCode; }
    public function isNotFound() {
        return 404 === $this->statusCode; }
    public function isRedirect() {
        return in_array($this->statusCode, array(301, 302, 303, 307)); }
    public function isEmpty() {
        return in_array($this->statusCode, array(201, 204, 304)); }
    public function isRedirected($location) {
        return $this->isRedirect() && $location == $this->headers->get('Location'); } }
namespace Symfony\Component\HttpKernel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class HttpKernel implements HttpKernelInterface {
    protected $dispatcher;
    protected $resolver;
    protected $request;
    public function __construct(EventDispatcher $dispatcher, ControllerResolverInterface $resolver) {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver; }
    public function getRequest() {
        return $this->request; }
    public function handle(Request $request = null, $type = HttpKernelInterface::MASTER_REQUEST, $raw = false) {
        if (null === $request) {
            $request = new Request(); }
        if (HttpKernelInterface::MASTER_REQUEST === $type) {
            $this->request = $request; }
        try {
            return $this->handleRaw($request, $type); } catch (\Exception $e) {
            if (true === $raw) {
                throw $e; }
                        $event = $this->dispatcher->notifyUntil(new Event($this, 'core.exception', array('request_type' => $type, 'request' => $request, 'exception' => $e)));
            if ($event->isProcessed()) {
                return $this->filterResponse($event->getReturnValue(), $request, 'A "core.exception" listener returned a non response object.', $type); }
            throw $e; } }
    protected function handleRaw(Request $request, $type = self::MASTER_REQUEST) {
                $event = $this->dispatcher->notifyUntil(new Event($this, 'core.request', array('request_type' => $type, 'request' => $request)));
        if ($event->isProcessed()) {
            return $this->filterResponse($event->getReturnValue(), $request, 'A "core.request" listener returned a non response object.', $type); }
                if (false === $controller = $this->resolver->getController($request)) {
            throw new NotFoundHttpException('Unable to find the controller.'); }
        $event = $this->dispatcher->filter(new Event($this, 'core.controller', array('request_type' => $type, 'request' => $request)), $controller);
        $controller = $event->getReturnValue();
                if (!is_callable($controller)) {
            throw new \LogicException(sprintf('The controller must be a callable (%s).', var_export($controller, true))); }
                $arguments = $this->resolver->getArguments($request, $controller);
                $retval = call_user_func_array($controller, $arguments);
                $event = $this->dispatcher->filter(new Event($this, 'core.view', array('request_type' => $type, 'request' => $request)), $retval);
        return $this->filterResponse($event->getReturnValue(), $request, sprintf('The controller must return a response (instead of %s).', is_object($event->getReturnValue()) ? 'an object of class '.get_class($event->getReturnValue()) : is_array($event->getReturnValue()) ? 'an array' : str_replace("\n", '', var_export($event->getReturnValue(), true))), $type); }
    protected function filterResponse($response, $request, $message, $type) {
        if (!$response instanceof Response) {
            throw new \RuntimeException($message); }
        $event = $this->dispatcher->filter(new Event($this, 'core.response', array('request_type' => $type, 'request' => $request)), $response);
        $response = $event->getReturnValue();
        if (!$response instanceof Response) {
            throw new \RuntimeException('A "core.response" listener returned a non response object.'); }
        return $response; } }
namespace Symfony\Component\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
class ResponseListener {
    public function register(EventDispatcher $dispatcher) {
        $dispatcher->connect('core.response', array($this, 'filter')); }
    public function filter(Event $event, Response $response) {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getParameter('request_type') || $response->headers->has('Content-Type')) {
            return $response; }
        $request = $event->getParameter('request');
        $format = $request->getRequestFormat();
        if ((null !== $format) && $mimeType = $request->getMimeType($format)) {
            $response->headers->set('Content-Type', $mimeType); }
        return $response; } }
namespace Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
class ControllerResolver implements ControllerResolverInterface {
    protected $logger;
    public function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger; }
    public function getController(Request $request) {
        if (!$controller = $request->attributes->get('_controller')) {
            if (null !== $this->logger) {
                $this->logger->err('Unable to look for the controller as the "_controller" parameter is missing'); }
            return false; }
        list($controller, $method) = $this->createController($controller);
        if (!method_exists($controller, $method)) {
            throw new \InvalidArgumentException(sprintf('Method "%s::%s" does not exist.', get_class($controller), $method)); }
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Using controller "%s::%s"', get_class($controller), $method)); }
        return array($controller, $method); }
    public function getArguments(Request $request, $controller) {
        $attributes = $request->attributes->all();
        list($controller, $method) = $controller;
        $r = new \ReflectionObject($controller);
        $arguments = array();
        foreach ($r->getMethod($method)->getParameters() as $param) {
            if (array_key_exists($param->getName(), $attributes)) {
                $arguments[] = $attributes[$param->getName()]; } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue(); } else {
                throw new \RuntimeException(sprintf('Controller "%s::%s()" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', get_class($controller), $method, $param->getName())); } }
        return $arguments; }
    protected function createController($controller) {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller)); }
        list($class, $method) = explode('::', $controller);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class)); }
        return array(new $class(), $method); } }
namespace Symfony\Bundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class RequestListener {
    protected $router;
    protected $logger;
    public function __construct(RouterInterface $router, LoggerInterface $logger = null) {
        $this->router = $router;
        $this->logger = $logger; }
    public function register(EventDispatcher $dispatcher) {
        $dispatcher->connect('core.request', array($this, 'resolve')); }
    public function resolve(Event $event) {
        $request = $event->getParameter('request');
        if (HttpKernelInterface::MASTER_REQUEST === $event->getParameter('request_type')) {
                                    $this->router->setContext(array(
                'base_url'  => $request->getBaseUrl(),
                'method'    => $request->getMethod(),
                'host'      => $request->getHost(),
                'is_secure' => $request->isSecure(),
            )); }
        if ($request->attributes->has('_controller')) {
            return; }
        if (false !== $parameters = $this->router->match($request->getPathInfo())) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], str_replace("\n", '', var_export($parameters, true)))); }
            $request->attributes->replace($parameters); } elseif (null !== $this->logger) {
            $this->logger->err(sprintf('No route found for %s', $request->getPathInfo())); } } }
namespace Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Framework\Kernel;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
class ControllerNameConverter {
    protected $kernel;
    protected $logger;
    protected $namespaces;
    public function __construct(Kernel $kernel, LoggerInterface $logger = null) {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->namespaces = array_keys($kernel->getBundleDirs()); }
    public function toShortNotation($controller) {
        if (2 != count($parts = explode('::', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid class::method controller string.', $controller)); }
        list($class, $method) = $parts;
        if ('Action' != substr($method, -6)) {
            throw new \InvalidArgumentException(sprintf('The "%s::%s" method does not look like a controller action (it does not end with Action)', $class, $method)); }
        $action = substr($method, 0, -6);
        if (!preg_match('/Controller\\\(.*)Controller$/', $class, $match)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it does not end with Controller)', $class)); }
        $controller = $match[1];
        $bundle = null;
        $namespace = substr($class, 0, strrpos($class, '\\'));
        foreach ($this->namespaces as $prefix) {
            if (0 === $pos = strpos($namespace, $prefix)) {
                                $bundle = substr($namespace, strlen($prefix) + 1, -11); } }
        if (null === $bundle) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not belong to a known bundle namespace.', $class)); }
        return $bundle.':'.$controller.':'.$action; }
    public function fromShortNotation($controller) {
        if (3 != count($parts = explode(':', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller)); }
        list($bundle, $controller, $action) = $parts;
        $bundle = strtr($bundle, array('/' => '\\'));
        $class = null;
        $logs = array();
        foreach ($this->namespaces as $namespace) {
            $try = $namespace.'\\'.$bundle.'\\Controller\\'.$controller.'Controller';
            if (!class_exists($try)) {
                if (null !== $this->logger) {
                    $logs[] = sprintf('Failed finding controller "%s:%s" from namespace "%s" (%s)', $bundle, $controller, $namespace, $try); } } else {
                if (!$this->kernel->isClassInActiveBundle($try)) {
                    throw new \LogicException(sprintf('To use the "%s" controller, you first need to enable the Bundle "%s" in your Kernel class.', $try, $namespace.'\\'.$bundle)); }
                $class = $try;
                break; } }
        if (null === $class) {
            if (null !== $this->logger) {
                foreach ($logs as $log) {
                    $this->logger->info($log); } }
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s:%s".', $bundle, $controller)); }
        return $class.'::'.$action.'Action'; } }
namespace Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameConverter;
class ControllerResolver extends BaseControllerResolver {
    protected $container;
    protected $converter;
    protected $esiSupport;
    public function __construct(ContainerInterface $container, ControllerNameConverter $converter, LoggerInterface $logger = null) {
        $this->container = $container;
        $this->converter = $converter;
        $this->esiSupport = $container->has('esi') && $container->getEsiService()->hasSurrogateEsiCapability($container->getRequestService());
        parent::__construct($logger); }
    protected function createController($controller) {
        if (false === strpos($controller, '::')) {
                        $controller = $this->converter->fromShortNotation($controller); }
        list($class, $method) = explode('::', $controller);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class)); }
        return array(new $class($this->container), $method); }
    public function forward($controller, array $path = array(), array $query = array()) {
        $path['_controller'] = $controller;
        $subRequest = $this->container->getRequestService()->duplicate($query, null, $path);
        return $this->container->get('kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST); }
    public function render($controller, array $options = array()) {
        $options = array_merge(array(
            'path'          => array(),
            'query'         => array(),
            'ignore_errors' => true,
            'alt'           => array(),
            'standalone'    => false,
            'comment'       => '',
        ), $options);
        if (!is_array($options['alt'])) {
            $options['alt'] = array($options['alt']); }
        if ($this->esiSupport && $options['standalone']) {
            $uri = $this->generateInternalUri($controller, $options['path'], $options['query']);
            $alt = '';
            if ($options['alt']) {
                $alt = $this->generateInternalUri($options['alt'][0], isset($options['alt'][1]) ? $options['alt'][1] : array(), isset($options['alt'][2]) ? $options['alt'][2] : array()); }
            return $this->container->getEsiService()->renderTag($uri, $alt, $options['ignore_errors'], $options['comment']); }
        $request = $this->container->getRequestService();
                if (0 === strpos($controller, '/')) {
            $subRequest = Request::create($controller, 'get', array(), $request->cookies->all(), array(), $request->server->all()); } else {
            $options['path']['_controller'] = $controller;
            $options['path']['_format'] = $request->getRequestFormat();
            $subRequest = $request->duplicate($options['query'], null, $options['path']); }
        try {
            $response = $this->container->getKernelService()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true);
            if (200 != $response->getStatusCode()) {
                throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $request->getUri(), $response->getStatusCode())); }
            return $response->getContent(); } catch (\Exception $e) {
            if ($options['alt']) {
                $alt = $options['alt'];
                unset($options['alt']);
                $options['path'] = isset($alt[1]) ? $alt[1] : array();
                $options['query'] = isset($alt[2]) ? $alt[2] : array();
                return $this->render($alt[0], $options); }
            if (!$options['ignore_errors']) {
                throw $e; } } }
    public function generateInternalUri($controller, array $path = array(), array $query = array()) {
        if (0 === strpos($controller, '/')) {
            return $controller; }
        $uri = $this->container->getRouterService()->generate('_internal', array(
            'controller' => $controller,
            'path'       => $path ? http_build_query($path) : 'none',
            '_format'    => $this->container->getRequestService()->getRequestFormat(),
        ), true);
        if ($query) {
            $uri = $uri.'?'.http_build_query($query); }
        return $uri; } }
namespace Symfony\Component\EventDispatcher;
class Event implements \ArrayAccess {
    protected $value = null;
    protected $processed = false;
    protected $subject;
    protected $name;
    protected $parameters;
    public function __construct($subject, $name, $parameters = array()) {
        $this->subject = $subject;
        $this->name = $name;
        $this->parameters = $parameters; }
    public function getSubject() {
        return $this->subject; }
    public function getName() {
        return $this->name; }
    public function setReturnValue($value) {
        $this->value = $value; }
    public function getReturnValue() {
        return $this->value; }
    public function setProcessed($processed) {
        $this->processed = (boolean) $processed; }
    public function isProcessed() {
        return $this->processed; }
    public function getParameters() {
        return $this->parameters; }
    public function hasParameter($name) {
        return array_key_exists($name, $this->parameters); }
    public function getParameter($name) {
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->name, $name)); }
        return $this->parameters[$name]; }
    public function setParameter($name, $value) {
        $this->parameters[$name] = $value; }
    public function offsetExists($name) {
        return array_key_exists($name, $this->parameters); }
    public function offsetGet($name) {
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->name, $name)); }
        return $this->parameters[$name]; }
    public function offsetSet($name, $value) {
        $this->parameters[$name] = $value; }
    public function offsetUnset($name) {
        unset($this->parameters[$name]); } }
namespace Symfony\Bundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
class Controller implements \ArrayAccess {
    protected $container;
    function __construct(ContainerInterface $container) {
        $this->container = $container; }
    public function createResponse($content = '', $status = 200, array $headers = array()) {
        $response = $this->container->get('response');
        $response->setContent($content);
        $response->setStatusCode($status);
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value); }
        return $response; }
    public function generateUrl($route, array $parameters = array(), $absolute = false) {
        return $this->container->get('router')->generate($route, $parameters, $absolute); }
    public function forward($controller, array $path = array(), array $query = array()) {
        return $this->container->get('controller_resolver')->forward($controller, $path, $query); }
    public function redirect($url, $status = 302) {
        $response = $this->container->get('response');
        $response->setRedirect($url, $status);
        return $response; }
    public function renderView($view, array $parameters = array()) {
        return $this->container->get('templating')->render($view, $parameters); }
    public function render($view, array $parameters = array(), Response $response = null) {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response); }
    public function offsetExists($id) {
        return $this->container->has($id); }
    public function offsetGet($id) {
        return $this->container->get($id); }
    public function offsetSet($id, $value) {
        throw new \LogicException(sprintf('You can\'t set a service from a controller (%s).', $id)); }
    public function offsetUnset($id) {
        throw new \LogicException(sprintf('You can\'t unset a service from a controller (%s).', $id)); } }
namespace Symfony\Component\Templating\Loader;
interface LoaderInterface {
    function load($template, array $options = array());
    function isFresh($template, array $options = array(), $time); }
namespace Symfony\Component\Templating\Loader;
use Symfony\Component\Templating\DebuggerInterface;
abstract class Loader implements LoaderInterface {
    protected $debugger;
    protected $defaultOptions;
    public function __construct() {
        $this->defaultOptions = array('renderer' => 'php'); }
    public function setDebugger(DebuggerInterface $debugger) {
        $this->debugger = $debugger; }
    public function setDefaultOption($name, $value) {
        $this->defaultOptions[$name] = $value; }
    protected function mergeDefaultOptions(array $options) {
        return array_merge($this->defaultOptions, $options); } }
namespace Symfony\Component\Templating\Loader;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;
class FilesystemLoader extends Loader {
    protected $templatePathPatterns;
    public function __construct($templatePathPatterns) {
        if (!is_array($templatePathPatterns)) {
            $templatePathPatterns = array($templatePathPatterns); }
        $this->templatePathPatterns = $templatePathPatterns;
        parent::__construct(); }
    public function load($template, array $options = array()) {
        if (self::isAbsolutePath($template) && file_exists($template)) {
            return new FileStorage($template); }
        $options = $this->mergeDefaultOptions($options);
        $options['name'] = $template;
        $replacements = array();
        foreach ($options as $key => $value) {
            $replacements['%'.$key.'%'] = $value; }
        $logs = array();
        foreach ($this->templatePathPatterns as $templatePathPattern) {
            if (is_file($file = strtr($templatePathPattern, $replacements))) {
                if (null !== $this->debugger) {
                    $this->debugger->log(sprintf('Loaded template file "%s" (renderer: %s)', $file, $options['renderer'])); }
                return new FileStorage($file); }
            if (null !== $this->debugger) {
                $logs[] = sprintf('Failed loading template file "%s" (renderer: %s)', $file, $options['renderer']); } }
        if (null !== $this->debugger) {
            foreach ($logs as $log) {
                $this->debugger->log($log); } }
        return false; }
    public function isFresh($template, array $options = array(), $time) {
        if (false === $template = $this->load($template, $options)) {
            return false; }
        return filemtime((string) $template) < $time; }
    static protected function isAbsolutePath($file) {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
        ) {
            return true; }
        return false; } }
namespace Symfony\Component\Templating;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\Renderer\PhpRenderer;
use Symfony\Component\Templating\Renderer\RendererInterface;
use Symfony\Component\Templating\Helper\HelperInterface;
class Engine implements \ArrayAccess {
    protected $loader;
    protected $renderers;
    protected $current;
    protected $helpers;
    protected $parents;
    protected $stack;
    protected $charset;
    protected $cache;
    public function __construct(LoaderInterface $loader, array $renderers = array(), array $helpers = array()) {
        $this->loader    = $loader;
        $this->renderers = $renderers;
        $this->helpers   = array();
        $this->parents   = array();
        $this->stack     = array();
        $this->charset   = 'UTF-8';
        $this->cache     = array();
        $this->addHelpers($helpers);
        if (!isset($this->renderers['php'])) {
            $this->renderers['php'] = new PhpRenderer(); }
        foreach ($this->renderers as $renderer) {
            $renderer->setEngine($this); } }
    public function render($name, array $parameters = array()) {
        if (isset($this->cache[$name])) {
            list($tpl, $options, $template) = $this->cache[$name]; } else {
            list($tpl, $options) = $this->splitTemplateName($name);
                        $template = $this->loader->load($tpl, $options);
            if (false === $template) {
                throw new \InvalidArgumentException(sprintf('The template "%s" does not exist (renderer: %s).', $name, $options['renderer'])); }
            $this->cache[$name] = array($tpl, $options, $template); }
        $this->current = $name;
        $this->parents[$name] = null;
                $renderer = $template->getRenderer() ? $template->getRenderer() : $options['renderer'];
        if (!isset($this->renderers[$options['renderer']])) {
            throw new \InvalidArgumentException(sprintf('The renderer "%s" is not registered.', $renderer)); }
                if (false === $content = $this->renderers[$renderer]->evaluate($template, $parameters)) {
            throw new \RuntimeException(sprintf('The template "%s" cannot be rendered (renderer: %s).', $name, $renderer)); }
                if ($this->parents[$name]) {
            $slots = $this->get('slots');
            $this->stack[] = $slots->get('_content');
            $slots->set('_content', $content);
            $content = $this->render($this->parents[$name], $parameters);
            $slots->set('_content', array_pop($this->stack)); }
        return $content; }
    public function output($name, array $parameters = array()) {
        echo $this->render($name, $parameters); }
    public function offsetGet($name) {
        return $this->$name = $this->get($name); }
    public function offsetExists($name) {
        return isset($this->helpers[$name]); }
    public function offsetSet($name, $value) {
        $this->set($name, $value); }
    public function offsetUnset($name) {
        throw new \LogicException(sprintf('You can\'t unset a helper (%s).', $name)); }
    public function addHelpers(array $helpers = array()) {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, is_int($alias) ? null : $alias); } }
    public function set(HelperInterface $helper, $alias = null) {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper; }
        $helper->setCharset($this->charset); }
    public function has($name) {
        return isset($this->helpers[$name]); }
    public function get($name) {
        if (!isset($this->helpers[$name])) {
            throw new \InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name)); }
        return $this->helpers[$name]; }
    public function extend($template) {
        $this->parents[$this->current] = $template; }
    public function escape($value) {
        return is_string($value) || (is_object($value) && method_exists($value, '__toString')) ? htmlspecialchars($value, ENT_QUOTES, $this->charset) : $value; }
    public function setCharset($charset) {
        $this->charset = $charset; }
    public function getCharset() {
        return $this->charset; }
    public function getLoader() {
        return $this->loader; }
    public function setRenderer($name, RendererInterface $renderer) {
        $this->renderers[$name] = $renderer;
        $renderer->setEngine($this); }
    public function splitTemplateName($name) {
        if (false !== $pos = strpos($name, ':')) {
            $renderer = substr($name, $pos + 1);
            $name = substr($name, 0, $pos); } else {
            $renderer = 'php'; }
        return array($name, array('renderer' => $renderer)); } }
namespace Symfony\Component\Templating\Renderer;
use Symfony\Component\Templating\Engine;
use Symfony\Component\Templating\Storage\Storage;
interface RendererInterface {
    function evaluate(Storage $template, array $parameters = array());
    function setEngine(Engine $engine); }
namespace Symfony\Component\Templating\Renderer;
use Symfony\Component\Templating\Engine;
abstract class Renderer implements RendererInterface {
    protected $engine;
    public function setEngine(Engine $engine) {
        $this->engine = $engine; } }
namespace Symfony\Component\Templating\Renderer;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\Storage\StringStorage;
class PhpRenderer extends Renderer {
    public function evaluate(Storage $template, array $parameters = array()) {
        if ($template instanceof FileStorage) {
            extract($parameters);
            $view = $this->engine;
            ob_start();
            require $template;
            return ob_get_clean(); } else if ($template instanceof StringStorage) {
            extract($parameters);
            $view = $this->engine;
            ob_start();
            eval('; ?>'.$template.'<?php ;');
            return ob_get_clean(); }
        return false; } }
namespace Symfony\Component\Templating\Storage;
abstract class Storage {
    protected $renderer;
    protected $template;
    public function __construct($template, $renderer = null) {
        $this->template = $template;
        $this->renderer = $renderer; }
    public function __toString() {
        return (string) $this->template; }
    abstract public function getContent();
    public function getRenderer() {
        return $this->renderer; } }
namespace Symfony\Component\Templating\Storage;
class FileStorage extends Storage {
    public function getContent() {
        return file_get_contents($this->template); } }
namespace Symfony\Bundle\FrameworkBundle\Templating;
use Symfony\Component\Templating\Engine as BaseEngine;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\OutputEscaper\Escaper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
class Engine extends BaseEngine {
    protected $container;
    protected $escaper;
    protected $level;
    public function __construct(ContainerInterface $container, LoaderInterface $loader, array $renderers = array(), $escaper) {
        $this->level = 0;
        $this->container = $container;
        $this->escaper = $escaper;
        foreach ($this->container->findTaggedServiceIds('templating.renderer') as $id => $attributes) {
            if (isset($attributes[0]['alias'])) {
                $renderers[$attributes[0]['alias']] = $this->container->get($id); } }
        parent::__construct($loader, $renderers);
        $this->helpers = array();
        foreach ($this->container->findTaggedServiceIds('templating.helper') as $id => $attributes) {
            if (isset($attributes[0]['alias'])) {
                $this->helpers[$attributes[0]['alias']] = $id; } } }
    public function render($name, array $parameters = array()) {
        ++$this->level;
        list(, $options) = $this->splitTemplateName($name);
        if ('php' === $options['renderer']) {
                        if (1 === $this->level && !isset($parameters['_data'])) {
                $parameters = $this->escapeParameters($parameters); } }
        $content = parent::render($name, $parameters);
        --$this->level;
        return $content; }
    public function renderResponse($view, array $parameters = array(), Response $response = null) {
        if (null === $response) {
            $response = $this->container->get('response'); }
        $response->setContent($this->render($view, $parameters));
        return $response; }
    public function has($name) {
        return isset($this->helpers[$name]); }
    public function get($name) {
        if (!isset($this->helpers[$name])) {
            throw new \InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name)); }
        if (is_string($this->helpers[$name])) {
            $this->helpers[$name] = $this->container->get($this->helpers[$name]);
            $this->helpers[$name]->setCharset($this->charset); }
        return $this->helpers[$name]; }
    protected function escapeParameters(array $parameters) {
        if (false !== $this->escaper) {
            Escaper::setCharset($this->getCharset());
            $parameters['_data'] = Escaper::escape($this->escaper, $parameters);
            foreach ($parameters['_data'] as $key => $value) {
                $parameters[$key] = $value; } } else {
            $parameters['_data'] = Escaper::escape('raw', $parameters); }
        return $parameters; }
        public function splitTemplateName($name, array $defaults = array()) {
        $parts = explode(':', $name, 4);
        if (sizeof($parts) < 3) {
            throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid.', $name)); }
        $options = array_replace(
            array(
                'renderer' => 'php',
                'format'   => '',
            ),
            $defaults,
            array(
                'bundle'     => str_replace('\\', '/', $parts[0]),
                'controller' => $parts[1],
            )
        );
        if (false !== $pos = strpos($parts[2], '.')) {
            $options['format'] = substr($parts[2], $pos);
            $parts[2] = substr($parts[2], 0, $pos); } else {
            $format = $this->container->getRequestService()->getRequestFormat();
            if (null !== $format && 'html' !== $format) {
                $options['format'] = '.'.$format; } }
        if (isset($parts[3]) && $parts[3]) {
            $options['renderer'] = $parts[3]; }
        return array($parts[2], $options); } }
namespace Symfony\Component\Templating\Helper;
abstract class Helper implements HelperInterface {
    protected $charset = 'UTF-8';
    public function setCharset($charset) {
        $this->charset = $charset; }
    public function getCharset() {
        return $this->charset; } }
namespace Symfony\Component\Templating\Helper;
class SlotsHelper extends Helper {
    protected $slots = array();
    protected $openSlots = array();
    public function start($name) {
        if (in_array($name, $this->openSlots)) {
            throw new \InvalidArgumentException(sprintf('A slot named "%s" is already started.', $name)); }
        $this->openSlots[] = $name;
        $this->slots[$name] = '';
        ob_start();
        ob_implicit_flush(0); }
    public function stop() {
        $content = ob_get_clean();
        if (!$this->openSlots) {
            throw new \LogicException('No slot started.'); }
        $name = array_pop($this->openSlots);
        $this->slots[$name] = $content; }
    public function has($name) {
        return isset($this->slots[$name]); }
    public function get($name, $default = false) {
        return isset($this->slots[$name]) ? $this->slots[$name] : $default; }
    public function set($name, $content) {
        $this->slots[$name] = $content; }
    public function output($name, $default = false) {
        if (!isset($this->slots[$name])) {
            if (false !== $default) {
                echo $default;
                return true; }
            return false; }
        echo $this->slots[$name];
        return true; }
    public function getName() {
        return 'slots'; } }
namespace Symfony\Bundle\FrameworkBundle\Templating\Helper;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\OutputEscaper\Escaper;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
class ActionsHelper extends Helper {
    protected $resolver;
    public function __construct(ControllerResolver $resolver) {
        $this->resolver = $resolver; }
    public function output($controller, array $options = array()) {
        echo $this->render($controller, $options); }
    public function render($controller, array $options = array()) {
        if (isset($options['path'])) {
            $options['path'] = Escaper::unescape($options['path']); }
        if (isset($options['query'])) {
            $options['query'] = Escaper::unescape($options['query']); }
        return $this->resolver->render($controller, $options); }
    public function getName() {
        return 'actions'; } }
namespace Symfony\Bundle\FrameworkBundle\Templating\Helper;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Routing\Router;
class RouterHelper extends Helper {
    protected $generator;
    public function __construct(Router $router) {
        $this->generator = $router->getGenerator(); }
    public function generate($name, array $parameters = array(), $absolute = false) {
        return $this->generator->generate($name, $parameters, $absolute); }
    public function getName() {
        return 'router'; } }
