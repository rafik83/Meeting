<?php

namespace Proximum\Vimeet\Application\Components\Token\User;

use DateInterval;
use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Token\AbstractTokenGenerator;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;

class ActivateAccountTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $respository;

    /** @var string */
    private $userActivateAccountTokenExpiration;

    /**
     * @param ActivateAccountTokenRepositoryInterface $respository
     * @param DateTimeInterface                      $dateTime
     * @param string                                  $userActivateAccountTokenExpiration
     */
    public function __construct(
        ActivateAccountTokenRepositoryInterface $respository,
        DateTimeInterface $dateTime,
        $userActivateAccountTokenExpiration
    ) {
        $this->userActivateAccountTokenExpiration = $userActivateAccountTokenExpiration;
        $this->respository = $respository;

        parent::__construct($dateTime);
    }

    /**
     * Delete all user token and generate a new one
     *
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return ActivateAccountToken
     */
    public function generate(User $user, Sheet $sheet): ActivateAccountToken
    {
        $token = new ActivateAccountToken($user, $this->generateToken($user), $sheet, $this->expirateDate);

        $this->respository->deleteAllForUser($user);
        $this->respository->create($token);

        return $token;
    }

    protected function getLifetime()
    {
        return new DateInterval($this->userActivateAccountTokenExpiration);
    }
}
