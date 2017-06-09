<?php

namespace Mautic\WebhookBundle\Tests;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PointBundle\Entity\Trigger;
use Mautic\PointBundle\Entity\TriggerEvent;
use Mautic\WebhookBundle\Command\ProcessWebhookTriggerQueuesCommand;
use Mautic\WebhookBundle\Entity\WebhookTriggerQueue;
use Mautic\WebhookBundle\Model\Webhook;
use Mautic\WebhookBundle\Model\WebhookTriggerQueueModel;
use Symfony\Component\Console\Output\NullOutput;

class ProcessWebhookTriggerQueuesCommandTest extends \PHPUnit_Framework_TestCase
{
    private $processedWebhooks = [];

    public function testCommand()
    {
        $expected = 10;
        $command  = $this->getMockCommand($expected);
        $command->proccessQueuedWebhooks(new NullOutput());

        $this->assertEquals($expected, sizeof($this->processedWebhooks));
    }

    /**
     * @param int $amount
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockCommand($amount = 2)
    {
        $webhookTriggerQueue = new WebhookTriggerQueue();
        $webhookTriggerQueue->setEvent(new TriggerEvent());
        $webhookTriggerQueue->setTrigger(new Trigger());
        $webhookTriggerQueue->setPayload([1 => 'someData']);

        $models = [];

        for ($i = 0; $i < $amount; ++$i) {
            $models[] = $webhookTriggerQueue;
        }

        $mock = $this->getMockBuilder(ProcessWebhookTriggerQueuesCommand::class)
            ->setConstructorArgs([
                $this->getMockEntityManager(),
                $this->getMockMauticFactory(),

            ])
            ->setMethods(['getModel', 'getAllWebhooks', 'getWebhookUrl', 'getEntityManager'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getModel')
            ->willReturnCallback(function ($model) {
                switch ($model) {
                    case 'webhook.webhook_trigger_queue':
                        return $this->getMockWebhookQueueModel();
                    case 'webhook':
                        return $this->getMockWebhookModel();
                }
            });

        $mock->expects($this->any())
            ->method('getAllWebhooks')
            ->will($this->returnValue($models));

        $mock->expects($this->any())
            ->method('getWebhookUrl')
            ->will($this->returnValue('http://www.test.com'));

        $mock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->getMockEntityManager()));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWebhookQueueModel()
    {
        $mock = $this->getMockBuilder(WebhookTriggerQueueModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueueMode'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getQueueMode')
            ->will($this->returnValue('command_process'));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWebhookModel()
    {
        $mock = $this->getMockBuilder(Webhook::class)
            ->disableOriginalConstructor()
            ->setMethods(['processWebhook'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('processWebhook')
            ->willReturnCallback(function ($webhook) {
                $this->processedWebhooks[] = $webhook;
            });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEntityManager()
    {
        $mock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove', 'flush'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('remove')
            ->will($this->returnValue(null));

        $mock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMauticFactory()
    {
        $mock = $this->getMockBuilder(MauticFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;

        return $mock;
    }
}
