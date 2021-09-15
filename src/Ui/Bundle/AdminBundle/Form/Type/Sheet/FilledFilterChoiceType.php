<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Sheet\FilledFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilledFilterChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'form.sheet_filter.children.filledFilter.choice.filled' => FilledFilter::FILLED,
                'form.sheet_filter.children.filledFilter.choice.not_filled' => FilledFilter::NOT_FILLED,
                'form.sheet_filter.children.filledFilter.choice.partly_filled' => FilledFilter::PARTLY_FILLED,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
