<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TermsOfSaleTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', TextareaType::class, [
                'label'    => false,
                'required' => true,
                'attr'     => [
                    'rows' => 15,
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'terms_of_sale_translation';
    }
}
