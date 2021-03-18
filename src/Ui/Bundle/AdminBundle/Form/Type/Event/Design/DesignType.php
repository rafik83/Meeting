<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Design;

use Proximum\Vimeet\Application\Command\Event\Design\UpdateDesign;
use Proximum\Vimeet\Domain\Event\Image;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DesignType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('localizedImages', CollectionType::class, [
                'entry_type' => LogoLocalizedType::class,
                'label' => false,
                'required' => false,
            ])
            ->add('backgroundImage', FileType::class, [
                'required' => false,
                'attr'        => [
                    'accept' => implode(', ', Image::SUPPORTED_MIME_TYPE),
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes' => Image::SUPPORTED_MIME_TYPE,
                            'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                        ]
                    ),
                ],
            ])
            ->add('backgroundColor', TextType::class, [
                'required' => true,
            ])
            ->add('headerLeftColor', TextType::class, [
                'required' => true,
            ])
            ->add('headerRightColor', TextType::class, [
                'required' => true,
            ])
            ->add('headerButtonLeftColor', TextType::class, [
                'required' => true,
            ])
            ->add('headerButtonRightColor', TextType::class, [
                'required' => true,
            ])
            ->add('headerButtonTextColor', TextType::class, [
                'required' => true,
            ])
            ->add('leftColor', TextType::class, [
                'required' => true,
            ])
            ->add('rightColor', TextType::class, [
                'required' => true,
            ])
            ->add('textColor', TextType::class, [
                'required' => true,
            ])
        ;

        if (null !== $event && $event->getConfiguration()->hasBackgroundImage()) {
            $builder->add('removeBackgroundImage', CheckboxType::class, [
                'required' => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['localizedImages'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('event')
            ->setAllowedTypes('event', Event::class)
            ->setDefaults([
                'data_class' => UpdateDesign::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'event_update_design';
    }
}
