<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class WebhookTriggerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'webhookUrl',
            'url',
            [
                'label'       => 'mautic.webhook.form.webhook_url',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'constraints' => [
                    new Url(
                        ['message' => 'mautic.core.valid_url_required']
                    ),
                    new NotBlank(
                        ['message' => 'mautic.core.valid_url_required']
                    ),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'webhook_trigger';
    }
}
