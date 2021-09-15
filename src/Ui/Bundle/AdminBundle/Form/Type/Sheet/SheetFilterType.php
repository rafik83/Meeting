<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Symfony\Component\Form\FormBuilderInterface;

class SheetFilterType extends AbstractFilterType
{
    /**
     * @return array
     */
    public static function getDefaultFilters(): array
    {
        return [
            'enabled' => true,
            'orderBy' => Constant::ORDER_BY_CREATED_AT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('enabled', EnabledStateChoiceType::class, [
                'label'    => 'form.sheet_filter.children.enabledState.label',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
            ])
            ->add('orderBy', SortChoiceType::class, [
                'label' => 'form.sheet_filter.children.orderBy.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_filter';
    }
}
