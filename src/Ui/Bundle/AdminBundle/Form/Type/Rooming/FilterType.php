<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\StateChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'types',
                TypeChoiceType::class,
                [
                    'label'       => 'form.filter_operator.rooming.types',
                    'multiple'    => true,
                    'select2'     => true,
                    'placeholder' => '',
                    'locale'      => $options['locale'],
                    'user'        => $options['admin'],
                    'event'       => $options['event'],
                    'orderByTitle' => true,
                ]
            )
            ->add(
                'states', StateChoiceType::class, [
                    'label'    => 'form.filter_operator.rooming.states',
                    'multiple' => true,
                    'expanded' => true,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'locale',
                'admin',
                'event',
            ]
        );
        $resolver->addAllowedTypes('admin', Admin::class);
        $resolver->addAllowedTypes('locale', 'string');
        $resolver->addAllowedTypes('event', Event::class);
        $resolver->setDefaults(
            [
                'required' => false,
                'method' => 'GET',
                'csrf_protection' => false,
            ]
        );
    }
}
