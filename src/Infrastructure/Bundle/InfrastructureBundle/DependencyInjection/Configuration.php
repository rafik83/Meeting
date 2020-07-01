<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('infrastructure');
        $rootNode
            ->children()
                ->arrayNode('eu_countries')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('preferred_locales')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('default_locales')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('supported_currencies')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('web_asset_event_guideline_path')->end()
                ->scalarNode('bundle_guideline_path')->end()
                ->scalarNode('font_path')->end()
                ->scalarNode('image_path')->end()
                ->scalarNode('print_planning_path')->end()
                ->scalarNode('print_sheet_path')->end()
                ->scalarNode('export_transactions_path')->end()
                ->scalarNode('export_order_path')->end()
                ->scalarNode('export_products_path')->end()
                ->scalarNode('export_planner_path')->end()
                ->scalarNode('import_planner_path')->end()
                ->scalarNode('export_omz_path')->end()
                ->scalarNode('export_spot_path')->end()
                ->scalarNode('export_form_template_user_data_path')->end()
                ->scalarNode('export_rooming_list_path')->end()
                ->scalarNode('export_participant_path')->end()
                ->scalarNode('common_export_path')->end()
                ->scalarNode('print_sheet_path')->end()
                ->scalarNode('import_spot_path')->end()
                ->scalarNode('import_authentication_token_path')->end()
                ->scalarNode('encrypted_files_path')->end()
                ->scalarNode('print_invoices_path')->end()
                ->arrayNode('package')
                    ->children()
                        ->arrayNode('default_labels')
                            ->children()
                                ->arrayNode('plans')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('participant_and_planning')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('options')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('form_collection')
                    ->info('<info>Activate the Collection feature</info>')
                    ->defaultTrue()
                ->end()
                ->booleanNode('form_choice')
                    ->info('<info>Activate the Choice feature</info>')
                    ->defaultTrue()
                ->end()
                ->booleanNode('form_buttons')
                    ->info('<info>Activate the Form Buttons feature</info>')
                    ->defaultTrue()
                ->end()
                ->booleanNode('form_help')
                    ->info('<info>Activate the Help feature</info>')
                    ->defaultTrue()
                ->end()
                ->booleanNode('form_placeholder')
                    ->info('<info>Activate the Placeholder feature</info>')
                    ->defaultTrue()
                ->end()
                ->booleanNode('form_confirm')
                    ->info('<info>Activate the Confirm feature</info>')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
