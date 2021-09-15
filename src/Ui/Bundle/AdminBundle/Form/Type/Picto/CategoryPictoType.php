<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Picto;

use Proximum\Vimeet\Domain\Model\AbstractCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryPictoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => AbstractCategory::getPictos(),
            'choice_label' => function ($choice) {
                return 'form.admin.picto.' . $choice;
            },
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
