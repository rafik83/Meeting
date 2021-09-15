<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FormTemplateTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => false,
            ])
        ;
    }
}
