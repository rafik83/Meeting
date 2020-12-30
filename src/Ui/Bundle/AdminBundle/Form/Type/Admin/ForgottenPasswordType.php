<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Proximum\Vimeet\Application\Command\Admin\ForgottenPassword;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\AbstractEmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgottenPasswordType extends AbstractEmailType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ForgottenPassword::class,
        ]);
    }
}
