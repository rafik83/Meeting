<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Create;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'sheetViews',
            SheetChoiceType::class,
            [
                'sheetViews' => $options['sheetViews'],
            ]
        );

        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Create::class, 'sheetViews' => []]);
        $resolver->setAllowedTypes('sheetViews', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'create_linked_sheets';
    }
}
