<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Type::class,
            'attr'  => [
                'size' => '15',
            ],
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
