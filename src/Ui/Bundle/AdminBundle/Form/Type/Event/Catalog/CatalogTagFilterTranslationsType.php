<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CatalogTagFilterTranslationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'form.search_facet_update.children.catalogTagFilters.translations.label',
            ])
            ->add('placeholder', TextareaType::class, [
                'label' => 'form.search_facet_update.children.catalogTagFilters.translations.placeholder',
            ])
        ;
    }
}
