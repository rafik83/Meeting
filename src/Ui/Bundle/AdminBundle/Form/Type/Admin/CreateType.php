<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Proximum\Vimeet\Application\Command\Admin\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AdminType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
            'password_required' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'create_admin';
    }
}
