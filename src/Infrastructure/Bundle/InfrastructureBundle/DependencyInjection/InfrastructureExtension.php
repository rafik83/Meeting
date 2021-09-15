<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class InfrastructureExtension extends Extension
{
    /**
     * Features
     *
     * @var array
     */
    private const FORM_FEATURES = [
        'form_collection'  => 'form_collection.xml',
        'form_choice'      => 'form_choice.xml',
        'form_buttons'     => 'form_buttons.xml',
        'form_help'        => 'form_help.xml',
        'form_placeholder' => 'form_placeholder.xml',
        'form_confirm'     => 'form_confirm.xml',
    ];

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
        $container->setParameter('infrastructure.export_products_path', $config['export_products_path']);
        $container->setParameter('infrastructure.export_omz_path', $config['export_omz_path']);
        $container->setParameter('infrastructure.export_spot_path', $config['export_spot_path']);
        $container->setParameter('infrastructure.export_form_template_user_data_path', $config['export_form_template_user_data_path']);
        $container->setParameter('infrastructure.export_rooming_list_path', $config['export_rooming_list_path']);
        $container->setParameter('infrastructure.export_planner_path', $config['export_planner_path']);
        $container->setParameter('infrastructure.import_planner_path', $config['import_planner_path']);
        $container->setParameter('infrastructure.import_spot_path', $config['import_spot_path']);
        $container->setParameter('infrastructure.import_authentication_token_path', $config['import_authentication_token_path']);
        $container->setParameter('infrastructure.export_participant_path', $config['export_participant_path']);
        $container->setParameter('infrastructure.common_export_path', $config['common_export_path']);
        $container->setParameter('infrastructure.encrypted_files_path', $config['encrypted_files_path']);
        $container->setParameter('infrastructure.package.default_labels', $config['package']['default_labels']);
        $container->setParameter('infrastructure.print_invoices_path', $config['print_invoices_path']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_third_party_tech_event.yml');
        $loader->load('services_third_party_leni.yml');
        $loader->load('services_third_party_vianeo.yml');
        $loader->load('services_third_party_jenkins.yml');
        $loader->load('services_third_party_comexposium.yml');
        $loader->load('services_third_party_openl10n.yml');
        $loader->load('services_third_party_oauth2.yml');

        $loaderXml = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach (self::FORM_FEATURES as $option => $xml) {
            if ($config[$option]) {
                $loaderXml->load($xml);
            }
        }
    }
}
