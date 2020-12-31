<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeRangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = $options['days'];
        $timezone = $options['timezone'];

        if (!empty($days)) {
            $begins = array_map(static function (TimeRangeView $day) use ($timezone) {
                $clone = $day->getBegin();
                $clone->setTimezone(new \DateTimeZone($timezone));

                return $clone;
            }, $days);

            usort($begins, static function (\DateTime $one, \DateTime $another) {
                return (int) ($one->format('H')) - (int) ($another->format('H'));
            });

            $ends = array_map(static function (TimeRangeView $day) use ($timezone) {
                $clone = $day->getEnd();
                $clone->setTimezone(new \DateTimeZone($timezone));

                return $clone;
            }, $days);

            usort($ends, static function (\DateTime $one, \DateTime $another) {
                return  (int) ($another->format('H')) - (int) ($one->format('H'));
            });

            $beginHour = $begins[0]->format('H');
            $endHour   = $ends[0]->format('H');
            $hours     = range($beginHour, $endHour);

            $builder
                ->add('begin', TimeType::class, [
                    'hours' => $hours,
                ])
                ->add('end', TimeType::class, [
                    'hours' => $hours,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('days');
        $resolver->setRequired('timezone');
    }
}
