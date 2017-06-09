<?php

namespace Mautic\WebhookBundle\Command;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\WebhookBundle\Entity\Webhook;
use Mautic\WebhookBundle\Entity\WebhookTriggerQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to process queued webhook payloads.
 */
class ProcessWebhookTriggerQueuesCommand extends Command
{
    /**
     * @var \Mautic\CoreBundle\Factory\MauticFactory
     */
    protected $factory;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em, MauticFactory $factory)
    {
        $this->em      = $em;
        $this->factory = $factory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mautic:webhooks:trigger:proccess')
            ->setDescription('Process queued points trigger webhooks');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->proccessQueuedWebhooks($output);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param $model
     *
     * @return \Mautic\CoreBundle\Model\AbstractCommonModel
     */
    protected function getModel($model)
    {
        return $this->factory->getModel($model);
    }

    /**
     * @return array
     */
    protected function getAllWebhooks()
    {
        return $this->em->getRepository(WebhookTriggerQueue::class)->findAll();
    }

    /**
     * @param $queuedWebhook
     *
     * @return null|string
     */
    protected function getWebhookUrl($queuedWebhook)
    {
        $properties = $queuedWebhook->getEvent()->getProperties();

        return isset($properties['webhookUrl']) ? $properties['webhookUrl'] : null;
    }

    /**
     * @param OutputInterface $output
     *
     * @return mixed
     */
    public function proccessQueuedWebhooks(OutputInterface $output)
    {
        $webhookModel      = $this->getModel('webhook');
        $webhookQueueModel = $this->getModel('webhook.webhook_trigger_queue');

        // check to make sure we are in queue mode
        if ($webhookQueueModel->getQueueMode() != 'command_process') {
            return $output->writeLn('Point Bundle is in immediate process mode. To use the command function change to command mode.');
        }

        $queuedWebhooks = $this->getAllWebhooks();

        if (!count($queuedWebhooks)) {
            return $output->writeln('<error>No published webhooks found. Try again later.</error>');
        }

        $output->writeLn('<info>Processing Webhooks</info>');

        foreach ($queuedWebhooks as $queuedWebhook) {
            $webhookUrl = $this->getWebhookUrl($queuedWebhook);

            if (!$webhookUrl) {
                continue;
            }

            $webhook = new Webhook();
            $webhook->setWebhookUrl($webhookUrl);
            $webhook->setPayload($queuedWebhook->getPayload());

            try {
                $webhookModel->processWebhook($webhook);
                $this->em->remove($queuedWebhook);
            } catch (\Exception $e) {
                $output->writeLn('<error>'.$e->getMessage().'</error>');
            }
        }

        $this->em->flush();

        $output->writeLn('<info>Webhook Processing Complete</info>');
    }
}
