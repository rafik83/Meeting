<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\File;

class FileDataType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TemplateObject\UploadObject $uploadObject */
        $uploadObject = $options['object'];

        $mimeTypes = MimeType::getMimeTypesByFormats($uploadObject->getOption('formats'));
        if (!$mimeTypes) {
            return;
        }

        $builder->add('file', FileType::class, [
            'label' => true === $options['showLabel'] ? $uploadObject->getLabel($options['locale']) : false,
            'required' => $uploadObject->getOption('required') && null === $uploadObject->getPath(),
            'help' => $this->getHelp($uploadObject),
            'attr' => [
                'accept' => implode(', ', $mimeTypes),
            ],
            'constraints' => [
                new File([
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['object', 'locale'])
            ->setAllowedTypes('object', TemplateObject\UploadObject::class)
            ->setDefaults([
                'label' => false,
                'showLabel' => false,
                'data_class' => TemplateObject\UploadObject::class,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_file_data';
    }

    private function getHelp(TemplateObject\UploadObject $uploadObject): string
    {
        $formatsTranslated = array_map(function (string $format) {
            return $this->translator->trans(sprintf('%s.%s', 'template.upload.formats', $format));
        }, $uploadObject->getFormats());

        return sprintf(
            '%s %s',
            $uploadObject->isCrypted() ? $this->translator->trans('common.cryptedUploadFileHelp') : '',
            $this->translator->transChoice(
                'common.required_formats',
                \count($uploadObject->getFormats()),
                ['%format%' => implode(', ', $formatsTranslated)]
            )
        );
    }
}
