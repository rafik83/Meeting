<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

abstract class AbstractCreateHandler
{
    /** @var AdminRepositoryInterface */
    protected $adminRepository;

    /** @var PasswordEncoderInterface */
    protected $encoder;

    /** @var SaltGeneratorInterface */
    protected $saltGenerator;

    /** @var \DateTimeInterface */
    protected $dateTime;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        \DateTimeInterface $dateTime
    ) {
        $this->adminRepository = $adminRepository;
        $this->encoder = $encoder;
        $this->saltGenerator = $saltGenerator;
        $this->dateTime = $dateTime;
    }
}
