<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $testingDatabase = dirname(__DIR__).'/database/testing.sqlite';

        if (! file_exists($testingDatabase)) {
            touch($testingDatabase);
        }

        $app = parent::createApplication();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $testingDatabase);
        $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
