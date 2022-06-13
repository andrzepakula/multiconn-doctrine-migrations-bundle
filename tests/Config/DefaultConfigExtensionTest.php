<?php

declare(strict_types=1);

namespace Tests\Config;

use Andreo\MultiConnDoctrineMigrationsBundle\DependencyInjection\AndreoMultiConnDoctrineMigrationsExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class DefaultConfigExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function should_load_connections(): void
    {
        $this->load([
            'connections' => [
                'foo' => [
                    'migrations_paths' => [],
                ],
                'bar' => [
                    'migrations_paths' => [],
                    'dependency_factory_alias' => 'bar_alias',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.dependency_factory.foo');
        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.dependency_factory.bar');
        $this->assertContainerBuilderHasAlias('bar_alias');

        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.generate.foo');
        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.migrate.foo');
        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.execute.foo');

        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.generate.bar');
        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.migrate.bar');
        $this->assertContainerBuilderHasService('andreo.multi_conn_doctrine_migrations.command.execute.bar');
    }

    protected function getContainerExtensions(): array
    {
        return [
            new AndreoMultiConnDoctrineMigrationsExtension(),
        ];
    }
}
