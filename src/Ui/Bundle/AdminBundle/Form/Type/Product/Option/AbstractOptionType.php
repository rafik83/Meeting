<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Option;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;

abstract class AbstractOptionType extends AbstractType
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
                'entry_type' => Option\TranslationsType::class,
                'label'      => false,
            ])
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('availabilityCurrent', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('availabilityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('updatable', CheckboxType::class, [
                'required' => false,
            ])
            ->add('updatableUntil', DateTimePickerType::class, [
                'required' => false,
            ])
            ->add('subjectedToValidation', CheckboxType::class, [
                'required' => false,
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
