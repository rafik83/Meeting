<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Domain\View\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeCheckboxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews']);
        $resolver->setAllowedTypes('typeViews', 'array');
        $resolver->setDefaults([
            'choice_label' => function (TypeView $typeView) {
                return $typeView->title;
            },
            'choices' => function (Options $options) {
                return $options['typeViews'];
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
