<?php

namespace Proximum\Vimeet\Application\Components\Token\Admin;

use DateInterval;
use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Token\AbstractTokenGenerator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;

class ActivateAccountTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $respository;

    public function __construct(
        ActivateAccountTokenRepositoryInterface $respository,
        DateTimeInterface $dateTime
    ) {
        parent::__construct($dateTime);


        $this->respository = $respository;
    }

    /**
     * Delete all user token and generate a new one
     *
     * @param Admin $admin
     *
     * @return ActivateAccountToken
     */
    public function generate(Admin $admin): ActivateAccountToken
    {
        $token = new ActivateAccountToken($admin, $this->generateToken($admin), $this->expirateDate);

        $this->respository->deleteAllForUser($admin);
        $this->respository->create($token);

        return $token;
    }

    protected function getLifetime()
    {
        return new DateInterval('P14D');
    }
}
