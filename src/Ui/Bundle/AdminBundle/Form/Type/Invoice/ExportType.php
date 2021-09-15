<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Invoice;

use Proximum\Vimeet\Application\Command\Invoice\Export;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $firstDay = (new \DateTime())->modify('first day of last month 00:00:00.000');

        $lastDay = (new \DateTime())->modify('last day of last month 23:59:59.999');

        $builder
            ->setAction($options['action'])
            ->add('beginDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd/MM/yyyy',
                'data'        => $firstDay,
                'placeholder' => 'form.invoice_export.children.date.placeholder',
            ])
            ->add('endDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd/MM/yyyy',
                'data'        => $lastDay,
                'placeholder' => 'form.invoice_export.children.date.placeholder',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => Export::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'invoice_export';
    }
}
