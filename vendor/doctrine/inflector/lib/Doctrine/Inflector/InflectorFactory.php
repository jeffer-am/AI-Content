<?php

declare(strict_types=1);

namespace AIMuseVendor\Doctrine\Inflector;

use AIMuseVendor\Doctrine\Inflector\Rules\English;
use AIMuseVendor\Doctrine\Inflector\Rules\French;
use AIMuseVendor\Doctrine\Inflector\Rules\NorwegianBokmal;
use AIMuseVendor\Doctrine\Inflector\Rules\Portuguese;
use AIMuseVendor\Doctrine\Inflector\Rules\Spanish;
use AIMuseVendor\Doctrine\Inflector\Rules\Turkish;
use InvalidArgumentException;

use function sprintf;

final class InflectorFactory
{
    public static function create(): LanguageInflectorFactory
    {
        return self::createForLanguage(Language::ENGLISH);
    }

    public static function createForLanguage(string $language): LanguageInflectorFactory
    {
        switch ($language) {
            case Language::ENGLISH:
                return new English\InflectorFactory();

            case Language::FRENCH:
                return new French\InflectorFactory();

            case Language::NORWEGIAN_BOKMAL:
                return new NorwegianBokmal\InflectorFactory();

            case Language::PORTUGUESE:
                return new Portuguese\InflectorFactory();

            case Language::SPANISH:
                return new Spanish\InflectorFactory();

            case Language::TURKISH:
                return new Turkish\InflectorFactory();

            default:
                throw new InvalidArgumentException(sprintf(
                    'Language "%s" is not supported.',
                    $language
                ));
        }
    }
}
