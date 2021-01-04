<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planning;

use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'choices'            => PlanningOrderedBy::getPlanningOrderByOptions(),
                'choice_label'       => function ($label) {
                    return sprintf('form.admin_export_planning.children.orderBy.choice.%s', $label);
                },
                'translation_domain' => 'forms',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
