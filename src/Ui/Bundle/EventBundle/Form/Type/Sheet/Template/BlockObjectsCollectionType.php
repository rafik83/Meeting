<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextTranslationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\NomenclatureDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockObjectsCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Template\Block $block */
        $block = $options['block'];

        foreach ($block->getEditableObjects() as $uid => $object) {
            if ($object instanceof Template\TemplateObject\EditableText) {
                $this->addText($uid, $builder, $object, $options['locale'], $options['locales']);
                continue;
            }

            if ($object instanceof Template\TemplateObject\Nomenclature) {
                $this->addNomenclature($uid, $builder, $object, $options['locale']);
                continue;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['block', 'locale', 'locales']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('locales', 'array');
        $resolver->setAllowedTypes('block', Template\Block::class);
    }

    private function addText(
        string $uid,
        FormBuilderInterface $builder,
        Template\TemplateObject\EditableText $object,
        string $locale,
        array $locales
    ) {
        if ($object->isTranslatable()) {
            $builder->add($uid, EditableTextTranslationType::class, [
                'locales' => $locales,
                'object' => $object,
                'label' => $object->getOption('label', $locale),
                'data_class' => null,
            ]);
        } else {
            $builder->add($uid, EditableTextInputDataType::class, [
                'label' => false,
                'locale' => $locale,
                'object' => $object,
                'data_class' => null,
            ]);
        }
    }

    private function addNomenclature(
        string $uid,
        FormBuilderInterface $builder,
        Template\TemplateObject\Nomenclature $object,
        string $locale
    ) {
        $builder->add(
            $uid,
            NomenclatureDataType::class,
            [
                'label' => false,
                'locale' => $locale,
                'object' => $object,
                'placeholder' => $object->getLabel($locale),
                'onMultipleUseSinglesInsteadOfCheckboxes' => true,
                'data_class' => null,
            ]
        );
    }
}
