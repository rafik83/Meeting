<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator;

use Proximum\Vimeet\Application\Command\Operator\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractOperatorType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'create_operator';
    }
}
