<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Design;

use Proximum\Vimeet\Domain\Event\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class LogoLocalizedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notificationImage', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', Image::SUPPORTED_MIME_TYPE),
                ],
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', Image::SUPPORTED_MIME_TYPE),
                ],
            ])
            ->add('mobileLogo', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', Image::SUPPORTED_MIME_TYPE),
                ],
            ])
        ;
    }
}
