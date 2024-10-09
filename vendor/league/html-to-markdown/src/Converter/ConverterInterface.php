<?php

declare(strict_types=1);

namespace AIMuseVendor\League\HTMLToMarkdown\Converter;

use AIMuseVendor\League\HTMLToMarkdown\ElementInterface;

interface ConverterInterface
{
    public function convert(ElementInterface $element): string;

    /**
     * @return string[]
     */
    public function getSupportedTags(): array;
}
