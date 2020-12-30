<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Picto\CategoryPictoType;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class CategoryType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('picto', CategoryPictoType::class, [
                'required' => true,
            ])
            ->add('leftColor', TextType::class, [
                'required' => true,
            ])
            ->add('rightColor', TextType::class, [
                'required' => true,
            ])
        ;
    }
}
