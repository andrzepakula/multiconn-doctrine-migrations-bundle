<?php

declare(strict_types=1);

namespace Andreo\MultiConnDoctrineMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('andreo_multi_conn_doctrine_migrations');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('table_storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('table_name')
                            ->defaultValue('doctrine_migration_versions')
                            ->cannotBeEmpty()
                        ->end()
                        ?->scalarNode('version_column_name')
                            ->defaultValue('version')
                            ->cannotBeEmpty()
                        ->end()
                        ?->integerNode('version_column_length')
                            ->defaultValue(1024)
                        ->end()
                        ?->scalarNode('executed_at_column_name')
                            ->defaultValue('executed_at')
                            ->cannotBeEmpty()
                        ->end()
                        ?->scalarNode('execution_time_column_name')
                            ->defaultValue('execution_time')
                            ->cannotBeEmpty()
                        ->end()
                    ?->end()
                ->end()
                ->arrayNode('connections')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('migrations_paths')
                                ->scalarPrototype()->end()
                            ?->end()
                            ->booleanNode('all_or_nothing')->defaultFalse()->end()
                            ?->booleanNode('transactional')->defaultTrue()->end()
                            ?->booleanNode('check_database_platform')->defaultTrue()->end()
                            ?->scalarNode('dependency_factory_alias')->defaultValue(null)->end()
                            ?->enumNode('organize_migrations')
                                ->values(['none', 'year', 'year_and_month'])
                                ->defaultValue('none')
                            ->end()
                        ?->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
