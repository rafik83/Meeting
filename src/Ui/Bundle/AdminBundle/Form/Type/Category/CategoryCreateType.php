<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Category;

use Proximum\Vimeet\Application\Command\Category\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryCreateType extends AbstractCategoryType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }
}
