<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\LinkedSheets;

use Proximum\Vimeet\Domain\View\SheetView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheetViews']);
        $resolver->setAllowedTypes('sheetViews', 'array');
        $resolver->setDefaults([
            'choice_label' => function (SheetView $sheetView) {
                return $sheetView->typeTitle;
            },
            'choices' => function (Options $options) {
                return $options['sheetViews'];
            },
            'multiple' => true,
            'select2'  => true,
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
