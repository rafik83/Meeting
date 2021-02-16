<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextTranslatableDataType extends AbstractEditableTextInputDataType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', EditableText::class);
        $resolver->setDefaults([
            'rows'      => 7,
            'showLabel' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'text_data_translatable';
    }
}
