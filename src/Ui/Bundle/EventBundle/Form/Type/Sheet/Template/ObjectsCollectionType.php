<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectsCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['block', 'locale'])
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('block', Block::class)
            ->setDefaults(
                [
                    'entry_type' => BlockObjectsCollectionType::class,
                    'entry_options' => function (Options $options) {
                        return [
                            'block' => $options['block'],
                            'locale' => $options['locale'],
                            'label' => false,
                        ];
                    },
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                    'max' => 10,
                ]
            )
        ;
    }

    public function getParent()
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_template_objects_collection_type';
    }
}
