<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker;

use Proximum\Vimeet\Domain\Event\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints\File;

abstract class AbstractSpeakerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, ['required' => true])
            ->add('lastname', TextType::class, ['required' => true])
            ->add('translations', CollectionType::class, [
                'entry_type' => SpeakerTranslationType::class,
                'required'   => true,
            ])
            ->add('organization', TextType::class, ['required' => true])
            ->add(
                'logo',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new File(
                            [
                                'mimeTypes' => Image::SUPPORTED_MIME_TYPE,
                                'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'photo',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new File(
                            [
                                'mimeTypes' => Image::SUPPORTED_MIME_TYPE,
                                'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                            ]
                        ),
                    ],
                ]
            )
            ->add('email', EmailType::class, ['required' => false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }
}
