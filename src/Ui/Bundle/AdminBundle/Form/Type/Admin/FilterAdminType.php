<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label'   => false,
                'choices' => [
                    'form.filter_admin.role.all'         => null,
                    'form.filter_admin.role.organizer'   => Admin::ROLE_ORGANIZER,
                    'form.filter_admin.role.operator'    => Admin::ROLE_OPERATOR,
                    'form.filter_admin.role.super_admin' => Admin::ROLE_SUPER_ADMIN,
                    'form.filter_admin.role.partner'     => Admin::ROLE_PARTNER,
                    'form.filter_admin.role.host'        => Admin::ROLE_HOST,
                ],
            ])
            ->add('event', EventChoiceType::class, [
                'label'       => false,
                'required'    => false,
                'expanded'    => false,
                'multiple'    => false,
                'placeholder' => 'form.filter_admin.event.all',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.filter_admin.children.submit.label',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
