<?php

namespace Mautic\CoreBundle\Test;

abstract class MauticMysqlTestCase extends AbstractMauticTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createDatabase();
        $this->applyMigrations();
        $this->installDatabaseFixtures();
    }

    private function createDatabase()
    {
        $this->runCommand('doctrine:database:drop', [
            '--env'   => 'test',
            '--force' => true,
        ]);

        $this->runCommand('doctrine:database:create', [
            '--env' => 'test',
        ]);

        $this->runCommand('doctrine:schema:create', [
            '--env' => 'test',
        ]);
    }
}
