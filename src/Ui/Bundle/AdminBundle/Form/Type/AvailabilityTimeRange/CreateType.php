<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\AvailabilityTimeRange;

use Proximum\Vimeet\Application\Command\AvailabilityTimeRange\Create;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
               'required' => true,
            ])
            ->add('begin', DateTimePickerType::class, [
                'required'      => true,
                'view_timezone' => $options['timezone'],
            ])
            ->add('end', DateTimePickerType::class, [
                'required'      => true,
                'view_timezone' => $options['timezone'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['timezone']);
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'availability_time_range_create';
    }
}
