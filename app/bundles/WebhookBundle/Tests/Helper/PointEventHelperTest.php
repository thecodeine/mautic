<?php

namespace Mautic\WebhookBundle\Tests;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PointBundle\Entity\Trigger;
use Mautic\PointBundle\Entity\TriggerEvent;
use Mautic\PointBundle\Model\TriggerEventModel;
use Mautic\PointBundle\Model\TriggerModel;
use Mautic\WebhookBundle\Helper\PointEventHelper;
use Mautic\WebhookBundle\Model\Webhook;
use Mautic\WebhookBundle\Model\WebhookTriggerQueueModel;

class PointEventHelperTest extends \PHPUnit_Framework_TestCase
{
    private $processedWebhooks = [];
    private $savedWebhooks     = [];
    private $siteUrl = 'http://test.com';

    public function testTriggerWebhook()
    {
        $iterations = 4;
        $helper     = new PointEventHelper();
        $event      = [
            'id'      => 1,
            'trigger' => [
                'id' => 1,
            ],
            'properties' => [
                'webhookUrl' => 'webhookUrl',
            ],
        ];

        $expectedEmail = 'test@test.com';
        $expectedPoints = 10;

        $expectedPayload = [
            'lead'         => null,
            'email'        => $expectedEmail,
            'points'       => $expectedPoints,
            'instance_url' => $this->siteUrl,
        ];

        $lead = new Lead();
        $lead->setEmail($expectedEmail);
        $lead->setPoints($expectedPoints);

        $commandProcessFactory = $this->getMockMauticFactory('command_process');
        $immediateProcessFactory = $this->getMockMauticFactory('immediate_process');

        for ($i = 0; $i < $iterations; $i++) {
            $this->triggerAndTestWebhookHelper($helper, $event, $lead, $commandProcessFactory, true);
        }

        $this->assertEquals($iterations, sizeof($this->savedWebhooks));
        $this->assertEquals(0, sizeof($this->processedWebhooks));

        for ($i = 0; $i < $iterations; $i++) {
            $this->triggerAndTestWebhookHelper($helper, $event, $lead, $immediateProcessFactory, true);
        }

        $this->assertEquals($iterations, sizeof($this->savedWebhooks));
        $this->assertEquals($iterations, sizeof($this->processedWebhooks));

        for ($i = 0; $i < $iterations; $i++) {
            $this->assertEquals($expectedPayload, $this->processedWebhooks[$i]->getPayload());
            $this->assertEquals($expectedPayload, $this->savedWebhooks[$i]->getPayload());
        }
    }

    private function triggerAndTestWebhookHelper($helper, $event, $lead, $factory, $expected)
    {
        $result = $helper->triggerWebhook($event, $lead, $factory);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $queueMode
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMauticFactory($queueMode)
    {
        $mock = $this->getMockBuilder(MauticFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModel'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getModel')
            ->willReturnCallback(function ($model) use ($queueMode) {
                switch ($model) {
                    case 'webhook.webhook_trigger_queue':
                        return $this->getMockWebhookQueueModel($queueMode);
                    case 'webhook':
                        return $this->getMockWebhookModel();
                    case 'point.trigger':
                        return $this->getMockTrigger(); //its different model, but we need only mocked 'getEntity' method
                    case 'point.triggerevent':
                        return $this->getMockTriggerEvent(); //its different model, but we need only mocked 'getEntity' method
                }
            });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWebhookQueueModel($queueMode)
    {
        $mock = $this->getMockBuilder(WebhookTriggerQueueModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueueMode', 'saveEntity', 'getSiteUrl'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getQueueMode')
            ->will($this->returnValue($queueMode));

        $mock->expects($this->any())
            ->method('saveEntity')
            ->willReturnCallback(function ($webhook) {
                $this->savedWebhooks[] = $webhook;
            });

        $mock->expects($this->any())
            ->method('getSiteUrl')
            ->will($this->returnValue($this->siteUrl));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockWebhookModel()
    {
        $mock = $this->getMockBuilder(Webhook::class)
            ->disableOriginalConstructor()
            ->setMethods(['processWebhook', 'serializeData'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('processWebhook')
            ->willReturnCallback(function ($webhook) {
                $this->processedWebhooks[] = $webhook;

                return true;
            });

        $mock->expects($this->any())
            ->method('serializeData')
            ->willReturnCallback(function ($payload) {
                return $payload;
            });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTrigger()
    {
        $mock = $this->getMockBuilder(TriggerModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntity'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getEntity')
            ->willReturnCallback(function ($id) {
                return new Trigger();
            });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTriggerEvent()
    {
        $mock = $this->getMockBuilder(TriggerEventModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntity'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getEntity')
            ->willReturnCallback(function ($id) {
                return new TriggerEvent();
            });

        return $mock;
    }
}
