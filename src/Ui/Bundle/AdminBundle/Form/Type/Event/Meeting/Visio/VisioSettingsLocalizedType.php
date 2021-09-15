<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio;

use Proximum\Vimeet\Domain\Event\Audio;
use Proximum\Vimeet\Domain\Event\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;

class VisioSettingsLocalizedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('header', FileType::class, [
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
            ->add('endSound', FileType::class, [
                'required' => false,
                'help' => 'form.event_meeting_visio_settings.children.localizedVisioSettings.prototype.children.endSound.help',
                'attr' => [
                    'accept' => implode(', ', Audio::SUPPORTED_MIME_TYPE),
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes' => Audio::SUPPORTED_MIME_TYPE,
                            'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                        ]
                    ),
                ],
            ])
            ->add('endImage', FileType::class, [
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
            ->add('endMessage', TextareaType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) {
            $settings = $event->getData();
            $form = $event->getForm();

            if (isset($settings['hasHeader']) && $settings['hasHeader'] === true) {
                $form
                    ->add('removeHeader', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }

            if (isset($settings['hasEndSound']) && $settings['hasEndSound'] === true) {
                $form
                    ->add('removeEndSound', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }

            if (isset($settings['hasEndImage']) && $settings['hasEndImage'] === true) {
                $form
                    ->add('removeEndImage', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }
        });
    }
}
