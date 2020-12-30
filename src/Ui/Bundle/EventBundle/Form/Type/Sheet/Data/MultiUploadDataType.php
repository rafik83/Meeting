<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiUploadDataType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MultiUploadCollectionObject $uploadCollection */
        $uploadCollection = $options['object'];

        $builder
            ->add('uploads', CollectionType::class, [
                'entry_type' => UploadDataType::class,
                'entry_options' => [
                    'label' => false,
                    'collection' => $options['data'],
                    'required' => $uploadCollection->getRequired(),
                    'titlePlaceholder' => $uploadCollection->getTitlePlaceholder(),
                    'help' => $this->getHelp($uploadCollection->getFormats())
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'max' => $uploadCollection->getMax(),
            ]);
    }

    private function getHelp(array $formats): string
    {
        $formatsTranslated = array_map(function (string $format) {
            return $this->translator->trans(sprintf('%s.%s', 'template.upload.formats', $format));
        }, $formats);

        return $this->translator->transChoice(
            'common.required_formats',
            \count($formats),
            ['%format%' => implode(', ', $formatsTranslated)]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => MultiUploadCollectionObject::class,
            'placeholder' => null,
        ]);
    }
}
