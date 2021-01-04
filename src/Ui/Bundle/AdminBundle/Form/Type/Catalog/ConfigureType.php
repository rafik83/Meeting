<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Catalog\CatalogTagFilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigureType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('externalCatalogEnabled', CheckboxType::class, ['required' => false])
            ->add('registrationUrl', TextType::class, [
                'required' => false,
                'placeholder' => 'form.configure.children.registrationUrl.placeholder',
            ])
            ->add('hasMessage', CheckboxType::class, ['required' => false])
            ->add('messageTranslations', CollectionType::class, [
                'entry_type' => CatalogVisibilityTranslationType::class,
                'label'      => true,
            ])
            ->add('types', TypeChoiceType::class, [
                'event'    => $options['event'],
                'locale'   => $options['locale'],
                'user'     => $options['user'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('categories', CategoryChoiceType::class, [
                'event'    => $options['event'],
                'locale'   => $options['locale'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('searchFacets', CollectionType::class, [
                'entry_type'    => SearchFacetType::class,
                'entry_options' => [
                    'required' => false,
                    'help'     => true,
                ],
                'label'         => false,
            ])
            ->add('catalogTagFilters', CollectionType::class, [
                'entry_type'  => CatalogTagFilterType::class,
                'attr' => [
                    'data-shared-choices-collection' => 'tags',
                ],
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['user', 'event', 'locale', 'types']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('user', Admin::class);
        $resolver->setDefaults([
            'data_class' => Configure::class,
        ]);
    }

    /**
     * this gets called in the final stage before rendering the form
     *
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

        foreach ($view->children['messageTranslations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }
}
