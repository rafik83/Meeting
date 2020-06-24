<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AdminType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('password', TextType::class, [
                'required' => isset($options['password_required']) ? $options['password_required'] : true,
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
            ])
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'form.create_admin.role.organizer' => Admin::ROLE_ORGANIZER,
                    'form.create_admin.role.operator' => Admin::ROLE_OPERATOR,
                    'form.create_admin.role.super_admin' => Admin::ROLE_SUPER_ADMIN,
                    'form.create_admin.role.host' => Admin::ROLE_HOST,
                ],
                'required' => true,
            ])
            ->add('events', EventChoiceType::class, [
                'required'    => false,
                'expanded'    => true,
                'multiple'    => true,
                'placeholder' => '',
            ])
        ;
    }
}
