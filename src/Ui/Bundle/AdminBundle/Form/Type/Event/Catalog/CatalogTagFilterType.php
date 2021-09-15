<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogTagFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sheetAndGenericTags = array_merge(Tag::getSheetAndGenericTags(), Tag::getGenericSheetTemplateTags());

        $builder
            ->add('tag', ChoiceType::class, [
                'choices' => array_combine($sheetAndGenericTags, $sheetAndGenericTags),
                'choice_label' => function ($value) {
                    return sprintf('template.tag.%s', $value);
                },
                'attr' => [
                    'data-shared-choices-collection-item' => 'tags',
                ],
                'choice_translation_domain' => 'templates',
            ])
            ->add('translations', CatalogTagFilterTranslationLocaleType::class, [
                'label' => false,
                'event' => $options['event'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'event' => null,
        ]);
    }
}
