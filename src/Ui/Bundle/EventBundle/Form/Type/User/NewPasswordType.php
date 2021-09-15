<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\NewPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractPasswordType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => NewPassword::class,
        ]);
    }
}
