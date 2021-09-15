<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextInputDataType extends AbstractEditableTextInputDataType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\EditableText::class);
        $resolver->setDefaults([
            'data_class' => TemplateObject\EditableText::class,
            'rows'       => 7,
            'showLabel'  => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'text_data';
    }
}
