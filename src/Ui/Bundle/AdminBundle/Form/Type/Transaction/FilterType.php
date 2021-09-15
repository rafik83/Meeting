<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction;

use Proximum\Vimeet\Application\Command\Transaction\Filter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $firstDay = new \DateTime();
        $firstDay->modify('first day of last month 00:00:00');

        $lastDay = new \DateTime();
        $lastDay->modify('last day of last month 23:59:59');

        $builder
            ->add('beginDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd/MM/yyyy',
                'data'        => $firstDay,
                'placeholder' => 'form.transaction_find.children.date.placeholder',
            ])
            ->add('endDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd/MM/yyyy',
                'data'        => $lastDay,
                'placeholder' => 'form.transaction_find.children.date.placeholder',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'transaction_find';
    }
}
