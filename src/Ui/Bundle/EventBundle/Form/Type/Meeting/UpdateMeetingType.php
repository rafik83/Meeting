<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Event\Update;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateMeetingType extends AbstractType
{
    private ParticipantInfoGuesser $guesser;
    private TranslatorInterface $translator;

    public function __construct(ParticipantInfoGuesser $guesser, TranslatorInterface $translator)
    {
        $this->guesser = $guesser;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $timezone = $options['timezone'];
        $locale = $options['locale'];

        if (count($options['participants']) > 1) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices' => $options['participants'],
                    'choice_label' => fn (Participant $participant) => $this->guesser->guessParticipantCompleteName($participant, $locale),
                    'expanded' => true,
                    'multiple' => true,
                ]);
        }

        $builder
            ->add('meetingSlot', ChoiceType::class, [
                'choices' => $options['availableSlots'],
                'choice_label' => function(MeetingSlot $meetingSlot) use ($timezone, $locale) {
                    $day = DayHelper::getFormatter($locale, $timezone)->format($meetingSlot->getBegin());
                    $begin = DayHelper::getHourFormatter($locale, $timezone)->format($meetingSlot->getBegin());
                    $end = DayHelper::getHourFormatter($locale, $timezone)->format($meetingSlot->getEnd());

                    return $this->translator->trans('form.move_meeting.children.meetingSlot.label.begin.end', [
                        '%day%' => $day,
                        '%begin%' => $begin,
                        '%end%' => $end,
                    ], 'forms');
                }
            ])
            ->add('content', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 300,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'availableSlots',
                'locale',
                'timezone',
            ])
            ->setDefaults([
                'data_class' => Update::class,
                'participants' => [],
            ])
        ;
    }
}
