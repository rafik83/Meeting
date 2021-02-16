<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category;

use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as BaseChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('event');
        $resolver->setDefined('removeCurrentEvent');
        $resolver->setDefaults([
            'class'                     => Category::class,
            'choice_label'              => function ($category) {
                if ($category instanceof Category) {
                    return $category->getTitle();
                }

                return null;
            },
            'choice_value'              => function ($category) {
                if ($category instanceof Category) {
                    return $category->getId();
                }

                return null;
            },
            'choices'                   => function (Options $options) {
                return $this->categoryRepository->findByEvent($options['event']);
            },
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'unavailability_category_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseChoiceType::class;
    }
}
