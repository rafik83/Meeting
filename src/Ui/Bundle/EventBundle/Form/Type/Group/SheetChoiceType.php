<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Sheet\SortedSheet;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetChoiceType extends AbstractType
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SortedSheet */
    private $sortedSheet;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesserCache    $sheetInfoGuesserCache
     * @param SortedSheet              $sortedSheet
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        SortedSheet $sortedSheet
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
        $this->sortedSheet = $sortedSheet;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['group']);
        $resolver->setAllowedTypes('group', Group::class);
        $resolver->setDefaults(
            [
                'choice_label' => function (Sheet $sheet) {
                    return $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null);
                },
                'choices'      => function (Options $options) {
                    $sheets = $this->sheetRepository->getByGroup($options['group']);
                    $sheets = $this->sortedSheet->sort($sheets);

                    return $sheets;
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
