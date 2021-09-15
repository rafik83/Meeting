<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextDataType extends AbstractType
{
    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param TranslatorAdapter $translator
     */
    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EditableText $object */
        $object    = $options['object'];
        $locale    = $options['locale'];
        $attributes = [
            'rows' => 7,
        ];

        if (0 !== $object->getMaxLength()) {
            $attributes['data-text-max-length-indicator']    = $object->getMaxLength();
            $attributes['data-text-max-length-translations'] = sprintf(
                '%s|%s|%s',
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.plural', [], 'forms', $locale),
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.singular', [], 'forms', $locale),
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.reached', [], 'forms', $locale)
            );
        }

        $fallback = $object instanceof EditableText && $object->getLocale() !== $object->getFallback() ? $object->getFallbackContent() : false;

        $builder
            ->add(EditableText::CONTENT, TextareaType::class, [
                'placeholder'         => $options['placeholder'],
                'label'               => false,
                'attr'                => $attributes,
                'required'            => $object->getRequired(),
                'fallbackTranslation' => $fallback,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => EditableText::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_editable_text_data';
    }
}
