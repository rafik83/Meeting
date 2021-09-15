<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\DataTransformer\RankedCollectionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new RankedCollectionTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setDefaults([
            'allow_add'      => true,
            'allow_delete'   => true,
            'entry_type'     => RankedType::class,
            'entry_options'  => function (Options $options) {
                return [
                    'rank_options' => [
                        'attr' => [
                            'data-rank' => 'groups',
                        ],
                    ],
                    'item_type'    => GroupType::class,
                    'item_options' => [
                        'label'  => false,
                        'event'  => $options['event'],
                        'locale' => $options['locale'],
                    ],
                    'error_bubbling' => false,
                    'error_mapping' => [
                        'labels'  => 'item.labels',
                        'options' => 'item.options',
                    ],
                ];
            },
            'attr'           => [
                'class'                    => 'package-options-groups',
                'data-sortable-collection' => 'groups',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_group_collection';
    }
}
