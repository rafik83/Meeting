<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet;

use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Catalog\CatalogTagFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchFacets', CollectionType::class, [
                'entry_type'    => SearchFacetType::class,
                'entry_options' => [
                    'help'     => true,
                    'required' => false,
                ],
                'label' => false,
            ])
            ->add('catalogTagFilters', CollectionType::class, [
                'entry_type'  => CatalogTagFilterType::class,
                'attr' => [
                    'data-shared-choices-collection' => 'tags',
                ],
                'entry_options' => [
                    'label'  => false,
                    'event' => $options['event'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['types']);
        $resolver->setDefaults([
            'data_class' => Update::class,
            'event' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'search_facet_update';
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view['searchFacets']->children as $key => $childView) {
            $childView->vars['label'] = str_replace('prototype', $options['types'][$key], $childView->vars['label']);
            $childView->vars['help']  = str_replace('prototype', $options['types'][$key], $childView->vars['help']);
        }
    }
}
