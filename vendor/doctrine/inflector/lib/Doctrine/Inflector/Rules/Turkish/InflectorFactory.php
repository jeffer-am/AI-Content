<?php

declare(strict_types=1);

namespace AIMuseVendor\Doctrine\Inflector\Rules\Turkish;

use AIMuseVendor\Doctrine\Inflector\GenericLanguageInflectorFactory;
use AIMuseVendor\Doctrine\Inflector\Rules\Ruleset;

final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset(): Ruleset
    {
        return Rules::getSingularRuleset();
    }

    protected function getPluralRuleset(): Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
