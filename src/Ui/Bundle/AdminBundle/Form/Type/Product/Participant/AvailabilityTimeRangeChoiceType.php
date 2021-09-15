<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityTimeRangeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['event', 'locale']);

        $resolver
            ->setDefaults([
                'select2'      => true,
                'availabilityTimeRangeDateFormatter' => function (Options $options) {
                    return new \IntlDateFormatter(
                        $options['locale'],
                        \IntlDateFormatter::SHORT,
                        \IntlDateFormatter::SHORT,
                        $options['event']->getTimeZone()
                    );
                },
                'choice_label' => function (Options $options) {
                    $formatter = $options['availabilityTimeRangeDateFormatter'];

                    return function (AvailabilityTimeRange $availabilityTimeRange = null) use ($formatter) {
                        if (null === $availabilityTimeRange) {
                            return '';
                        }

                        return sprintf(
                            '%s (%s - %s)',
                            $availabilityTimeRange->getName(),
                            $formatter->format($availabilityTimeRange->getBegin()),
                            $formatter->format($availabilityTimeRange->getEnd())
                        );
                    };
                },
            ])
        ;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
