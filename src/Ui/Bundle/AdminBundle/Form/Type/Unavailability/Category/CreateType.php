<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category;

use Proximum\Vimeet\Application\Command\Unavailability\Category\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends CategoryType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'unavailability_category_create';
    }
}
