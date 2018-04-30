<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

abstract class AbstractCreateHandler
{
    /**
     * @var AdminRepositoryInterface
     */
    protected $adminRepository;

    /**
     * @var PasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var SaltGeneratorInterface
     */
    protected $saltGenerator;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param PasswordEncoderInterface $encoder
     * @param SaltGeneratorInterface   $saltGenerator
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator
    ) {
        $this->adminRepository = $adminRepository;
        $this->encoder         = $encoder;
        $this->saltGenerator   = $saltGenerator;
    }
}
