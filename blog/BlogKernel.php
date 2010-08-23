<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Framework\Kernel;
use Symfony\Component\DependencyInjection\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BlogKernel extends Kernel
{
    public function registerRootDir()
    {
        return __DIR__;
    }

    public function boot()
    {
        Symfony\Component\OutputEscaper\Escaper::markClassesAsSafe(array(
            'Symfony\Component\Form\Form',
            'Symfony\Component\Form\Field'
        ));

//        #TODO remove me
//        foreach(array('BundleDoctrineUserBundleEntityUserProxy', 'ApplicationMiamBundleEntityProjectProxy', 'ApplicationMiamBundleEntityStoryProxy', 'ApplicationMiamBundleEntitySprintProxy', 'ApplicationMiamBundleEntityTimelineEntryProxy') as $class) {
//            require_once(__DIR__.'/cache/'.$this->getEnvironment().'/doctrine/orm/Proxies/'.$class.'.php');
//        }

        return parent::boot();
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Framework\KernelBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            //new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),
            //new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            //new Symfony\Bundle\PropelBundle\PropelBundle(),
            //new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Application\BlogBundle\BlogBundle(),
        );

        if ($this->isDebug()) {
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'     => __DIR__.'/../src/Application',
            'Bundle'          => __DIR__.'/../src/Bundle',
            'Symfony\\Bundle' => __DIR__.'/../src/vendor/symfony/src/Symfony/Bundle',
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // use YAML for configuration
        // comment to use another configuration format
        $container = new ContainerBuilder();
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        $container->setParameter('validator.message_interpolator.class', 'Application\\BlogBundle\\Validator\\NoValidationXliffMessageInterpolator');
        return $container;

        // uncomment to use XML for configuration
        //$loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.xml');

        // uncomment to use PHP for configuration
        //$loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.php');
    }
}
