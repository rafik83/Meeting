<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\Save\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class PrepareUserDataForApiCallHandler
{
    /** @var LeniUserViewQueryHandler */
    private $leniUserViewQueryHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var LeniApiCallJobQueueInterface */
    private $leniApiCallJobQueue;

    /** @var LeniUserViewNormalizer */
    private $leniUserViewNormalizer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     * @param LeniUserViewNormalizer            $leniUserViewNormalizer
     * @param LeniApiCallJobQueueInterface      $leniApiCallJobQueue
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        LeniUserViewQueryHandler $leniUserViewQueryHandler,
        LeniUserViewNormalizer $leniUserViewNormalizer,
        LeniApiCallJobQueueInterface $leniApiCallJobQueue,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->leniApiCallJobQueue = $leniApiCallJobQueue;
        $this->leniUserViewNormalizer = $leniUserViewNormalizer;
        $this->dateTime = $dateTime;
    }

    public function handle(PrepareUserDataForApiCall $command): void
    {
        $event = $command->event;
        $user = $command->user;
        $previousUserEventExtraData = $command->previousUserEventExtraData;

        try {
            $leniUserView = $this->leniUserViewQueryHandler->handle(
                new LeniUserViewQuery($event, $user, $previousUserEventExtraData)
            );
        } catch (SheetNotFoundException $sheetNotFoundException) {
            return;
        } catch (TypeDoesNotMatchException $typeDoesNotMatchException) {
            return;
        }

        $leniUserData = $this->leniUserViewNormalizer->normalize($leniUserView);

        // User data did not changed, skip
        if ($previousUserEventExtraData instanceof ExtraData
            && $leniUserData ===  unserialize($previousUserEventExtraData->getValue(), ['allowed_classes' => false])
        ) {
            return;
        }

        $userExtraDataPendingFingerprint = $this->addOrUpdatePendingFingerprint($event, $user, $leniUserData);

        // Create a job for calling LENI API
        $this->leniApiCallJobQueue->createJob($userExtraDataPendingFingerprint);
    }

    /**
     * @param Event $event
     * @param User  $user
     * @param array $leniUserData
     *
     * @return ExtraData
     */
    private function addOrUpdatePendingFingerprint(Event $event, User $user, array &$leniUserData): ExtraData
    {
        $userExtraDataPendingFingerprint = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            Type::LENI_FINGERPRINT_PENDING,
            $user
        );

        $fingerPrint = serialize($leniUserData);

        if ($userExtraDataPendingFingerprint instanceof ExtraData) {
            $userExtraDataPendingFingerprint->update($fingerPrint, $this->dateTime);
            $this->extraDataRepository->set($userExtraDataPendingFingerprint);
        } else {
            $userExtraDataPendingFingerprint = new ExtraData(
                $user,
                $event,
                Type::LENI_FINGERPRINT_PENDING,
                $fingerPrint,
                $this->dateTime
            );
            $this->extraDataRepository->add($userExtraDataPendingFingerprint);
        }

        return $userExtraDataPendingFingerprint;
    }
}
