<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ImportData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, ['help' => 'form.nomenclature_import_type.children.file.help'])
            ->add('charset', CharsetChoiceType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ImportData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_import_type';
    }
}
