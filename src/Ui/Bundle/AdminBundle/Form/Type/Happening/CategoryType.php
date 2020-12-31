<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'         => Category::class,
            'query_builder' => function (Options $options) {
                return function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('category')
                        ->where('category.event = :event')
                        ->setParameter('event', $options['event']);
                };
            },
            'choice_label' => function (Options $options) {
                return function (Category $category) use ($options) {
                    return $category->getTitle($options['locale']);
                };
            },
        ]);

        $resolver->setRequired(['event', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
