<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\ItemCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemCollectionDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type'    => ItemDataType::class,
                'entry_options' => [
                    'label'       => false,
                    'locale'      => $options['locale'],
                    'collection'  => $options['data'],
                    'placeholder' => $options['placeholder'],
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
                'max'           => $options['data']->getMax(),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => ItemCollection::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_item_collection_data';
    }
}
