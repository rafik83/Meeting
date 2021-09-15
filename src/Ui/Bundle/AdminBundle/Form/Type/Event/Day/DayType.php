<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Day;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('startTime', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element',
                ],
            ])
            ->add('endTime', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
