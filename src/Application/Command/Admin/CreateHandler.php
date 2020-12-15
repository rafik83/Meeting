<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Exception\Admin\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Admin;

class CreateHandler extends AbstractCreateHandler
{
    public function handle(Create $create): void
    {
        $create->email = StringHelper::trimSpacesAndNonBreakSpaces($create->email);

        $existingAdmin = $this->adminRepository->findByEmail($create->email, true);

        if ($existingAdmin !== null) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $create->email), $existingAdmin);
        }

        $salt = $this->saltGenerator->generate();

        $admin = new Admin(
            $create->email,
            $salt,
            null,
            $create->locale,
            $create->firstname,
            $create->lastname,
            $create->role,
            $this->dateTime
        );

        $password = $this->encoder->encode($admin, $create->password);
        $admin->updatePassword($salt, $password);

        foreach ($create->events as $event) {
            $admin->addEvent($event);
        }

        $this->adminRepository->add($admin);
    }
}
