<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass;

use Proximum\Vimeet\Application\Command\Unavailability\Mass\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends MassType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'unavailability_mass_update';
    }
}
