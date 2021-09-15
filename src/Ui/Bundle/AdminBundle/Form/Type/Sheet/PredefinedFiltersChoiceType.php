<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\BooleanFiltersBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PredefinedFiltersChoiceType extends AbstractType
{
    /**
     * @var BooleanFiltersBuilder
     */
    private $booleanFilterBuilder;

    /**
     * @param BooleanFiltersBuilder $booleanFiltersBuilder
     */
    public function __construct(BooleanFiltersBuilder $booleanFiltersBuilder)
    {
        $this->booleanFilterBuilder = $booleanFiltersBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                $filters = $this->booleanFilterBuilder->getFilters($options['event']);
                $filters = array_flip($filters);
                $predefinedFilters = [
                    'admin.sheet.created_today'     => Constant::CREATED_TODAY,
                    'admin.sheet.created_this_week' => Constant::CREATED_THIS_WEEK,
                    'admin.sheet.filter.no_order'   => Constant::ORDER_STATUS_NO_ORDER,
                    'admin.sheet.filter.has_cart'   => Constant::HAS_CART,
                ];

                return array_merge($predefinedFilters, $filters);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_predefined_filters_choice';
    }
}
