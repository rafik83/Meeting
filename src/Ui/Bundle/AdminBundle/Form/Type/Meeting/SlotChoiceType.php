<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['slots', 'timeZone', 'locale'])
            ->setAllowedTypes('slots', 'array')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('timeZone', 'string')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hourFormatter = \IntlDateFormatter::create(
            $options['locale'],
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $options['timeZone']
        );

        $dayFormatter = \IntlDateFormatter::create(
            $options['locale'],
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $options['timeZone']
        );

        $builder
            ->add('slot', ChoiceType::class, [
                'choices' => $this->getFormattedSlots($dayFormatter, $options['slots']),
                'choice_label' => function (MeetingSlot $meetingSlot) use ($hourFormatter) {
                    return sprintf(
                        '%s - %s',
                        $hourFormatter->format($meetingSlot->getBegin()),
                        $hourFormatter->format($meetingSlot->getEnd())
                    );
                },
                'required' => true,
            ])
        ;
    }

    /**
     * @param \IntlDateFormatter $dayFormatter
     * @param MeetingSlot[]      $slots
     *
     * @return array
     */
    private function getFormattedSlots(\IntlDateFormatter $dayFormatter, array $slots): array
    {
        $formattedSlots = [];

        foreach ($slots as $slot) {
            $formattedSlots[$dayFormatter->format($slot->getBegin())][$slot->getId()] = $slot;
        }

        return $formattedSlots;
    }
}
