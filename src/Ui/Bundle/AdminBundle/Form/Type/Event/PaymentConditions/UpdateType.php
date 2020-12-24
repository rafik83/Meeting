<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;

use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event */
        $event = $options['event'];

        $builder
            ->add('allowDeposit', CheckboxType::class, [
                'required' => false,
            ])
            ->add('depositUntil', DateTimePickerType::class, [
                'view_timezone' => $event->getTimeZone(),
                'required'      => false,
            ])
            ->add('minimumForDeposit', NumberType::class, [
                'required' => false,
            ])
            ->add('deposit', DepositType::class, [
                'required' => false,
            ])
            ->add('paymentModes', PaymentModeChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data-class' => Update::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'event_payment_conditions_update';
    }
}
