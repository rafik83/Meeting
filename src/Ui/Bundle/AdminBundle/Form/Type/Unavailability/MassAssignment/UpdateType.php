<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\MassAssignment;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('begin', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'required'      => true,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('end', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'required'      => true,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('enabled', CheckboxType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'crsf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'unavaibility_mass_assignment_update';
    }
}
