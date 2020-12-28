<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetManager
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var UserManager */
    private $userManager;

    /** @var TypeManager */
    private $typeManager;

    /** @var SheetInfoSetter */
    private $sheetInfoSetter;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoSetter $sheetInfoSetter,
        UserManager $userManager,
        TypeManager $typeManager
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->userManager = $userManager;
        $this->typeManager = $typeManager;
        $this->sheetInfoSetter = $sheetInfoSetter;
    }

    /**
     * @throws \Exception
     */
    public function create(
        Event $event,
        User $user = null,
        Type $type = null,
        string $title = null,
        Sheet\Group $group = null,
        string $createdAt = 'now'
    ): Sheet {
        if (null === $user) {
            $user = $this->userManager->create();
        }

        if (null === $type) {
            $type = $this->typeManager->create($event, 'Type 1');
        }

        $sheet = SheetFactory::create($event, $user, new \DateTime($createdAt), $type);
        $sheet->setData([
            'dcc42d3d' => ['text' => ['fr' => $title, 'en' => $title]],
            '03b394ac' => ['items' => []],
            '63ccc105' => ['items' => []],
        ]);
        $sheet->setRegistrationData([]);
        $sheet->setTitle($title);

        if (null !== $group) {
            $sheet->setGroup($group);
        }

        if (null !== $title) {
            $this->sheetInfoSetter->setSheetTitle($sheet, $title);
        }

        $this->sheetRepository->add($sheet);

        return $sheet;
    }

    public function getSheetByEventAndTitle(Event $event, string $sheetTitle): ?Sheet
    {
        return $this->sheetRepository->getSheetByEventAndTitle($event, $sheetTitle);
    }

    public function setInCatalog(Sheet $sheet): void
    {
        $sheet->setInCatalog(true);
        $sheet->setInCatalogAt(new \DateTime());

        $this->sheetRepository->set($sheet);
    }

    public function setValidated(Sheet $sheet): void
    {
        $sheet->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $sheet->markAsValidated();

        $this->sheetRepository->set($sheet);
    }

    public function setEnabled(Sheet $sheet): void
    {
        $sheet->setEnable(true);

        $this->sheetRepository->set($sheet);
    }

    public function updateCompleteness(Sheet $sheet, int $completeness): void
    {
        $sheet->setCompleteness($completeness);

        $this->sheetRepository->set($sheet);
    }
}
