<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', HiddenType::class)
            ->add('password', PasswordType::class, [
                'placeholder' => 'form.login.children.password.placeholder',
            ])
            ->add('rememberMe', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'login';
    }
}
