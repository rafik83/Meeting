<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCodeChoiceType extends AbstractType
{
    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('event')
            ->setAllowedTypes('event', Event::class)
            ->setDefaults(
                [
                    'class' => PromotionCode::class,
                    'choice_label' => function ($promotionCode) {
                        if ($promotionCode instanceof PromotionCode) {
                            return sprintf('%s (%s)', $promotionCode->getTitle(), $promotionCode->getCode());
                        }

                        return null;
                    },
                    'choice_value' => function ($promotionCode) {
                        if ($promotionCode instanceof PromotionCode) {
                            return $promotionCode->getId();
                        }

                        return null;
                    },
                    'choices' => function (Options $options) {
                        return $this->promotionCodeRepository->findByEvent($options['event']);
                    },
                    'choice_translation_domain' => false,
                    'required' => false,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
