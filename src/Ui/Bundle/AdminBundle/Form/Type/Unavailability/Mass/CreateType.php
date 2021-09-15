<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass;

use Proximum\Vimeet\Application\Command\Unavailability\Mass\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends MassType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'unavailability_mass_create';
    }
}
