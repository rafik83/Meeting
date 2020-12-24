<?php

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetGuesser
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $locale
     *
     * @throws SheetNotFoundException
     * @throws \Exception
     *
     * @return Sheet
     */
    public function getUserSheet(User $user, Event $event, $locale)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (empty($sheets)) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        if (!$event->hasLocale($locale)) {
            throw new \Exception('Locale not available for this event.');
        }

        return $sheet;
    }
}
