<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;


use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iban', TextType::class, [
                'required' => false,
            ])
            ->add('billingAddress', TextType::class, [
                'required' => true,
            ])
            ->add('paymentCondition', TextType::class, [
                'required' => false,
            ])
            ->add('footers', TextType::class, [
                'required'    => false,
                'placeholder' => 'form.event_billing_configuration.children.footers.placeholder',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingConfiguration::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_billing_configuration';
    }
}
