<?php

declare(strict_types=1);

namespace Andreo\MultiConnDoctrineMigrationsBundle\DependencyInjection;

use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AndreoMultiConnDoctrineMigrationsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadConnection($config, $container);
    }

    private function loadConnection(array $config, ContainerBuilder $container): void
    {
        $tableStorageConfig = $config['table_storage'];

        $tableStorageConfiguration = (new Definition(TableMetadataStorageConfiguration::class))
            ->addMethodCall('setTableName', [$tableStorageConfig['table_name']])
            ->addMethodCall('setVersionColumnName', [$tableStorageConfig['version_column_name']])
            ->addMethodCall('setVersionColumnLength', [$tableStorageConfig['version_column_length']])
            ->addMethodCall('setExecutedAtColumnName', [$tableStorageConfig['executed_at_column_name']])
            ->addMethodCall('setExecutionTimeColumnName', [$tableStorageConfig['execution_time_column_name']])
        ;

        foreach ($config['connections'] as $connectionName => $connectionConfig) {
            $connectionRef = new Reference(sprintf('doctrine.dbal.%s_connection', $connectionName));

            $configurationDef = new Definition(MigrationsConfiguration::class, [
                $connectionRef,
            ]);

            foreach ($connectionConfig['migrations_paths'] as $namespace => $path) {
                $configurationDef->addMethodCall('addMigrationsDirectory', [$namespace, $path]);
            }

            $configurationDef
                ->addMethodCall('setAllOrNothing', [$connectionConfig['all_or_nothing']])
                ->addMethodCall('setTransactional', [$connectionConfig['transactional']])
                ->addMethodCall('setCheckDatabasePlatform', [$connectionConfig['check_database_platform']])
                ->addMethodCall('setMigrationOrganization', [$connectionConfig['organize_migrations']])
                ->addMethodCall('setMetadataStorageConfiguration', [$tableStorageConfiguration])
            ;

            $dependencyFactoryAlias = $connectionConfig['dependency_factory_alias'];
            $dependencyFactoryAlias = $dependencyFactoryAlias ?? "andreo.multi_conn_doctrine_migrations.dependency_factory.$connectionName";

            $container
                ->register($dependencyFactoryAlias, DependencyFactory::class)
                ->setFactory([DependencyFactory::class, 'fromConnection'])
                ->addArgument(new Definition(ExistingConfiguration::class, [$configurationDef]))
                ->addArgument(new Definition(ExistingConnection::class, [$connectionRef]))
                ->setPublic(false)
            ;

            $container
                ->register("andreo.multi_conn_doctrine_migrations.command.generate.$connectionName", GenerateCommand::class)
                ->addArgument(new Reference($dependencyFactoryAlias))
                ->addTag('console.command', [
                    'command' => "andreo:multi-conn-doctrine-migrations:generate:$connectionName",
                ])
                ->setPublic(false)
            ;

            $container
                ->register("andreo.multi_conn_doctrine_migrations.command.migrate.$connectionName", MigrateCommand::class)
                ->addArgument(new Reference($dependencyFactoryAlias))
                ->addTag('console.command', [
                    'command' => "andreo:multi-conn-doctrine-migrations:migrate:$connectionName",
                ])
                ->setPublic(false)
            ;

            $container
                ->register("andreo.multi_conn_doctrine_migrations.command.execute.$connectionName", ExecuteCommand::class)
                ->addArgument(new Reference($dependencyFactoryAlias))
                ->addTag('console.command', [
                    'command' => "andreo:multi-conn-doctrine-migrations:execute:$connectionName",
                ])
                ->setPublic(false)
            ;
        }
    }
}
