<?php

namespace Mautic\WebhookBundle\Model;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Model\FormModel as CommonFormModel;
use Mautic\WebhookBundle\Entity\WebhookTriggerQueue;

class WebhookTriggerQueueModel extends CommonFormModel
{
    /**
     * @var string
     */
    protected $queueMode;

    /**
     * @var string
     */
    protected $siteUrl;

    /**
     * WebhookModel constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->queueMode = $coreParametersHelper->getParameter('queue_mode');
        $this->siteUrl   = $coreParametersHelper->getParameter('site_url');
    }

    public function getQueueMode()
    {
        return $this->queueMode;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getRepository()
    {
        return $this->em->getRepository(WebhookTriggerQueue::class);
    }
}
