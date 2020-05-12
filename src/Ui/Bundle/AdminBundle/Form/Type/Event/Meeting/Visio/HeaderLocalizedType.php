<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio;

use Proximum\Vimeet\Domain\Event\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class HeaderLocalizedType extends AbstractType
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
            ->add('removeHeader', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }
}
