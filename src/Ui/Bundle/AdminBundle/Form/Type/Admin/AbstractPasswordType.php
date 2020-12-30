<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'validators.password.mismatch',
            ])
        ;
    }
}
