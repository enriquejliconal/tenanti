<?php

namespace Orchestra\Tenanti\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Factory
{
    /**
     * Install migrations.
     *
     * @param  mixed|null  $id
     */
    public function install(?string $database, $id = null): void;

    /**
     * Run migrations.
     *
     * @param  mixed|null  $id
     */
    public function run(?string $database, $id = null, bool $pretend = false): void;

    /**
     * Rollback migrations.
     *
     * @param  mixed|null  $id
     */
    public function rollback(?string $database, $id = null, bool $pretend = false): void;

    /**
     * Reset migrations.
     *
     * @param  mixed|null  $id
     */
    public function reset(?string $database, $id = null, bool $pretend = false): void;

    /**
     * Run migration up on a single entity.
     */
    public function runInstall(Model $entity, ?string $database): void;

    /**
     * Run migration up on a single entity.
     */
    public function runUp(Model $entity, ?string $database, bool $pretend = false): void;

    /**
     * Run migration down on a single entity.
     */
    public function runDown(Model $entity, ?string $database, bool $pretend = false): void;

    /**
     * Run migration down on a single entity.
     */
    public function runReset(Model $entity, ?string $database, bool $pretend = false): void;

    /**
     * Get migration paths.
     */
    public function getMigrationPaths(Model $entity = null): ?array;

    /**
     * Get model name.
     */
    public function modelName(): string;

    /**
     * Resolve model.
     *
     * @throws \InvalidArgumentException
     */
    public function model(): Model;

    /**
     * Get table prefix.
     */
    public function tablePrefix(): string;

    /**
     * Set tenant as default database connection and get the connection name.
     */
    public function asDefaultConnection(Model $entity, ?string $database): ?string;
}
