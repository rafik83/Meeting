<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Application\Adapter\SMSBlackListInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UpdateBlackListHandler
{
    /** @var SMSBlackListInterface */
    private $SMSBlackList;

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /**
     * @param SMSBlackListInterface             $SMSBlackList
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     */
    public function __construct(
        SMSBlackListInterface $SMSBlackList,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository
    ) {
        $this->SMSBlackList = $SMSBlackList;
        $this->userEventPhoneRepository = $userEventPhoneRepository;
    }

    /**
     * @param UpdateBlackList $command
     */
    public function handle(UpdateBlackList $command)
    {
        $blackList = $this->SMSBlackList->getBlackList();

        $this->userEventPhoneRepository->setIntoBlackList($blackList);
        $this->userEventPhoneRepository->unsetFromBlackList($blackList);
    }
}
