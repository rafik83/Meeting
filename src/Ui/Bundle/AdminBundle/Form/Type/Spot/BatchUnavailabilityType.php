<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Command\Spot\UnavailabilityBatch;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchUnavailabilityType extends AbstractType
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * BatchUnavailabilityType constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event        = $options['event'];
        $meetingSlots = $this->meetingSlotRepository->findByEvent($options['event']);

        $dateFormatter = IntlDateFormatter::create(
            $options['locale'],
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $timeFormatter = IntlDateFormatter::create(
            $options['locale'],
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            $event->getTimeZone()
        );

        $builder
            ->add('meetingSlots', ChoiceType::class, [
                'choices'      => $meetingSlots,
                'expanded'     => true,
                'multiple'     => true,
                'choice_label' => function (MeetingSlot $meetingSlot) use ($dateFormatter, $timeFormatter) {
                    if (null !== $meetingSlot) {
                        $label =
                            $this->getFormattedValue($dateFormatter, $meetingSlot->getBegin()) . ' ' .
                            $this->getFormattedValue($timeFormatter, $meetingSlot->getBegin()) . ' - ' .
                            $this->getFormattedValue($timeFormatter, $meetingSlot->getEnd());

                        return $label;
                    }

                    return '';
                },
            ]);
    }

    /**
     * @param IntlDateFormatter  $formatter
     * @param \DateTimeInterface $dateTime
     *
     * @return string
     */
    private function getFormattedValue(IntlDateFormatter $formatter, \DateTimeInterface $dateTime)
    {
        $formattedValue = $formatter->format($dateTime);

        return false !== $formattedValue ? $formattedValue : '';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => UnavailabilityBatch::class,
            'submit'     => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'spot_batch_unavailability';
    }
}
