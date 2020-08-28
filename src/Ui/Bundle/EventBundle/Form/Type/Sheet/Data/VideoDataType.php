<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VideoDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TemplateObject\Video $video */
        $video = $options['object'];

        $builder->add('file', FileType::class, [
            'label' => true === $options['showLabel'] ? $video->getLabel($options['locale']) : false,
            'required' => $video->getOption('required'),
            'attr' => [
                'accept' => implode(', ', TemplateObject\Video::supportedMimeType()),
            ],
            'constraints' => [
                new File([
                    'mimeTypes' => TemplateObject\Video::supportedMimeType(),
                    'maxSize' => '100M',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Video::class);
        $resolver->setDefaults([
            'label' => false,
            'showLabel' => false,
            'data_class' => TemplateObject\Video::class,
            'placeholder' => null,
            'help' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_video_data';
    }
}
