<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message;

use Proximum\Vimeet\Application\Command\Messaging\Message\Update as UpdateMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UpdateType extends AbstractMessageType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', UpdateMessage::class);
    }
}
