<?php

namespace Mautic\WebhookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\PointBundle\Entity\Trigger;
use Mautic\PointBundle\Entity\TriggerEvent;

class WebhookTriggerQueue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Trigger
     */
    private $trigger;

    /**
     * @var \DateTime
     */
    private $dateAdded;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var TriggerEvent
     **/
    private $event;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('webhook_trigger_queue')
            ->setCustomRepositoryClass('Mautic\WebhookBundle\Entity\WebhookTriggerQueueRepository');

        $builder->addId();

        $builder->createManyToOne('trigger', 'Mautic\PointBundle\Entity\Trigger')
            ->addJoinColumn('trigger_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->createField('dateAdded', 'datetime')
            ->columnName('date_added')
            ->nullable()
            ->build();

        $builder->createField('payload', 'text')
            ->columnName('payload')
            ->build();

        $builder->createManyToOne('event', 'Mautic\PointBundle\Entity\TriggerEvent')
            ->addJoinColumn('event_id', 'id', false, false, 'CASCADE')
            ->build();
    }
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return Trigger
     */
    public function getTrigger()
    {
        return $this->trigger;
    }
    /**
     * @param Trigger $trigger
     *
     * @return TriggerEventQueue
     */
    public function setTrigger(Trigger $trigger)
    {
        $this->trigger = $trigger;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }
    /**
     * @param mixed $dateAdded
     *
     * @return TriggerEventQueue
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * @param mixed $payload
     *
     * @return TriggerEventQueue
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }
    /**
     * @return TriggerEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * @param TriggerEvent $event
     *
     * @return TriggerEventQueue
     */
    public function setEvent(TriggerEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
