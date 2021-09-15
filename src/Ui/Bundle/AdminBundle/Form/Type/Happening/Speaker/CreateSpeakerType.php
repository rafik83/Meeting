<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSpeakerType extends AbstractSpeakerType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
            'submit'     => true,
        ]);
    }
}
