<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant;

use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\CountryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddFastCheckinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'type',
                TypeChoiceType::class,
                [
                    'event' => $options['event'],
                    'locale' => $options['locale'],
                    'user' => $options['user'],
                ]
            )
            ->add(
                'sheetTitle',
                TextType::class,
                ['required' => false,]
            )
            ->add(
                'firstname',
                TextType::class,
                ['required' => false,]
            )
            ->add(
                'lastname',
                TextType::class,
                ['required' => false,]
            )
            ->add(
                'mobile',
                TextType::class,
                ['required' => false,]
            )
            ->add(
                'country',
                CountryChoiceType::class,
                [
                    'event' => $options['event'],
                    'locale' => $options['locale'],
                    'required' => false,
                ]
            )
            ->add(
                'hasAccessToMeetings',
                CheckboxType::class,
                ['required' => false]
            )
            ->add(
                'submit',
                SubmitType::class
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setDefaults(['data_class' => AddFastCheckin::class]);
    }
}
