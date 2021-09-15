<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RankedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', $options['item_type'], array_merge(['label' => false], $options['item_options']))
            ->add('rank', $options['rank_type'], array_merge(['label' => false], $options['rank_options']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['item_type']);
        $resolver->setDefaults([
            'item_options' => [],
            'rank_type'    => HiddenType::class,
            'rank_options' => [],
        ]);
    }
}
