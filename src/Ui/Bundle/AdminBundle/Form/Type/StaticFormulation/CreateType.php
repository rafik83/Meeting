<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation;

use Proximum\Vimeet\Application\Command\StaticFormulation\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractStaticFormulationType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'static_formulation_create';
    }
}
