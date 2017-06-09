<?php

namespace Mautic\WebhookBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PointBundle\Entity\Trigger;
use Mautic\WebhookBundle\Entity\Webhook;
use Mautic\WebhookBundle\Entity\WebhookTriggerQueue;

/**
 * Class PointEventHelper.
 */
class PointEventHelper
{
    /**
     * @param array         $event
     * @param Lead          $lead
     * @param MauticFactory $factory
     */
    public static function triggerWebhook(array $event, Lead $lead, MauticFactory $factory)
    {
        $webhookModel      = $factory->getModel('webhook');
        $webhookQueueModel = $factory->getModel('webhook.webhook_trigger_queue');

        $payload = [
            'lead'         => $lead->getId(),
            'email'        => $lead->getEmail(),
            'points'       => $lead->getPoints(),
            'instance_url' => $webhookQueueModel->getSiteUrl(),
        ];

        if ($webhookQueueModel->getQueueMode() == 'immediate_process') {
            $webhook = new Webhook();
            $webhook->setWebhookUrl($event['properties']['webhookUrl']);
            $webhook->setPayload($payload);

            return $webhookModel->processWebhook($webhook);
        }

        $triggerModel      = $factory->getModel('point.trigger');
        $triggerEventModel = $factory->getModel('point.triggerevent');

        $triggerId = $event['trigger'] instanceof Trigger ? $event['trigger']->getId() : $event['trigger']['id'];

        $trigger      = $triggerModel->getEntity($triggerId);
        $triggerEvent = $triggerEventModel->getEntity($event['id']);

        $webhookTriggerQueue = new WebhookTriggerQueue();
        $webhookTriggerQueue->setTrigger($trigger);
        $webhookTriggerQueue->setEvent($triggerEvent);
        $webhookTriggerQueue->setPayload($webhookModel->serializeData($payload));

        $webhookQueueModel->saveEntity($webhookTriggerQueue);

        return true;
    }
}
