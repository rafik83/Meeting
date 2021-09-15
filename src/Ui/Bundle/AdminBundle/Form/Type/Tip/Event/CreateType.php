<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Application\Command\Tip\Event\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractEventTipType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'tip_event_create';
    }
}
