<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CustomLink;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation\TranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StyleGuideIconType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AbstractCustomLinkType extends AbstractType
{
    private TypeRepositoryInterface $typeRepository;

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = $this->typeRepository->getTypesByEvent($options['event']);

        $builder
            ->add(
                'translatedLabels',
                CollectionType::class,
                [
                    'entry_type' => TranslationType::class,
                    'label' => false,
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'required' => true,
                    'help' => 'form.custom_link_create.children.url.help',
                ]
            )
            ->add(
                'types',
                ChoiceType::class,
                [
                    'choice_label' => static fn($type) => $type->getTitle($options['locale']),
                    'choice_value' => static fn($type) => $type->getId(),
                    'choices' => $types,
                    'choice_translation_domain' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add(
                'iconName',
                StyleGuideIconType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'iconColor',
                ColorType::class,
                [
                    'required' => true,
                ],
            )
            ->add(
                'labelColor',
                ColorType::class,
                [
                    'required' => true,
                ],
            )
            ->add(
                'buttonColor',
                ColorType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'priority',
                IntegerType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children['translatedLabels'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['locale', 'event']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
