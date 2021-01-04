<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\PaymentConditions;

use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions\DepositType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions\PaymentModeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event */
        $event = $options['event'];

        $builder
            ->add('specificPaymentConditions', CheckboxType::class, [
                'required' => false,
            ])
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
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label' => false,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }

    public function getBlockPrefix(): string
    {
        return 'type_payment_conditions_update';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data-class' => Update::class,
        ]);
    }
}
