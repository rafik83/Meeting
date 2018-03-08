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
        $container->setParameter('infrastructure.preferred_locales', $config['preferred_locales']);
        $container->setParameter('infrastructure.default_locales', $config['default_locales']);
        $container->setParameter('infrastructure.supported_currencies', $config['supported_currencies']);
        $container->setParameter('infrastructure.web_asset_event_guideline_path', $config['web_asset_event_guideline_path']);
        $container->setParameter('infrastructure.bundle_guideline_path', $config['bundle_guideline_path']);
        $container->setParameter('infrastructure.font_path', $config['font_path']);
        $container->setParameter('infrastructure.image_path', $config['image_path']);
        $container->setParameter('infrastructure.print_sheet_path', $config['print_sheet_path']);
        $container->setParameter('infrastructure.print_planning_path', $config['print_planning_path']);
        $container->setParameter('infrastructure.print_sheet_path', $config['print_sheet_path']);
        $container->setParameter('infrastructure.export_transactions_path', $config['export_transactions_path']);
        $container->setParameter('infrastructure.export_order_path', $config['export_order_path']);
        $container->setParameter('infrastructure.export_planner_path', $config['export_planner_path']);
        $container->setParameter('infrastructure.import_planner_path', $config['import_planner_path']);
        $container->setParameter('infrastructure.import_spot_path', $config['import_spot_path']);
        $container->setParameter('infrastructure.package.default_labels', $config['package']['default_labels']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_third_party_leni.yml');
        $loader->load('services_third_party_vianeo.yml');
        $loader->load('services_third_party_jenkins.yml');
        $loader->load('services_third_party_comexposium.yml');
    }
}
