<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Proximum\Vimeet\Application\Command\Tip\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends TipType
{
    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_create';
    }
}
