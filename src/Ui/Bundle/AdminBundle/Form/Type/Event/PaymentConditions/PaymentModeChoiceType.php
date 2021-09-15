<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;

use Proximum\Vimeet\Domain\Payment\Mode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentModeChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $labelKeyProvider = function ($value) {
            return sprintf('form.payment_mode_choice.children.paymentMode.%s', $value ?? 'none');
        };

        $builder
            ->add('onlinePayment', ChoiceType::class, [
                'required' => true,
                'expanded' => true,
                'label' => 'form.payment_mode_choice.children.paymentMode.onlinePayment',
                'choices' => array_merge(['none' => null], Mode::getOnlinePaymentModes()),
                'choice_label' => $labelKeyProvider,
            ])
            ->add('offlinePayments', ChoiceType::class, [
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'label' => 'form.payment_mode_choice.children.paymentMode.offlinePayments',
                'choices' => Mode::getOfflinePaymentModes(),
                'choice_label' => $labelKeyProvider,
            ]);

            $builder
                ->addModelTransformer(new CallbackTransformer(
                    function ($flatenPaymentModes) {
                        return array_reduce($flatenPaymentModes, function ($carry, $paymentMode) {
                            if (\in_array($paymentMode, Mode::getOnlinePaymentModes())) {
                                $carry['onlinePayment'] = $paymentMode;
                            } else {
                                $carry['offlinePayments'][] = $paymentMode;
                            }

                            return $carry;
                        }, ['onlinePayment' => null, 'offlinePayments' => []]);
                    },
                    function ($splittedPaymentModes) {
                        $flatenPaymentModes = [];
                        if ($splittedPaymentModes['onlinePayment']) {
                            $flatenPaymentModes[] = $splittedPaymentModes['onlinePayment'];
                        }

                        return array_values(array_merge($flatenPaymentModes, $splittedPaymentModes['offlinePayments']));
                    }
                ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'payment_mode_choice';
    }
}
