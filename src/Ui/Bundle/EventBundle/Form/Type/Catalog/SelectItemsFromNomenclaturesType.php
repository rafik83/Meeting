<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\CheckboxesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectItemsFromNomenclaturesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Nomenclature[] $nomenclatures */
        $nomenclatures = $options['nomenclatures'];
        $locale = $options['locale'];

        foreach ($nomenclatures as $nomenclature) {
            $builder->add(
                sprintf('items%s', $nomenclature->getId()),
                CheckboxesType::class,
                [
                    'nomenclature' => $nomenclature,
                    'locale' => $locale,
                    'label' => false,
                    'required' => false,
                    'levelsArchitecture' => $nomenclature->getLevelsArchitecture($locale)
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['nomenclatures', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'select_items_from_nomenclatures';
    }
}
