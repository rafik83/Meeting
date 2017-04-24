<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantSubstitution implements SubstituteInterface
{
    /**
     * @var FirstnameSubstitution
     */
    private $firstnameSubstitution;

    /**
     * @var LastnameSubstitution
     */
    private $lastnameSubstitution;

    /**
     * ParticipantSubstitution constructor.
     *
     * @param FirstnameSubstitution $firstnameSubstitution
     * @param LastnameSubstitution  $lastnameSubstitution
     */
    public function __construct(
        FirstnameSubstitution $firstnameSubstitution,
        LastnameSubstitution $lastnameSubstitution
    ) {
        $this->firstnameSubstitution = $firstnameSubstitution;
        $this->lastnameSubstitution  = $lastnameSubstitution;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $firstname = $this->firstnameSubstitution->getValue($sheet, $locale);
        $lastname = $this->lastnameSubstitution->getValue($sheet, $locale);

        return ucfirst(strtolower($firstname)) . ' ' . strtoupper($lastname);
    }
}
