<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation;

use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractStaticFormulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('types', ChoiceType::class, [
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
                'required' => true,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label' => false,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('locale');
        $resolver->setRequired('remainingTypes');
    }
}
