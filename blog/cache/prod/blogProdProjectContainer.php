<?php
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
class blogProdProjectContainer extends Container {
    protected $shared = array();
    public function __construct() {
        parent::__construct(new FrozenParameterBag($this->getDefaultParameters())); }
    protected function getEventDispatcherService() {
        if (isset($this->shared['event_dispatcher'])) return $this->shared['event_dispatcher'];
        $class = 'Symfony\\Framework\\EventDispatcher';
        $instance = new $class($this);
        $this->shared['event_dispatcher'] = $instance;
        return $instance; }
    protected function getErrorHandlerService() {
        if (isset($this->shared['error_handler'])) return $this->shared['error_handler'];
        $class = 'Symfony\\Framework\\Debug\\ErrorHandler';
        $instance = new $class(NULL);
        $this->shared['error_handler'] = $instance;
        $instance->register();
        return $instance; }
    protected function getHttpKernelService() {
        if (isset($this->shared['http_kernel'])) return $this->shared['http_kernel'];
        $class = 'Symfony\\Component\\HttpKernel\\HttpKernel';
        $instance = new $class($this->getEventDispatcherService(), $this->getControllerResolverService());
        $this->shared['http_kernel'] = $instance;
        return $instance; }
    protected function getRequestService() {
        if (isset($this->shared['request'])) return $this->shared['request'];
        $class = 'Symfony\\Component\\HttpFoundation\\Request';
        $instance = new $class();
        $this->shared['request'] = $instance;
        if ($this->has('session')) {
            $instance->setSession($this->get('session', ContainerInterface::NULL_ON_INVALID_REFERENCE)); }
        return $instance; }
    protected function getResponseService() {
        $class = 'Symfony\\Component\\HttpFoundation\\Response';
        $instance = new $class();
        return $instance; }
    protected function getControllerNameConverterService() {
        if (isset($this->shared['controller_name_converter'])) return $this->shared['controller_name_converter'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameConverter';
        $instance = new $class($this->get('kernel'), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['controller_name_converter'] = $instance;
        return $instance; }
    protected function getControllerResolverService() {
        if (isset($this->shared['controller_resolver'])) return $this->shared['controller_resolver'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver';
        $instance = new $class($this, $this->getControllerNameConverterService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['controller_resolver'] = $instance;
        return $instance; }
    protected function getRequestListenerService() {
        if (isset($this->shared['request_listener'])) return $this->shared['request_listener'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\RequestListener';
        $instance = new $class($this->getRouterService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['request_listener'] = $instance;
        return $instance; }
    protected function getEsiService() {
        if (isset($this->shared['esi'])) return $this->shared['esi'];
        $class = 'Symfony\\Component\\HttpKernel\\Cache\\Esi';
        $instance = new $class();
        $this->shared['esi'] = $instance;
        return $instance; }
    protected function getEsiListenerService() {
        if (isset($this->shared['esi_listener'])) return $this->shared['esi_listener'];
        $class = 'Symfony\\Component\\HttpKernel\\Cache\\EsiListener';
        $instance = new $class($this->get('esi', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['esi_listener'] = $instance;
        return $instance; }
    protected function getResponseListenerService() {
        if (isset($this->shared['response_listener'])) return $this->shared['response_listener'];
        $class = 'Symfony\\Component\\HttpKernel\\ResponseListener';
        $instance = new $class();
        $this->shared['response_listener'] = $instance;
        return $instance; }
    protected function getExceptionListenerService() {
        if (isset($this->shared['exception_listener'])) return $this->shared['exception_listener'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Debug\\ExceptionListener';
        $instance = new $class($this, 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController::exceptionAction', $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['exception_listener'] = $instance;
        return $instance; }
    protected function getRouting_ResolverService() {
        if (isset($this->shared['routing.resolver'])) return $this->shared['routing.resolver'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Routing\\LoaderResolver';
        $instance = new $class($this);
        $this->shared['routing.resolver'] = $instance;
        return $instance; }
    protected function getRouting_Loader_XmlService() {
        if (isset($this->shared['routing.loader.xml'])) return $this->shared['routing.loader.xml'];
        $class = 'Symfony\\Component\\Routing\\Loader\\XmlFileLoader';
        $instance = new $class(array('Application' => '/var/www/sugarbox/blog/../src/Application', 'Bundle' => '/var/www/sugarbox/blog/../src/Bundle', 'Symfony\\Bundle' => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle'));
        $this->shared['routing.loader.xml'] = $instance;
        return $instance; }
    protected function getRouting_Loader_YmlService() {
        if (isset($this->shared['routing.loader.yml'])) return $this->shared['routing.loader.yml'];
        $class = 'Symfony\\Component\\Routing\\Loader\\YamlFileLoader';
        $instance = new $class(array('Application' => '/var/www/sugarbox/blog/../src/Application', 'Bundle' => '/var/www/sugarbox/blog/../src/Bundle', 'Symfony\\Bundle' => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle'));
        $this->shared['routing.loader.yml'] = $instance;
        return $instance; }
    protected function getRouting_Loader_PhpService() {
        if (isset($this->shared['routing.loader.php'])) return $this->shared['routing.loader.php'];
        $class = 'Symfony\\Component\\Routing\\Loader\\PhpFileLoader';
        $instance = new $class(array('Application' => '/var/www/sugarbox/blog/../src/Application', 'Bundle' => '/var/www/sugarbox/blog/../src/Bundle', 'Symfony\\Bundle' => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle'));
        $this->shared['routing.loader.php'] = $instance;
        return $instance; }
    protected function getRouting_LoaderService() {
        if (isset($this->shared['routing.loader'])) return $this->shared['routing.loader'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader';
        $instance = new $class($this->getControllerNameConverterService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->getRouting_ResolverService());
        $this->shared['routing.loader'] = $instance;
        return $instance; }
    protected function getRouterService() {
        if (isset($this->shared['router'])) return $this->shared['router'];
        $class = 'Symfony\\Component\\Routing\\Router';
        $instance = new $class($this->getRouting_LoaderService(), '/var/www/sugarbox/blog/config/routing.yml', array('cache_dir' => '/var/www/sugarbox/blog/cache/prod', 'debug' => false, 'matcher_cache_class' => 'blog'.'UrlMatcher', 'generator_cache_class' => 'blog'.'UrlGenerator'));
        $this->shared['router'] = $instance;
        return $instance; }
    protected function getValidatorService() {
        if (isset($this->shared['validator'])) return $this->shared['validator'];
        $class = 'Symfony\\Component\\Validator\\Validator';
        $instance = new $class($this->getValidator_Mapping_ClassMetadataFactoryService(), $this->getValidator_ValidatorFactoryService(), $this->getValidator_MessageInterpolatorService());
        $this->shared['validator'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_ClassMetadataFactoryService() {
        if (isset($this->shared['validator.mapping.class_metadata_factory'])) return $this->shared['validator.mapping.class_metadata_factory'];
        $class = 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory';
        $instance = new $class($this->getValidator_Mapping_Loader_LoaderChainService());
        $this->shared['validator.mapping.class_metadata_factory'] = $instance;
        return $instance; }
    protected function getValidator_ValidatorFactoryService() {
        if (isset($this->shared['validator.validator_factory'])) return $this->shared['validator.validator_factory'];
        $class = 'Symfony\\Component\\Validator\\Extension\\DependencyInjectionValidatorFactory';
        $instance = new $class($this);
        $this->shared['validator.validator_factory'] = $instance;
        return $instance; }
    protected function getValidator_MessageInterpolatorService() {
        if (isset($this->shared['validator.message_interpolator'])) return $this->shared['validator.message_interpolator'];
        $class = 'Symfony\\Component\\Validator\\MessageInterpolator\\XliffMessageInterpolator';
        $instance = new $class(array(0 => '/var/www/sugarbox/src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Validator/Resources/i18n/messages.en.xml', 1 => '/var/www/sugarbox/src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Form/Resources/i18n/messages.en.xml'));
        $this->shared['validator.message_interpolator'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_Loader_LoaderChainService() {
        if (isset($this->shared['validator.mapping.loader.loader_chain'])) return $this->shared['validator.mapping.loader.loader_chain'];
        $class = 'Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain';
        $instance = new $class(array(0 => $this->getValidator_Mapping_Loader_AnnotationLoaderService(), 1 => $this->getValidator_Mapping_Loader_StaticMethodLoaderService(), 2 => $this->getValidator_Mapping_Loader_XmlFilesLoaderService(), 3 => $this->getValidator_Mapping_Loader_YamlFilesLoaderService()));
        $this->shared['validator.mapping.loader.loader_chain'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_Loader_StaticMethodLoaderService() {
        if (isset($this->shared['validator.mapping.loader.static_method_loader'])) return $this->shared['validator.mapping.loader.static_method_loader'];
        $class = 'Symfony\\Component\\Validator\\Mapping\\Loader\\StaticMethodLoader';
        $instance = new $class('loadValidatorMetadata');
        $this->shared['validator.mapping.loader.static_method_loader'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_Loader_XmlFilesLoaderService() {
        if (isset($this->shared['validator.mapping.loader.xml_files_loader'])) return $this->shared['validator.mapping.loader.xml_files_loader'];
        $instance = new Symfony\Component\Validator\Mapping\Loader\XmlFilesLoader(array(0 => '/var/www/sugarbox/src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Form/Resources/config/validation.xml'));
        $this->shared['validator.mapping.loader.xml_files_loader'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_Loader_YamlFilesLoaderService() {
        if (isset($this->shared['validator.mapping.loader.yaml_files_loader'])) return $this->shared['validator.mapping.loader.yaml_files_loader'];
        $instance = new Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader(array());
        $this->shared['validator.mapping.loader.yaml_files_loader'] = $instance;
        return $instance; }
    protected function getValidator_Mapping_Loader_AnnotationLoaderService() {
        if (isset($this->shared['validator.mapping.loader.annotation_loader'])) return $this->shared['validator.mapping.loader.annotation_loader'];
        $instance = new Symfony\Component\Validator\Mapping\Loader\AnnotationLoader();
        $this->shared['validator.mapping.loader.annotation_loader'] = $instance;
        return $instance; }
    protected function getTemplating_EngineService() {
        if (isset($this->shared['templating.engine'])) return $this->shared['templating.engine'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Engine';
        $instance = new $class($this, $this->getTemplating_Loader_FilesystemService(), array(), 'htmlspecialchars');
        $this->shared['templating.engine'] = $instance;
        $instance->setCharset('UTF-8');
        return $instance; }
    protected function getTemplating_Loader_FilesystemService() {
        if (isset($this->shared['templating.loader.filesystem'])) return $this->shared['templating.loader.filesystem'];
        $class = 'Symfony\\Component\\Templating\\Loader\\FilesystemLoader';
        $instance = new $class(array(0 => '/var/www/sugarbox/blog/views/%bundle%/%controller%/%name%%format%.%renderer%', 1 => '/var/www/sugarbox/blog/../src/Application/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%', 2 => '/var/www/sugarbox/blog/../src/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%', 3 => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%'));
        $this->shared['templating.loader.filesystem'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE)); }
        return $instance; }
    protected function getTemplating_Loader_CacheService() {
        if (isset($this->shared['templating.loader.cache'])) return $this->shared['templating.loader.cache'];
        $class = 'Symfony\\Component\\Templating\\Loader\\CacheLoader';
        $instance = new $class($this->get('templating.loader.wrapped'), NULL);
        $this->shared['templating.loader.cache'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE)); }
        return $instance; }
    protected function getTemplating_Loader_ChainService() {
        if (isset($this->shared['templating.loader.chain'])) return $this->shared['templating.loader.chain'];
        $class = 'Symfony\\Component\\Templating\\Loader\\ChainLoader';
        $instance = new $class();
        $this->shared['templating.loader.chain'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE)); }
        return $instance; }
    protected function getTemplating_Helper_JavascriptsService() {
        if (isset($this->shared['templating.helper.javascripts'])) return $this->shared['templating.helper.javascripts'];
        $class = 'Symfony\\Component\\Templating\\Helper\\JavascriptsHelper';
        $instance = new $class($this->getTemplating_Helper_AssetsService());
        $this->shared['templating.helper.javascripts'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_StylesheetsService() {
        if (isset($this->shared['templating.helper.stylesheets'])) return $this->shared['templating.helper.stylesheets'];
        $class = 'Symfony\\Component\\Templating\\Helper\\StylesheetsHelper';
        $instance = new $class($this->getTemplating_Helper_AssetsService());
        $this->shared['templating.helper.stylesheets'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_SlotsService() {
        if (isset($this->shared['templating.helper.slots'])) return $this->shared['templating.helper.slots'];
        $class = 'Symfony\\Component\\Templating\\Helper\\SlotsHelper';
        $instance = new $class();
        $this->shared['templating.helper.slots'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_AssetsService() {
        if (isset($this->shared['templating.helper.assets'])) return $this->shared['templating.helper.assets'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper';
        $instance = new $class($this->getRequestService(), array(), NULL);
        $this->shared['templating.helper.assets'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_RequestService() {
        if (isset($this->shared['templating.helper.request'])) return $this->shared['templating.helper.request'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RequestHelper';
        $instance = new $class($this->getRequestService());
        $this->shared['templating.helper.request'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_SessionService() {
        if (isset($this->shared['templating.helper.session'])) return $this->shared['templating.helper.session'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\SessionHelper';
        $instance = new $class($this->getRequestService());
        $this->shared['templating.helper.session'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_RouterService() {
        if (isset($this->shared['templating.helper.router'])) return $this->shared['templating.helper.router'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper';
        $instance = new $class($this->getRouterService());
        $this->shared['templating.helper.router'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_ActionsService() {
        if (isset($this->shared['templating.helper.actions'])) return $this->shared['templating.helper.actions'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\ActionsHelper';
        $instance = new $class($this->getControllerResolverService());
        $this->shared['templating.helper.actions'] = $instance;
        return $instance; }
    protected function getTemplating_Helper_CodeService() {
        if (isset($this->shared['templating.helper.code'])) return $this->shared['templating.helper.code'];
        $class = 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\CodeHelper';
        $instance = new $class(NULL);
        $this->shared['templating.helper.code'] = $instance;
        return $instance; }
    protected function getTemplating_LoaderService() {
        return $this->getTemplating_Loader_FilesystemService(); }
    protected function getTemplatingService() {
        return $this->getTemplating_EngineService(); }
    public function findTaggedServiceIds($name) {
        static $tags = array (
  'kernel.listener' =>
  array (
    'request_listener' =>
    array (
      0 =>
      array (
      ),
    ),
    'esi_listener' =>
    array (
      0 =>
      array (
      ),
    ),
    'response_listener' =>
    array (
      0 =>
      array (
      ),
    ),
    'exception_listener' =>
    array (
      0 =>
      array (
      ),
    ),
  ),
  'routing.loader' =>
  array (
    'routing.loader.xml' =>
    array (
      0 =>
      array (
      ),
    ),
    'routing.loader.yml' =>
    array (
      0 =>
      array (
      ),
    ),
    'routing.loader.php' =>
    array (
      0 =>
      array (
      ),
    ),
  ),
  'templating.helper' =>
  array (
    'templating.helper.javascripts' =>
    array (
      0 =>
      array (
        'alias' => 'javascripts',
      ),
    ),
    'templating.helper.stylesheets' =>
    array (
      0 =>
      array (
        'alias' => 'stylesheets',
      ),
    ),
    'templating.helper.slots' =>
    array (
      0 =>
      array (
        'alias' => 'slots',
      ),
    ),
    'templating.helper.assets' =>
    array (
      0 =>
      array (
        'alias' => 'assets',
      ),
    ),
    'templating.helper.request' =>
    array (
      0 =>
      array (
        'alias' => 'request',
      ),
    ),
    'templating.helper.session' =>
    array (
      0 =>
      array (
        'alias' => 'session',
      ),
    ),
    'templating.helper.router' =>
    array (
      0 =>
      array (
        'alias' => 'router',
      ),
    ),
    'templating.helper.actions' =>
    array (
      0 =>
      array (
        'alias' => 'actions',
      ),
    ),
    'templating.helper.code' =>
    array (
      0 =>
      array (
        'alias' => 'code',
      ),
    ),
  ),
);
        return isset($tags[$name]) ? $tags[$name] : array(); }
    protected function getDefaultParameters() {
        return array(
            'kernel.root_dir' => '/var/www/sugarbox/blog',
            'kernel.environment' => 'prod',
            'kernel.debug' => false,
            'kernel.name' => 'blog',
            'kernel.cache_dir' => '/var/www/sugarbox/blog/cache/prod',
            'kernel.logs_dir' => '/var/www/sugarbox/blog/logs',
            'kernel.bundle_dirs' => array(
                'Application' => '/var/www/sugarbox/blog/../src/Application',
                'Bundle' => '/var/www/sugarbox/blog/../src/Bundle',
                'Symfony\\Bundle' => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle',
            ),
            'kernel.bundles' => array(
                0 => 'Symfony\\Framework\\KernelBundle',
                1 => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                2 => 'Symfony\\Bundle\\ZendBundle\\ZendBundle',
                3 => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle',
                4 => 'Symfony\\Bundle\\DoctrineBundle\\DoctrineBundle',
                5 => 'Application\\BlogBundle\\BlogBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.compiled_classes' => array(
                0 => 'Symfony\\Component\\Routing\\RouterInterface',
                1 => 'Symfony\\Component\\Routing\\Router',
                2 => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface',
                3 => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
                4 => 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface',
                5 => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
                6 => 'Symfony\\Component\\Routing\\Loader\\Loader',
                7 => 'Symfony\\Component\\Routing\\Loader\\DelegatingLoader',
                8 => 'Symfony\\Component\\Routing\\Loader\\LoaderResolver',
                9 => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\LoaderResolver',
                10 => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader',
                11 => 'Symfony\\Component\\HttpFoundation\\ParameterBag',
                12 => 'Symfony\\Component\\HttpFoundation\\HeaderBag',
                13 => 'Symfony\\Component\\HttpFoundation\\Request',
                14 => 'Symfony\\Component\\HttpFoundation\\Response',
                15 => 'Symfony\\Component\\HttpKernel\\HttpKernel',
                16 => 'Symfony\\Component\\HttpKernel\\ResponseListener',
                17 => 'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver',
                18 => 'Symfony\\Bundle\\FrameworkBundle\\RequestListener',
                19 => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameConverter',
                20 => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
                21 => 'Symfony\\Component\\EventDispatcher\\Event',
                22 => 'Symfony\\Bundle\\FrameworkBundle\\Controller',
                23 => 'Symfony\\Component\\Templating\\Loader\\LoaderInterface',
                24 => 'Symfony\\Component\\Templating\\Loader\\Loader',
                25 => 'Symfony\\Component\\Templating\\Loader\\FilesystemLoader',
                26 => 'Symfony\\Component\\Templating\\Engine',
                27 => 'Symfony\\Component\\Templating\\Renderer\\RendererInterface',
                28 => 'Symfony\\Component\\Templating\\Renderer\\Renderer',
                29 => 'Symfony\\Component\\Templating\\Renderer\\PhpRenderer',
                30 => 'Symfony\\Component\\Templating\\Storage\\Storage',
                31 => 'Symfony\\Component\\Templating\\Storage\\FileStorage',
                32 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Engine',
                33 => 'Symfony\\Component\\Templating\\Helper\\Helper',
                34 => 'Symfony\\Component\\Templating\\Helper\\SlotsHelper',
                35 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\ActionsHelper',
                36 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper',
                37 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper',
            ),
            'event_dispatcher.class' => 'Symfony\\Framework\\EventDispatcher',
            'http_kernel.class' => 'Symfony\\Component\\HttpKernel\\HttpKernel',
            'request.class' => 'Symfony\\Component\\HttpFoundation\\Request',
            'response.class' => 'Symfony\\Component\\HttpFoundation\\Response',
            'error_handler.class' => 'Symfony\\Framework\\Debug\\ErrorHandler',
            'error_handler.level' => NULL,
            'error_handler.enable' => true,
            'request_listener.class' => 'Symfony\\Bundle\\FrameworkBundle\\RequestListener',
            'controller_resolver.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
            'controller_name_converter.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameConverter',
            'response_listener.class' => 'Symfony\\Component\\HttpKernel\\ResponseListener',
            'exception_listener.class' => 'Symfony\\Bundle\\FrameworkBundle\\Debug\\ExceptionListener',
            'exception_listener.controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController::exceptionAction',
            'exception_manager.class' => 'Symfony\\Bundle\\FrameworkBundle\\Debug\\ExceptionManager',
            'esi.class' => 'Symfony\\Component\\HttpKernel\\Cache\\Esi',
            'esi_listener.class' => 'Symfony\\Component\\HttpKernel\\Cache\\EsiListener',
            'router.class' => 'Symfony\\Component\\Routing\\Router',
            'routing.loader.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader',
            'routing.resolver.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\LoaderResolver',
            'routing.loader.xml.class' => 'Symfony\\Component\\Routing\\Loader\\XmlFileLoader',
            'routing.loader.yml.class' => 'Symfony\\Component\\Routing\\Loader\\YamlFileLoader',
            'routing.loader.php.class' => 'Symfony\\Component\\Routing\\Loader\\PhpFileLoader',
            'routing.resource' => '/var/www/sugarbox/blog/config/routing.yml',
            'validator.class' => 'Symfony\\Component\\Validator\\Validator',
            'validator.validator_factory.class' => 'Symfony\\Component\\Validator\\Extension\\DependencyInjectionValidatorFactory',
            'validator.message_interpolator.class' => 'Symfony\\Component\\Validator\\MessageInterpolator\\XliffMessageInterpolator',
            'validator.mapping.class_metadata_factory.class' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory',
            'validator.mapping.loader.loader_chain.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain',
            'validator.mapping.loader.static_method_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\StaticMethodLoader',
            'validator.mapping.loader.annotation_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\AnnotationLoader',
            'validator.mapping.loader.xml_file_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\XmlFileLoader',
            'validator.mapping.loader.yaml_file_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\YamlFileLoader',
            'validator.mapping.loader.xml_files_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\XmlFilesLoader',
            'validator.mapping.loader.yaml_files_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\YamlFilesLoader',
            'validator.mapping.loader.static_method_loader.method_name' => 'loadValidatorMetadata',
            'validator.message_interpolator.files' => array(
                0 => '/var/www/sugarbox/src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Validator/Resources/i18n/messages.en.xml',
                1 => '/var/www/sugarbox/src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Form/Resources/i18n/messages.en.xml',
            ),
            'templating.engine.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Engine',
            'templating.loader.filesystem.class' => 'Symfony\\Component\\Templating\\Loader\\FilesystemLoader',
            'templating.loader.cache.class' => 'Symfony\\Component\\Templating\\Loader\\CacheLoader',
            'templating.loader.chain.class' => 'Symfony\\Component\\Templating\\Loader\\ChainLoader',
            'templating.helper.javascripts.class' => 'Symfony\\Component\\Templating\\Helper\\JavascriptsHelper',
            'templating.helper.stylesheets.class' => 'Symfony\\Component\\Templating\\Helper\\StylesheetsHelper',
            'templating.helper.slots.class' => 'Symfony\\Component\\Templating\\Helper\\SlotsHelper',
            'templating.helper.assets.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper',
            'templating.helper.actions.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\ActionsHelper',
            'templating.helper.router.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper',
            'templating.helper.request.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RequestHelper',
            'templating.helper.session.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\SessionHelper',
            'templating.helper.code.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\CodeHelper',
            'templating.output_escaper' => 'htmlspecialchars',
            'templating.assets.version' => NULL,
            'templating.assets.base_urls' => array(
            ),
            'debug.file_link_format' => NULL,
            'templating.loader.filesystem.path' => array(
                0 => '/var/www/sugarbox/blog/views/%bundle%/%controller%/%name%%format%.%renderer%',
                1 => '/var/www/sugarbox/blog/../src/Application/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
                2 => '/var/www/sugarbox/blog/../src/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
                3 => '/var/www/sugarbox/blog/../src/vendor/symfony/src/Symfony/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
            ),
            'templating.loader.cache.path' => NULL,
        ); } }
