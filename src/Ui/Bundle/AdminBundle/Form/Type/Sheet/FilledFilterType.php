<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilledFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $filledFilters = $options['filledFilters'];

        foreach ($filledFilters as $key => $filterLabel) {
            $builder->add($key, FilledFilterChoiceType::class, [
                'label' => $filterLabel,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['filledFilters']);
    }
}
