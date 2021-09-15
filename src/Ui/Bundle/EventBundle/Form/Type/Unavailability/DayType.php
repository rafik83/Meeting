<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('locale');
        $resolver->setRequired('formatter');
        $resolver->setRequired('days');

        $resolver->setDefaults([
            'required' => true,
            'choices' => static function (Options $options) {
                return $options['days'];
            },
            'choice_label' => static function (Options $options) {
                return static function ($day) use ($options) {
                    return $options['formatter']->format($day->getBegin());
                };
            },
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'unavailability_day';
    }
}
