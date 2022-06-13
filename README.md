# MultiConnectionMigrationBundle

This simple bundle provides basic doctrine migrations commands for the multiple connections

### Config reference with default values

```yaml

andreo_multi_connection_migrations:
    table_storage:
        table_name: doctrine_migration_versions
        version_column_name: version
        version_column_length: 1024
        executed_at_column_name: executed_at
        execution_time_column_name: execution_time
    connections:
        foo: # doctrine connection name
            migrations_paths:
                'Migrations\Foo':  '%kernel.project_dir%/migrations/foo'
            all_or_nothing: false
            transactional: true
            check_database_platform: true
            dependency_factory_alias: null
            organize_migrations: none # one of: none, year, year_and_month
```

### Commands

Example for `foo` connection name

```bash
bin/console andreo:multi-conn-doctrine-migrations:generate:foo # generate empty migrations

bin/console andreo:multi-conn-doctrine-migrations:migrate:foo # migrate

bin/console andreo:multi-conn-doctrine-migrations:execute:foo # execute one migration

```
