<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepositType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'attr'     => [
                'min' => 0,
                'max' => 100,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return IntegerType::class;
    }
}
