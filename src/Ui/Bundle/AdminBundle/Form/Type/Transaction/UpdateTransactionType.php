<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction;

use Proximum\Vimeet\Application\Command\Transaction\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateTransactionType extends AbstractTransactionType
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
