<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagView;
use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagViews;
use Proximum\Vimeet\Application\View\Catalog\TagFilterView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagFiltersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TagFilterView[] $tagFilterViews */
        $tagFilterViews = $options['tagFilterViews'];

        /** @var NomenclatureTagViews[] $taggedNomenclatureTagViews */
        $taggedNomenclatureTagViews = $options['taggedNomenclatureTagViews'];

        foreach ($tagFilterViews as $tagFilterView) {
            $choices = [];
            $nomenclatureTagViews = $taggedNomenclatureTagViews[$tagFilterView->tag] ?? null;

            if (null !== $nomenclatureTagViews) {
                $choices = $nomenclatureTagViews->nomenclatureTagViews;
            }

            $builder->add($tagFilterView->tag, ChoiceType::class, [
                'label' => $tagFilterView->label,
                'choice_value' => function (NomenclatureTagView $nomenclatureTagView = null) {
                    if (null !== $nomenclatureTagView) {
                        return $nomenclatureTagView->key;
                    }

                    return null;
                },
                'choice_label' => function (NomenclatureTagView $nomenclatureTagView = null) {
                    if (null !== $nomenclatureTagView) {
                        return $nomenclatureTagView->title;
                    }

                    return null;
                },
                'choices' => $choices,
                'required' => false,
                'multiple' => true,
                'attr' => $this->getAttributes($nomenclatureTagViews, $tagFilterView),
            ]);
        }
    }

    private function getAttributes(?NomenclatureTagViews $nomenclatureTagViews, TagFilterView $tagFilterView): array
    {
        if (null !== $nomenclatureTagViews && $nomenclatureTagViews->maxDepth > 1) {
            return [
                'data-placeholder' => $tagFilterView->placeholder,
                'data-select-in-list' => true,
                'class' => 'hidden',
            ];
        }

        return [
            'data-placeholder' => $tagFilterView->placeholder,
            'class' => 'form-control select2',
            'data-disallow-clear' => 'true',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'tagFilterViews',
                'taggedNomenclatureTagViews',
            ])
            ->setAllowedTypes('tagFilterViews', 'array')
            ->setAllowedTypes('taggedNomenclatureTagViews', 'array')
        ;
    }
}
