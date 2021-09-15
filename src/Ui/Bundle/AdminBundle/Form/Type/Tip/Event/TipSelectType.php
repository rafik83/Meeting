<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TipSelectType extends AbstractType
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * @param UrlGeneratorInterface  $urlGenerator
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        TipRepositoryInterface $tipRepository
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->tipRepository = $tipRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale']);
        $resolver->setDefaults([
            'choice_label' => function ($tip) {
                if ($tip instanceof Tip) {
                    return $tip->getTitle();
                }

                return null;
            },
            'choices'      => $this->tipRepository->getGlobals(),
            'choice_value' => function ($tip) {
                if ($tip instanceof Tip) {
                    return $tip->getId();
                }

                return null;
            },
            'choice_attr'  => function (Options $options) {
                return function (Tip $tip) use ($options) {
                    return [
                        'data-preview-url' => $this->urlGenerator->generate('admin_tip_event_preview', [
                            'locale' => $options['locale'],
                            'tip'    => $tip->getId(),
                        ]),
                    ];
                };
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
