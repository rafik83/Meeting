<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\CheckboxesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomizeAbstractType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCustomizableByType = $options['isCustomizableByType'];

        if (true === $isCustomizableByType) {
            $builder->add('associatedTypes', ChoiceType::class, [
                'choice_label' => function ($type) use ($options) {
                    if ($type instanceof Type) {
                        return $type->getTitle($options['locale']);
                    }

                    return null;
                },
                'choice_value' => function ($type) {
                    if ($type instanceof Type) {
                        return $type->getId();
                    }

                    return null;
                },
                'choices' => $options['remainingTypes'],
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'attr' =>  [
                    'data-toggle-visibility' => 'true',
                    'data-element-id-to-hide' => 'message-wysiwyg-section',
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => CustomizeTranslationType::class,
                'label' => false,
                'entry_options' => [
                    'isCustomizableByType' => $isCustomizableByType,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('locale');
        $resolver->setRequired('isCustomizableByType');
        $resolver->setAllowedTypes('isCustomizableByType', 'bool');
        $resolver->setDefined('remainingTypes');
    }
}
