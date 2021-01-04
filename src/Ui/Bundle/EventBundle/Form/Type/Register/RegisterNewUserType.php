<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Register;

use Proximum\Vimeet\Application\Command\Register\RegisterNewUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterNewUserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'required'        => true,
                'type'            => PasswordType::class,
                'first_options'   => [
                    'label'       => false,
                    'placeholder' => 'form.register.newUser.password.placeholder',
                ],
                'second_options'  => [
                    'label'       => false,
                    'placeholder' => 'form.register.newUser.repeatedPassword.placeholder',
                ],
                'invalid_message' => 'validators.password.mismatch',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegisterNewUser::class,
        ]);
    }
}
