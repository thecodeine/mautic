<?php

namespace Mautic\WebhookBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\PointBundle\Event\TriggerBuilderEvent;
use Mautic\PointBundle\PointEvents;

/**
 * Class PointSubscriber.
 */
class PointSubscriber extends CommonSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
        ];
    }

    /**
     * @param TriggerBuilderEvent $event
     */
    public function onTriggerBuild(TriggerBuilderEvent $event)
    {
        $sendEvent = [
            'group'    => 'mautic.webhook.actions',
            'label'    => 'mautic.webhook.point.action.webhook',
            'callback' => ['\\Mautic\\WebhookBundle\\Helper\\PointEventHelper', 'triggerWebhook'],
            'formType' => 'webhook_trigger',
        ];

        $event->addEvent('webhook.trigger', $sendEvent);
    }
}
