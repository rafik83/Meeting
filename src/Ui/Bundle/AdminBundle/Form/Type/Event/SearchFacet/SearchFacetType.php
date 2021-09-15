<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;

class SearchFacetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, ['label' => false])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $locale => $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($locale);
        }
    }
}
