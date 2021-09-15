<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature;

use Proximum\Vimeet\Application\Serializer\Charset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharsetChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $charsets = [Charset::WINDOWS_1252, Charset::ISO_8859_1, Charset::UTF_8];

        $resolver->setDefaults([
            'choices' => array_combine($charsets, $charsets),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'charset_choice';
    }
}
