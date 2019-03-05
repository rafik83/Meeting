<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Move;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveMeetingSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('slotId', ChoiceType::class, [
                'choices' => $options['availableSlotIds'],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'availableSlotIds',
            ])
            ->setDefaults([
                'data_class' => Move::class,
            ])
        ;
    }
}
