<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateSpeakerType extends AbstractSpeakerType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
            'submit'     => true,
        ]);
    }
}
