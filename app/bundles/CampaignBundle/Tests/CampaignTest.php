<?php

namespace Mautic\CampaignBundle\Tests;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Entity\EventDailySendLog;
use Mautic\CampaignBundle\Entity\LeadEventLog;
use Mautic\CoreBundle\Test\MauticWebTestCase;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\CampaignBundle\Entity\Lead as CampaignLead;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\ListLead;

class CampaignTest extends MauticWebTestCase
{
    public function testQueuedNegativeEvents()
    {
        $this->loadData();

        $this->executeCommand('mautic:campaign:trigger');

        $this->travelInTime();

        $this->executeCommand('mautic:campaign:trigger');

        $this->assertCount(40, $this->em->getRepository(LeadEventLog::class)->findAll());
    }

    private function travelInTime($period = 'P2D')
    {
        //mote two days ahead
        $campainLeadEventLogs = $this->em->getRepository(LeadEventLog::class)->findAll();
        $date                 = new \DateTime();
        $date->sub(new \DateInterval($period));

        foreach ($campainLeadEventLogs as $campainLeadEventLog) {

            $campainLeadEventLog->setDateTriggered($date);
            $this->em->persist($campainLeadEventLog);
            $this->em->flush();
        }

        $campaignEventDailySendLogs = $this->em->getRepository(EventDailySendLog::class)->findAll();

        foreach($campaignEventDailySendLogs as $campaignEventDailySendLog) {
            $date = new \DateTime();
            $date->sub(new \DateInterval($period));
            $campaignEventDailySendLog->setDate($date);
            $this->em->persist($campaignEventDailySendLog);
            $this->em->flush();
        }
    }

    private function loadData()
    {
        $date = new \DateTime();

        $leadList = new LeadList();
        $leadList->setIsPublished(1);
        $leadList->setDateAdded($date);
        $leadList->setName('Anime');
        $leadList->setAlias('Anime');

        $this->em->persist($leadList);

        $campaign = new Campaign();

        $campaign->setIsPublished(true);
        $campaign->setName('test');
        $campaign->addList($leadList);

        $email = new Email();
        $email->setIsPublished(true);
        $email->setDateAdded($date);
        $email->setName('test email');
        $email->setTemplate('blank');
        $email->setEmailType('template');

        $this->em->persist($email);

        for ($i = 0; $i < 20; $i++) {
            $lead = new Lead();
            $lead->setFirstname('Firstname_' . $i);
            $lead->setLastname('Lastname_' . $i);
            $lead->setEmail($i . '_test@test.com');
            $lead->setPhone('555-666-777');

            $this->em->persist($lead);

            $listLead = new ListLead();
            $listLead->setList($leadList);
            $listLead->setLead($lead);
            $listLead->setDateAdded($date);
            $listLead->setManuallyAdded(false);

            $this->em->persist($listLead);

            $campaignLead = new CampaignLead();
            $campaignLead->setCampaign($campaign);
            $campaignLead->setLead($lead);
            $campaignLead->setDateAdded($date);
            $campaignLead->setManuallyAdded(false);
            $campaignLead->setManuallyRemoved(false);
            $campaignLead->setRotation(1);

            $this->em->persist($campaignLead);

        }

        $this->em->persist($campaign);

        $event = new Event();
        $event->setCampaign($campaign);
        $event->setName('Send Email');
        $event->setType('email.send');
        $event->setEventType('action');
        $event->setOrder(1);
        $event->setProperties([
            'daily_max_limit' => '10',
            'email' => '1',
            'email_type' => 'transactional',
            'priority' => 2,
            'attempts' => 3
        ]);
        $event->setTriggerInterval(1);
        $event->setTriggerIntervalUnit('d');
        $event->setTriggerMode('immediate');
        $event->setTempId('newd282fcc139867d6ce915d8d9787f1464f8d100ae');
        $event->setChannel('email');
        $event->setChannelId(1);

        $this->em->persist($event);

        $event1 = new Event();
        $event1->setCampaign($campaign);
        $event1->setParent($event);
        $event1->setName('Opens email');
        $event1->setType('email.open');
        $event1->setEventType('decision');
        $event1->setOrder(2);
        $event1->setProperties([]);
        $event1->setTriggerInterval(0);
        $event1->setTempId('newf0a432f68c88369f622c4526f6f5890b0bc620a6');

        $this->em->persist($event1);

        $event2 = new Event();
        $event2->setCampaign($campaign);
        $event2->setParent($event1);
        $event2->setDecisionPath('no');
        $event2->setName('Send Email');
        $event2->setType('email.send');
        $event2->setEventType('action');
        $event2->setOrder(3);
        $event2->setProperties([
            'daily_max_limit' => '10',
            'email' => '1',
            'email_type' => 'transactional',
            'priority' => 2,
            'attempts' => 3
        ]);
        $event2->setTriggerInterval(1);
        $event2->setTriggerIntervalUnit('d');
        $event2->setTriggerMode('interval');
        $event2->setTempId('newe700d7143b78ffa779dcefd66249932a7291685c');
        $event2->setChannel('email');
        $event2->setChannelId(1);

        $this->em->persist($event2);

        $this->em->flush();
    }
}
