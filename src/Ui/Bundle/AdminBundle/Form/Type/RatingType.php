<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RatingType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices'     => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
                'expanded'    => true,
                'required'    => false,
                'attr' => ['class' => 'rating-group'],
                'choice_attr' => static function () {
                    return ['class' => 'rating__input'];
                },
            ]
        );
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
