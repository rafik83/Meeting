<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityConfirmationStatusChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'form.sheet_filter.children.availabilityConfirmationStatus.choice.all_confirmed' => ConfirmationStatus::ALL_CONFIRMED,
                'form.sheet_filter.children.availabilityConfirmationStatus.choice.partly_confirmed' => ConfirmationStatus::AT_LEAST_ONE_CONFIRMED,
                'form.sheet_filter.children.availabilityConfirmationStatus.choice.none_confirmed' => ConfirmationStatus::NONE_CONFIRMED,
            ],
            'placeholder' => 'form.sheet_filter.children.availabilityConfirmationStatus.choice.all',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
