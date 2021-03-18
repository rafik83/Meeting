<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Option;

use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\HappeningChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractUpdateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateOptionType extends AbstractUpdateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
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
            ->add('attributable', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'data-attributable-product-toggle-happening' => true,
                    'data-element-id-to-hide' => 'happenings-block',
                ],
            ])
            ->add('deletableUntil', DateTimePickerType::class, [
                'required' => false,
            ])
            ->add('buyableUntil', DateTimePickerType::class, [
                'required' => false,
            ])
            ->add('subjectedToValidation', CheckboxType::class, [
                'required' => false,
            ])
            ->add(
                'canScanParticipant', CheckboxType::class, [
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
            ->add('happenings', HappeningChoiceType::class, [
                'choices'  => $options['happenings'],
                'locale'   => $options['locale'],
                'required' => false,
                'multiple' => true,
                'select2'  => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['locale', 'happenings']);

        $resolver->setDefaults([
            'data_class' => UpdateOption::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_option';
    }
}
