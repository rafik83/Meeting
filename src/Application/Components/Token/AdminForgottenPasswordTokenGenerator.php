<?php

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken as AdminForgottenPasswordToken;

class AdminForgottenPasswordTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param Admin $admin
     *
     * @return AdminForgottenPasswordToken
     */
    public function generate(Admin $admin)
    {
        return new AdminForgottenPasswordToken(
            $admin,
            $this->generateToken($admin),
            $this->expirateDate
        );
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P1D');
    }
}
