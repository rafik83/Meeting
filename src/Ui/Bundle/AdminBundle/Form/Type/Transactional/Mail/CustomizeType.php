<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail;


use Proximum\Vimeet\Application\Command\Transactional\Mail\Customize;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomizeType extends CustomizeAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'transactional_mail_customize';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Customize::class
        ]);
    }
}
