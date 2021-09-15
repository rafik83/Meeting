<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneValidationStatusChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'form.sheet_filter.children.phoneValidationStatus.choice.all_confirmed' => ValidationStatus::ALL_CONFIRMED,
                'form.sheet_filter.children.phoneValidationStatus.choice.partly_confirmed' => ValidationStatus::PARTLY_CONFIRMED,
                'form.sheet_filter.children.phoneValidationStatus.choice.none_confirmed' => ValidationStatus::NONE_CONFIRMED,
            ],
            'placeholder' => 'form.sheet_filter.children.phoneValidationStatus.choice.all',
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
