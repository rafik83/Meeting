<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order;

use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateRowType extends AbstractRowType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => UpdateRow::class,
        ]);
    }
}
