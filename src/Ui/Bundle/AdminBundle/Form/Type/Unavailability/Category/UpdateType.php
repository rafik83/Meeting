<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category;

use Proximum\Vimeet\Application\Command\Unavailability\Category\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends CategoryType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'unavailability_category_update';
    }
}
