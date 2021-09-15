<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\UserExtraData;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class UserExtraDataFingerprintManager
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $fingerPrint
     */
    public function addOrUpdateFingerprint(Event $event, User $user, string $fingerPrint): void
    {
        $userExtraDataFingerprint = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            ExtraDataType::LENI_FINGERPRINT,
            $user
        );

        if ($userExtraDataFingerprint instanceof ExtraData) {
            $userExtraDataFingerprint->update($fingerPrint, $this->dateTime);
            $this->extraDataRepository->set($userExtraDataFingerprint);
        } else {
            $userExtraDataFingerprint = new ExtraData(
                $user,
                $event,
                ExtraDataType::LENI_FINGERPRINT,
                $fingerPrint,
                $this->dateTime
            );
            $this->extraDataRepository->add($userExtraDataFingerprint);
        }
    }
}
