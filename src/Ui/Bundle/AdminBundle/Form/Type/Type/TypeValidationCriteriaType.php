<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class TypeValidationCriteriaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sheetAccepted', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }
}
