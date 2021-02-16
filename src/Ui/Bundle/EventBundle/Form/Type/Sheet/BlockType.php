<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\ImageDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\AbstractBlockType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractBlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['askLocaleFirst']) {
            $localeChoices = [];
            foreach ($options['locales'] as $locale) {
                $localeChoices[$locale] = Intl::getLocaleBundle()->getLocaleName($locale);
            }

            $builder->add('locale', ChoiceType::class, ['choices' => array_flip($localeChoices), 'mapped' => false]);
        }

        parent::buildForm($builder, $options);
    }

    protected function getObjects(array $options): array
    {
        /** @var Block $block */
        $block = $options['block'];

        return $block->getEditableObjects();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class'        => Block::class,
            'validation_groups' => ['block', 'Default'],
            'askLocaleFirst' => false,
        ]);
        $resolver->setRequired(['block']);
        $resolver->setAllowedTypes('block', Block::class);
        $resolver->setAllowedTypes('askLocaleFirst', 'bool');
    }

    protected function addText(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\EditableText $object,
        string $locale,
        array $locales
    ): void {
        // In this context, there is only one translation that is required
        $builder->add($key, EditableTextInputDataType::class, [
            'label'  => false,
            'object' => $object,
            'locale' => $locale,
        ]);
    }

    protected function addImage(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Image $object,
        string $locale
    ): void {
        $builder->add($key, ImageDataType::class, [
            'showLabel' => true,
            'locale'    => $locale,
            'object'    => $object,
            'attr'      => [
                'image-preview' => $object->hasTag(Tag::PARTICIPANT_AVATAR),
            ],
        ]);
    }
}
