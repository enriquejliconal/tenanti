<?php

namespace Orchestra\Tenanti\Tests\Unit\Migrator;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use Orchestra\Tenanti\Migrator\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    use Operation;

    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::asDefaultDatabase()
     * method.
     *
     * @test
     */
    public function testAsDefaultDatabaseMethod()
    {
        $this->app = m::mock('Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $repository = new Repository([
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'tenant' => [
                        'database' => 'tenants',
                    ],
                ],
            ],
        ]);

        $this->app->shouldReceive('make')->times(3)->with('config')->andReturn($repository);

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('config')->with('user.connection', null)->andReturn([
                    'template' => $repository->get('database.connections.tenant'),
                    'resolver' => function (Model $entity, array $config) {
                        return array_merge($config, [
                            'database' => "tenants_{$entity->getKey()}",
                        ]);
                    },
                    'name' => 'tenant_{id}',
                    'options' => ['only' => ['user']],
                ]);

        $this->manager = $manager;

        $model = m::mock('Illuminate\Database\Eloquent\Model');

        $model->shouldReceive('getKey')->twice()->andReturn(5)
            ->shouldReceive('toArray')->once()->andReturn([
                'id' => 5,
            ]);

        $this->assertEquals('tenant_5', $this->asDefaultConnection($model, 'tenant_{id}'));
        $this->assertEquals(['database' => 'tenants_5'], $repository->get('database.connections.tenant_5'));
        $this->assertEquals('tenant_5', $repository->get('database.default'));
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::connectionName()
     * method.
     *
     * @test
     */
    public function testAsConnectionMethod()
    {
        $this->app = m::mock('Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $repository = new Repository([
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'tenant' => [
                        'database' => 'tenants',
                    ],
                ],
            ],
        ]);

        $this->app->shouldReceive('make')->twice()->with('config')->andReturn($repository);

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('config')->with('user.connection', null)->andReturn([
                'template' => $repository->get('database.connections.tenant'),
                'resolver' => function (Model $entity, array $config) {
                    return array_merge($config, [
                        'database' => "tenants_{$entity->getKey()}",
                    ]);
                },
                'name' => 'tenant_{id}',
                'options' => [],
            ]);

        $this->manager = $manager;

        $model = m::mock('Illuminate\Database\Eloquent\Model');

        $model->shouldReceive('getKey')->twice()->andReturn(5)
            ->shouldReceive('toArray')->once()->andReturn([
                'id' => 5,
            ]);

        $this->assertEquals('tenant_5', $this->connectionName($model, 'tenant_{id}'));
        $this->assertEquals(['database' => 'tenants_5'], $repository->get('database.connections.tenant_5'));
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::resolveModel()
     * method.
     *
     * @test
     */
    public function testResolveModelMethod()
    {
        $this->app = m::mock('Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $this->app->shouldReceive('make')->once()->with('config')->andReturn(m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('config')->with('user.model', null)->andReturn('User')
            ->shouldReceive('config')->with('user.database', null)->andReturnNull();

        $this->manager = $manager;

        $model = m::mock('Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->once()->with('User')->andReturn($model);

        $model->shouldReceive('useWritePdo')->once()->andReturnSelf();

        $this->assertEquals($model, $this->model());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::resolveModel()
     * method with connection name.
     *
     * @test
     */
    public function testResolveModelMethodWithConnectionName()
    {
        $this->app = m::mock('Illuminate\Container\Container[make]');
        $this->driver = 'user';


        $this->app->shouldReceive('make')->once()->with('config')->andReturn(m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('config')->with('user.model', null)->andReturn('User')
            ->shouldReceive('config')->with('user.database', null)->andReturn('primary');

        $this->manager = $manager;

        $model = m::mock('Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->once()->with('User')->andReturn($model);

        $model->shouldReceive('setConnection')->once()->with('primary')->andReturnSelf()
            ->shouldReceive('useWritePdo')->once()->andReturnSelf();

        $this->assertEquals($model, $this->model());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::resolveModel()
     * method throw an exception when model is not an instance of
     * Eloquent.
     *
     * @test
     */
    public function testResolveModelMethodThrowsException()
    {
        $this->expectException('InvalidArgumentException');

        $this->app = m::mock('Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $this->app->shouldReceive('make')->once()->with('config')->andReturn(m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('config')->with('user.model', null)->andReturn('User');

        $this->manager = $manager;

        $this->app->shouldReceive('make')->once()->with('User')->andReturnNull();

        $this->model();
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::modelName()
     * method.
     *
     * @test
     */
    public function testGetModelNameMethod()
    {
        $app = new Container();
        $this->driver = 'user';


        $app->instance('config', m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('config')->with('user.model', null)->andReturn('User');

        $this->manager = $manager;

        $this->assertEquals('User', $this->modelName());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::getMigrationPaths()
     * method.
     *
     * @test
     */
    public function testGetMigrationPathsMethod()
    {
        $this->driver = 'user';
        $path = realpath(__DIR__);
        $app = new Container();

        $app->instance('config', m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('config')->with('user.paths', [])->andReturn([$path]);

        $this->manager = $manager;

        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $model->shouldReceive('getKey')->andReturn(5);
        $this->loadMigrationsFrom('customPath', $model);
        $this->assertEquals([$path], $this->getDefaultMigrationPaths());
        $this->assertEquals([$path, 'customPath'], $this->getMigrationPaths($model));

        $model2 = m::mock('Illuminate\Database\Eloquent\Model');
        $model2->shouldReceive('getKey')->andReturn(6);
        $this->loadMigrationsFrom(['customPath', 'customPath2'], $model2);
        $this->assertEquals([$path], $this->getDefaultMigrationPaths());
        $this->assertEquals([$path, 'customPath', 'customPath2'], $this->getMigrationPaths($model2));

        $model3 = m::mock('Illuminate\Database\Eloquent\Model');
        $model3->shouldReceive('getKey')->andReturn(7);
        $this->assertEquals([$path], $this->getDefaultMigrationPaths());
        $this->assertEquals([$path], $this->getMigrationPaths($model3));

        $this->assertEquals([$path], $this->getDefaultMigrationPaths());
        $this->assertEquals([$path], $this->getMigrationPaths());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::tablePrefix()
     * method.
     *
     * @test
     */
    public function testGetTablePrefixMethod()
    {
        $app = new Container();
        $app->instance('config', m::mock('Illuminate\Contracts\Config\Repository'));

        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('config')->with('user.prefix', 'user')->andReturn('user');

        $this->driver = 'user';
        $this->manager = $manager;

        $this->assertEquals('user_{id}', $this->tablePrefix());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\Operation::tablePrefix()
     * method.
     *
     * @test
     */
    public function testGetTablePrefixMethodWithDifferentPrefix()
    {
        $app = new Container();
        $app->instance('config', m::mock('Illuminate\Contracts\Config\Repository'));
        $manager = m::mock('Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('config')->with('user.prefix', 'user')->andReturn('member');

        $this->driver = 'user';
        $this->manager = $manager;

        $this->assertEquals('member_{id}', $this->tablePrefix());
    }
}
