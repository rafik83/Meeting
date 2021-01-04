<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Proximum\Vimeet\Application\Command\Tip\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends TipType
{
    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_update';
    }
}
