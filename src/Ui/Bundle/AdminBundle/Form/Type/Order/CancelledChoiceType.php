<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancelledChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'cancelled' => 'cancelled',
                'notCancelled' => 'notCancelled',
                'all' => 'all',
            ],
            'select2' => true,
            'label' => false,
            'choice_translation_domain' => 'messages',
            'choice_label' => function ($currentChoice) {
                return sprintf('event.sheet.order.cancelled.%s', $currentChoice);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
