<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgottenPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'required' => true,
            'attr'     => [
                'placeholder' => 'form.email.placeholder',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ForgottenPassword::class,
        ]);
    }
}
