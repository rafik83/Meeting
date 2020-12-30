<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterPartType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'label'       => false,
                'placeholder' => 'form.user_filter.children.text_search.label',
                'required'    => false,
            ])
            ->add('type', HiddenType::class)
            ->add('participation', HiddenType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_filter_part';
    }
}
