<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetManager
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * SheetManager constructor.
     *
     * @param TypeRepositoryInterface    $typeRepository
     * @param SheetRepositoryInterface   $sheetRepository
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->typeRepository        = $typeRepository;
        $this->sheetRepository       = $sheetRepository;
        $this->requestRepository     = $requestRepository;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return Sheet[]
     */
    public function getUserSheetsThatCanSeeTheGivenSheet(User $user, Sheet $sheet)
    {
        $typesThatCanSee = $this->typeRepository->getSeeableTypeIdsBySheet($sheet);
        $typesForUser    = $this->typeRepository->getAllTypesByUser($user);

        $userTypeThatCanSeeTheSheet = [];
        $typesThatCanSee            = array_flip($typesThatCanSee);

        foreach ($typesForUser as $type) {
            if (isset($typesThatCanSee[$type->getId()])) {
                $userTypeThatCanSeeTheSheet[] = $type;
            }
        }

        $sheets = $this->sheetRepository->getUserSheetsByTypes($user, $userTypeThatCanSeeTheSheet);

        $sheets = array_filter($sheets, function ($sheetOfUser) use ($sheet) {
            return $sheetOfUser == $sheet ? false : true;
        });

        return $this->getSheetsWithoutMeetingRequestForTheGivenSheet($sheet, $sheets);
    }

    /**
     * @param Sheet   $sheet
     * @param Sheet[] $sheets
     *
     * @return Sheet[]
     */
    private function getSheetsWithoutMeetingRequestForTheGivenSheet(Sheet $sheet, array $sheets)
    {
        $allowedSheets = $sheets;

        foreach ($sheets as $givenSheetKey => $givenSheet) {
            $requests = $this->requestRepository->getAllRequestBySheet($givenSheet, ['disabled' => false]);

            foreach ($requests as $request) {
                if (($request->getToSheet() === $sheet || $request->getFromSheet() === $sheet)
                    && (Request::STATE_SENT === $request->getState() || Request::STATE_APPROVED === $request->getState())
                ) {
                    unset($allowedSheets[$givenSheetKey]);
                }
            }
        }

        return $allowedSheets;
    }

    /**
     * Is a user can see a $seebale sheet using $seet sheet
     *
     * @param User  $user    The user
     * @param Sheet $seer    The sheet used to see
     * @param Sheet $seeable The sheet the user want to see
     *
     * @return bool
     */
    public function isAllowedToSee(User $user, Sheet $seer, Sheet $seeable)
    {
        return in_array($seer, $this->getUserSheetsThatCanSeeTheGivenSheet($user, $seeable));
    }
}
