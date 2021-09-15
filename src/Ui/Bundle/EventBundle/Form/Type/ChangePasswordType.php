<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type;

use Proximum\Vimeet\Application\Command\User\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'validators.password.mismatch',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'change_password_user';
    }
}
