<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractEditableTextInputDataType extends AbstractType
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
        /** @var EditableText $text */
        $text      = $options['object'];
        $locale    = $options['locale'];
        $showLabel = $options['showLabel'];
        $attr      = $text->hasMaxLength() ? ['maxlength' => $text->getMaxLength()] : [];

        $constraints = $this->getConstraints($text, $options['data_class']);

        if ($text->isTextarea()) {
            $attr['rows'] = $options['rows'];

            if ($text->hasMaxLength()) {
                $attr['data-text-max-length-indicator']    = $text->getMaxLength();
                $attr['data-text-max-length-translations'] = sprintf(
                    '%s|%s|%s',
                    $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.plural', [], 'forms', $locale),
                    $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.singular', [], 'forms', $locale),
                    $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.reached', [], 'forms', $locale)
                );
            }

            $builder
                ->add(EditableText::CONTENT, TextareaType::class, [
                    'placeholder'        => $text->getOption('placeholder', $locale),
                    'label'              => $showLabel ? $text->getOption('label', $locale) : false,
                    'attr'               => $attr,
                    'required'           => $text->getOption('required'),
                    'translation_domain' => false,
                    'constraints'        => $constraints,
                ])
            ;
        } else {
            $builder
                ->add(EditableText::CONTENT, TextType::class, [
                    'label'              => $showLabel ? $text->getOption('label', $locale) : false,
                    'required'           => $text->getOption('required'),
                    'placeholder'        => $text->getOption('placeholder', $locale),
                    'attr'               => $attr,
                    'translation_domain' => false,
                    'constraints'        => $constraints,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('showLabel', 'bool');
        $resolver->setAllowedTypes('object', EditableText::class);
        $resolver->setDefaults([
            'data_class' => EditableText::class,
            'rows'       => 7,
            'showLabel'  => true,
        ]);
    }

    private function getConstraints(EditableText $text, ?string $dataClass): array
    {
        $constraints = [];

        if (null === $dataClass) {
            if ($text->isRequired()) {
                $constraints[] = new NotBlank();
            }

            if ($text->hasMaxLength()) {
                $constraints[] = new Length(['max' => $text->getMaxLength()]);
            }
        }

        return $constraints;
    }
}
