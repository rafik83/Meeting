<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\View\Catalog\TagViewInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_value' => function (TagViewInterface $tagView = null) {
                if (null !== $tagView) {
                    return $tagView->getKey();
                }

                return null;
            },
            'choice_label' => function (TagViewInterface $tagView = null) {
                if (null !== $tagView) {
                    return $tagView->getTitle();
                }

                return null;
            },
            'required'     => false,
            'multiple'     => true,
            'attr'         => [
                'class'               => 'form-control select2',
                'data-disallow-clear' => 'true',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
