<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio;

use Proximum\Vimeet\Domain\Event\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        });
    }
}
