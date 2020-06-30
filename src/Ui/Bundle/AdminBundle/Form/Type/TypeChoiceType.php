<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeChoiceType extends AbstractType
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event', 'locale', 'user', 'exceptHidden']);
        $resolver->setDefined(['orderByTitle']);
        $resolver->addAllowedTypes('orderByTitle', 'bool');
        $resolver->addAllowedTypes('exceptHidden', 'bool');
        $resolver->setDefaults([
            'exceptHidden' => false,
            'orderByTitle' => false,
            'choice_label' => static function (Options $options) {
                return static function ($type) use ($options) {
                    if ($type instanceof Type) {
                        return $type->getTitle($options['locale']);
                    }

                    return null;
                };
            },
            'choice_value' => static function ($type) {
                if ($type instanceof Type) {
                    return $type->getId();
                }

                return null;
            },
            'choices' => function (Options $options) {
                if ($options['user']->hasAllowedTypes()) {
                    $types = $this->typeRepository->getAllowedTypesByEvent(
                        $options['user'],
                        $options['event']
                    );
                } else {
                    $types = $this->typeRepository->getLocalizedTypesByEvent(
                        $options['event'],
                        $options['locale']
                    );
                }

                if ($options['orderByTitle']) {
                    $locale = $options['locale'];
                    usort(
                        $types,
                        static function (Type $typeA, Type $typeB) use ($locale) {
                            return mb_strtolower($typeA->getTitle($locale)) <=>
                                mb_strtolower($typeB->getTitle($locale));
                        }
                    );
                }

                if ($options['exceptHidden']) {
                    foreach ($types as $key => $type) {
                        if ($type->isHidden()) {
                            unset($types[$key]);
                        }
                    }

                    $types = array_values($types);
                }

                return $types;
            },
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
