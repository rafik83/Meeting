<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetQueryHandlerInterface;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSearchType extends AbstractType
{
    /** @var SearchFacetQueryHandlerInterface */
    private $searchFacetViewQueryHandler;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param SearchFacetQueryHandlerInterface $searchFacetViewQueryHandler
     * @param TranslatorInterface              $translator
     */
    public function __construct(
        SearchFacetQueryHandlerInterface $searchFacetViewQueryHandler,
        TranslatorInterface $translator
    ) {
        $this->searchFacetViewQueryHandler = $searchFacetViewQueryHandler;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeViews                 = $options['typeViews'];
        $categoryViews             = $options['categoryViews'];
        $organizationCategoryViews = $options['organizationCategoryViews'];
        $positionViews             = $options['positionViews'];

        $searchFacetsView = $this->searchFacetViewQueryHandler->handle(
            new SearchFacetViewQuery($options['event'], $options['locale'])
        );

        $hasTypeViews    = \count($typeViews) > 1;
        $typeSearchFacet = $searchFacetsView->getType();

        /* show type search facet only if there is more than one filter
         * and if category search facet is disabled to avoid having both at the same time
         */
        if ($hasTypeViews && null !== $typeSearchFacet) {
            $builder
                ->add(
                    SearchFields::FILTER_TYPE,
                    ChoiceType::class,
                    [
                        'label'        => $typeSearchFacet->label,
                        'expanded'     => true,
                        'multiple'     => true,
                        'choices'      => $typeViews,
                        'choice_value' => function (TypeView $typeView) {
                            return $typeView->id;
                        },
                        'choice_label' => function (TypeView $typeView) {
                            return $typeView->title;
                        },
                    ]
                );
        }

        $hasCategoryViews    = \count($categoryViews) > 1;
        $categorySearchFacet = $searchFacetsView->getCategory();

        // show category search facet only if there is more than one filter
        if ($hasCategoryViews && null !== $categorySearchFacet) {
            $builder
                ->add(
                    SearchFields::FILTER_CATEGORY,
                    ChoiceType::class,
                    [
                        'label'        => $categorySearchFacet->label,
                        'expanded'     => true,
                        'multiple'     => true,
                        'choices'      => $categoryViews,
                        'choice_value' => function (CategoryView $categoryView) {
                            return $categoryView->id;
                        },
                        'choice_label' => function (CategoryView $categoryView) {
                            return $categoryView->title;
                        },
                    ]
                );
        }

        if (null !== ($organizationCategoryFacet = $searchFacetsView->getOrganizationCategory())) {
            $builder->add(SearchFields::FILTER_ORGANIZATION_CATEGORY, ChoiceType::class, [
                'label'        => $organizationCategoryFacet->label,
                'choices'      => $organizationCategoryViews,
                'choice_value' => function (OrganizationCategoryView $organizationCategoryView = null) {
                    if (null !== $organizationCategoryView) {
                        return $organizationCategoryView->key;
                    }

                    return null;
                },
                'choice_label' => function (OrganizationCategoryView $organizationCategoryView = null) {
                    if (null !== $organizationCategoryView) {
                        return $organizationCategoryView->title;
                    }

                    return null;
                },
                'required'     => false,
                'multiple'     => true,
                'attr' => [
                    'class'               => 'form-control select2',
                    'data-disallow-clear' => 'true',
                    'data-placeholder'    => $organizationCategoryFacet->placeholder,
                ],
            ]);
        }

        if (null !== ($positionFacet = $searchFacetsView->getPosition())) {
            $builder->add(SearchFields::FILTER_POSITION, TagChoiceType::class, [
                'label'   => $positionFacet->label,
                'choices' => $positionViews,
                'attr' => [
                    'class'               => 'form-control select2',
                    'data-disallow-clear' => 'true',
                    'data-placeholder'    => $positionFacet->placeholder,
                ],
            ]);
        }

        if (null !== ($localizationFacet = $searchFacetsView->getLocalization())) {
            $builder->add(SearchFields::FILTER_LOCALIZATION, HiddenType::class, [
                'label'    => $localizationFacet->label,
                'required' => false,
                'attr'     => [
                    'data-placeholder' => $localizationFacet->placeholder,
                ],
            ]);
        }

        if (null !== ($keywordFacet = $searchFacetsView->getKeywords())) {
            $builder->add(SearchFields::FILTER_CONTENT, HiddenType::class, [
                'label' => $keywordFacet->label,
                'attr'  => [
                    'data-placeholder' => $keywordFacet->placeholder,
                ],
            ]);
        }

        $tagFilterViews = $searchFacetsView->getTagFilterViews();
        if (!empty($tagFilterViews)) {
            $builder->add('tagFilters', TagFiltersType::class, [
                'label' => false,
                'tagFilterViews' => $tagFilterViews,
                'taggedNomenclatureTagViews' => $options['taggedNomenclatureTagViews'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'typeViews',
            'categoryViews',
            'organizationCategoryViews',
            'positionViews',
            'taggedNomenclatureTagViews',
            'event',
            'locale',
        ]);

        $resolver->setAllowedTypes('typeViews', 'array');
        $resolver->setAllowedTypes('categoryViews', 'array');
        $resolver->setAllowedTypes('organizationCategoryViews', 'array');
        $resolver->setAllowedTypes('positionViews', 'array');
        $resolver->setAllowedTypes('taggedNomenclatureTagViews', 'array');
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('event', Event::class);

        $resolver->setDefaults([
            'required'           => false,
            'method'             => 'GET',
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
    }
}
