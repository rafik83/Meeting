<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translationsInput', TranslationsType::class, [
            'locales' => $options['locales'],
            'entry_type' => EditableTextTranslatableDataType::class,
            'entry_options' => [
                'object' => $options['object'],
            ],
            'label' => false,
            'required' => $options['object']->getRequired(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locales']);
        $resolver->setAllowedTypes('object', TemplateObject\EditableText::class);
        $resolver->setAllowedTypes('locales', 'array');
        $resolver->setDefaults([
            'data_class' => EditableText::class,
        ]);
    }
}
