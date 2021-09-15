<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice type that grants choices amongst pre-defined creation time intervals : "today", "this week", etc.
 */
class CreationIntervalFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'admin.sheet.created_today'     => Constant::CREATED_TODAY,
                'admin.sheet.created_this_week' => Constant::CREATED_THIS_WEEK,
            ],
            'placeholder' => 'admin.sheet.registeredAt.all',
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
