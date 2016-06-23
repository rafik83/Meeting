<?php


namespace Ui\Bundle\AdminBundle\Form\Type\Product;


use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductIncludedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AbstractPlanType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('unitPrice', NumberType::class)
            ->add('translations', CollectionType::class, [
                'entry_type' => Plan\TranslationsType::class,
                'label'      => false,
            ])
            ->add('availabilityCurrent', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ]
            ])
            ->add('availabilityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ]
            ])
            ->add('features', CollectionType::class, [
                'entry_type'    => Feature\FeatureType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('productIncluded', CollectionType::class, [
                'entry_type'    => ProductIncludedType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label'  => false,
                    'event'  => $options['event'],
                    'locale' => $options['locale'],
                ],
                'attr'          => [
                    'data-shared-choices-collection' => 'products',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation)
        {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }
}
