<?php

namespace Proximum\Vimeet\Domain\Messaging\Emailing;

use Proximum\Vimeet\Domain\Exception\Messaging\UndefinedSubstitutionProviderException;
use Proximum\Vimeet\Domain\Messaging\Substitutions\SubstitutionsProviders;
use Proximum\Vimeet\Domain\Model\Sheet;

class SubstitutionResolver
{
    /**
     * @var SubstitutionsProviders
     */
    private $substitutionsProviders;

    /**
     * @var string[]
     */
    private $placeholders = [];

    /**
     * SubstitutionResolver constructor.
     *
     * @param SubstitutionsProviders $substitutionsProviders
     */
    public function __construct(SubstitutionsProviders $substitutionsProviders)
    {
        $this->substitutionsProviders = $substitutionsProviders;
        $this->placeholders           = $substitutionsProviders->getTags();
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string[]
     */
    public function getSubstitutions(Sheet $sheet, $locale)
    {
        $substitutions = [];

        foreach ($this->placeholders as $placeholder) {
            try {
                $value = $this->substitutionsProviders
                    ->getSubstitution($placeholder)
                    ->getValue($sheet, $locale);

                $substitutions[$placeholder] = $value;
            } catch (UndefinedSubstitutionProviderException $exception) {
                continue;
            }
        }

        return $substitutions;
    }
}
