<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults(
            [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => AnswerType::class,
                'entry_options' => static function (Options $options) {
                    return [
                        'label' => false,
                        'event' => $options['event'],
                    ];
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
