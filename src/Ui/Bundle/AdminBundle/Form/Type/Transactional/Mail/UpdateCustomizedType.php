<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail;

use Proximum\Vimeet\Application\Command\Transactional\Mail\UpdateCustomized;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateCustomizedType extends CustomizeAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'transactional_mail_update_customized';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => UpdateCustomized::class
        ]);
    }
}
