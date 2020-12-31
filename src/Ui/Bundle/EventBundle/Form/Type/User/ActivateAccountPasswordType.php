<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivateAccountPasswordType extends AbstractPasswordType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ActivateAccountPassword::class,
        ]);
    }
}
