<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectsCollectionType extends AbstractType
{
    public const OBJECTS_COLLECTION = 'objectsCollection';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $block */
        $block = $options['block'];

        $builder
            ->add(self::OBJECTS_COLLECTION, CollectionType::class, [
                'entry_type' => BlockObjectsCollectionType::class,
                'entry_options' => [
                    'block' => $block,
                    'locale' => $block->getLocale(),
                    'locales' => $options['locales'],
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'max' => $block->getMaxItems(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['block', 'locale', 'locales'])
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('locales', 'array')
            ->setAllowedTypes('block', Block::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_template_objects_collection_type';
    }
}
