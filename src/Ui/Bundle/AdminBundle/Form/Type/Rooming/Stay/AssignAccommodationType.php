<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('arrival', DateType::class)
            ->add('departure', DateType::class)
            ->add('accommodation', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'title',
            ])
            ->add('roomType', ChoiceType::class, [
                'choices' => [
                    Stay::ROOM_TYPE_SINGLE => Stay::ROOM_TYPE_SINGLE,
                    Stay::ROOM_TYPE_DOUBLE => Stay::ROOM_TYPE_DOUBLE,
                ],
                'expanded' => true,
            ])
            ->add('roommate', ChoiceType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssignAccommodation::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'admin_assign_accommodation_type';
    }
}
