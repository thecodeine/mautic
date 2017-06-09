<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Test;

//@TODO - fix entity detachment issue that is leading to failed tests

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MauticWebTestCase extends AbstractMauticWebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->container->set('translator', $this->container->get('translator.default'));
    }

    /**
     * {@inheritdoc}
     */
    protected function setupDatabaseOnFirstRun()
    {
        $this->executeCommand('doctrine:database:drop', [
            '--env'   => 'test',
            '--force' => true,
        ]);

        $this->executeCommand('doctrine:database:create', [
            '--env' => 'test',
        ]);

        $this->executeCommand('doctrine:schema:create', [
            '--env' => 'test',
        ]);

        $this->installDatabaseFixtures();
    }

    private function installDatabaseFixtures()
    {
        $paths  = [dirname(__DIR__).'/../InstallBundle/InstallFixtures/ORM'];
        $loader = new ContainerAwareLoader($this->container);

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }

        $fixtures = $loader->getFixtures();

        if (!$fixtures) {
            throw new \InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($fixtures, true);
    }

    protected function executeCommand($name, array $params = [])
    {
        array_unshift($params, $name);

        $kernel      = $this->container->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($params);

        $output = new BufferedOutput();
        $application->run($input, $output);
    }
}
