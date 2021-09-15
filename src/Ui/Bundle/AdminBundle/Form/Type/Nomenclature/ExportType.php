<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ExportData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('charset', CharsetChoiceType::class, [
                'label' => 'form.nomenclature_export_type.children.charset.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method'          => 'GET',
            'data_class'      => ExportData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_export_type';
    }
}
