<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Phone;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class ValidationRequiredChecker
{
    /** @var DDayGuesser */
    private $dDayGuesser;

    /** @var TipTranslationViewQueryHandler */
    private $tipTranslationViewQueryHandler;

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /**
     * ValidationRequiredChecker constructor.
     *
     * @param DDayGuesser                       $dDayGuesser
     * @param TipTranslationViewQueryHandler    $tipTranslationViewQueryHandler
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     */
    public function __construct(
        DDayGuesser $dDayGuesser,
        TipTranslationViewQueryHandler $tipTranslationViewQueryHandler,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository
    ) {
        $this->dDayGuesser                    = $dDayGuesser;
        $this->tipTranslationViewQueryHandler = $tipTranslationViewQueryHandler;
        $this->userEventPhoneRepository       = $userEventPhoneRepository;
    }

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     *
     * @return bool
     */
    public function handle(Sheet $sheet, User $user, string $locale): bool
    {
        if ($this->dDayGuesser->isItDDay($sheet->getEvent())) {
            $tipTranslationView = $this->tipTranslationViewQueryHandler->handle(
                new TipTranslationViewQuery(
                    $sheet->getType(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    $locale
                )
            );

            if (!empty($tipTranslationView)) {
                $userEventPhone = $this->userEventPhoneRepository->findValidated(
                    $user,
                    $sheet->getEvent()
                );

                return $userEventPhone === null;
            }
        }

        return false;
    }
}
