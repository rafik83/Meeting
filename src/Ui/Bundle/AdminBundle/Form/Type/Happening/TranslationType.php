<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Proximum\Vimeet\Domain\Event\Image;
use Proximum\Vimeet\Domain\MimeType\MimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('webinarHeaderImage', FileType::class, [
                'required' => false,
                'attr' => [
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
            ->add('webinarWaitingMedia', FileType::class, [
                'required' => false,
                'attr' => [
                    'accept' => implode(', ', MimeType::MEDIA_MIME_TYPES),
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes' => MimeType::MEDIA_MIME_TYPES,
                            'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                        ]
                    ),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'happening_translation';
    }
}
