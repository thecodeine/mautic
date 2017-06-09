<?php

namespace Mautic\EmailBundle\Tests\EventListener;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Entity\EventDailySendLog;
use Mautic\CampaignBundle\Entity\Lead as CampaignLead;
use Mautic\CampaignBundle\Entity\LeadEventLog;
use Mautic\CoreBundle\Test\MauticWebTestCase;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\ListLead;

/**
 * Class CampaignSubscriberTest.
 */
class CampaignSubscriberTest extends MauticWebTestCase
{
    public function testOnCampaignTriggerWithoutLimitsAction()
    {
        $users = 5;
        $this->loadData(0, 0, $users);

        $this->executeCommand('mautic:campaign:trigger');

        $logs = $this->em->getRepository(LeadEventLog::class)->findBy([
            'isQueued' => 0,
        ]);

        $this->assertEquals(count($logs), $users);
    }

    public function testOnCampaignTriggerWithLimitsAction()
    {
        $dailyLimit = 2;
        $users      = 5;
        $this->loadData(0, $dailyLimit, $users);
        $this->executeCommand('mautic:campaign:trigger');

        $queuedLogs = $this->em->getRepository(LeadEventLog::class)->findBy([
            'isQueued' => 1,
        ]);

        $sendLogs = $this->em->getRepository(LeadEventLog::class)->findBy([
            'isQueued' => 0,
        ]);

        $this->assertEquals(count($queuedLogs), ($users - $dailyLimit));
        $this->assertEquals(count($sendLogs), $dailyLimit);
    }

    public function testOnCampaignTriggerQueuedAction()
    {
        $sendEmails = 3;
        $dailyLimit = 3;
        $users      = 3;

        $this->loadData($sendEmails, $dailyLimit, $users);
        $this->executeCommand('mautic:campaign:trigger');

        $logs = $this->em->getRepository(LeadEventLog::class)->findBy([
            'isQueued' => 1,
        ]);

        $this->assertEquals(count($logs), $users);
    }

    private function loadData($sendEmails = 0, $dailyMax = 0, $users = 20)
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

        for ($i = 0; $i < $users; ++$i) {
            $lead = new Lead();
            $lead->setFirstname('Firstname_'.$i);
            $lead->setLastname('Lastname_'.$i);
            $lead->setEmail($i.'_test@test.com');
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
            'daily_max_limit' => $dailyMax,
            'email'           => '1',
            'email_type'      => 'transactional',
            'priority'        => 2,
            'attempts'        => 3,
        ]);
        $event->setTriggerInterval(1);
        $event->setTriggerIntervalUnit('d');
        $event->setTriggerMode('immediate');
        $event->setTempId('newd282fcc139867d6ce915d8d9787f1464f8d100ae');
        $event->setChannel('email');
        $event->setChannelId(1);
        $this->em->persist($event);

        if ($sendEmails) {
            $dailyLog = new EventDailySendLog();
            $dailyLog->setSentCount($sendEmails);
            $dailyLog->setDate($date);
            $dailyLog->setEvent($event);

            $this->em->persist($dailyLog);
        }

        $this->em->flush();
    }
}
