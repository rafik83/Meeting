<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Resources\Icon\StyleGuideIcon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StyleGuideIconType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => StyleGuideIcon::LIST,
                'expanded' => true,
            ]
        );
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
