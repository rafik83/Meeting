<?php


namespace Proximum\Vimeet\Application\Exception\Admin;


use Proximum\Vimeet\Domain\Model\Admin;

class EmailAlreadyExistsException extends AdminException
{
    private $admin;

    public function __construct($message, $admin)
    {
        parent::__construct($message);
        $this->admin = $admin;
    }

    public function getExistingAdmin(): Admin
    {
        return $this->admin;
    }
}
