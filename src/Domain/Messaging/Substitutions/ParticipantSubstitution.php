<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantSubstitution implements SubstituteInterface
{
    /**
     * @var OwnerFirstnameSubstitution
     */
    private $firstnameSubstitution;

    /**
     * @var OwnerLastnameSubstitution
     */
    private $lastnameSubstitution;

    /**
     * ParticipantSubstitution constructor.
     *
     * @param OwnerFirstnameSubstitution $firstnameSubstitution
     * @param OwnerLastnameSubstitution  $lastnameSubstitution
     */
    public function __construct(
        OwnerFirstnameSubstitution $firstnameSubstitution,
        OwnerLastnameSubstitution $lastnameSubstitution
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

        return ucfirst(strtolower($firstname)) . ' ' . mb_strtoupper($lastname);
    }
}
