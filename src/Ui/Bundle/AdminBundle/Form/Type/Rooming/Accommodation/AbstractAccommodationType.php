<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('overnightCapacities', CollectionType::class, [
                'entry_type' => AccommodationOvernightCapacityType::class,
                'entry_options' => [
                    'firstDay' => $options['firstDay'],
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => false,
                'prototype' => true,
                'prototype_data' => new AccommodationOvernightCapacityView($options['firstDay'], 0),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['firstDay']);
    }

    public function getBlockPrefix(): string
    {
        return 'admin_accommodation_type';
    }
}
