<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class InfrastructureExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('infrastructure.eu_countries', $config['eu_countries']);
        $container->setParameter('infrastructure.web_asset_event_guideline_path', $config['web_asset_event_guideline_path']);
        $container->setParameter('infrastructure.bundle_guideline_path', $config['bundle_guideline_path']);
        $container->setParameter('infrastructure.font_path', $config['font_path']);
        $container->setParameter('infrastructure.image_path', $config['image_path']);
        $container->setParameter('infrastructure.package.default_labels', $config['package']['default_labels']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
