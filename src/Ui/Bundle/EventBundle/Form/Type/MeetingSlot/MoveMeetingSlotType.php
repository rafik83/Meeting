<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Move;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveMeetingSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $timezone = $options['timezone'];

        $builder
            ->add('meetingSlot', ChoiceType::class, [
                'choices' => $options['availableSlots'],
                'choice_label' => function(MeetingSlot $meetingSlot) use ($timezone) {
                    return sprintf(
                        'De %s à %s',
                        $this->convertDateToInternationalFormat($meetingSlot->getBegin(), $timezone),
                        $this->convertDateToInternationalFormat($meetingSlot->getEnd(), $timezone)
                    );
                }
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    private function convertDateToInternationalFormat(\DateTimeInterface $dateTime, string $timezone): string
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTimezone(new \DateTimeZone($timezone))
            ->format('d/m/Y H:i');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'availableSlots',
                'timezone',
            ])
            ->setDefaults([
                'data_class' => Move::class,
            ])
        ;
    }
}
