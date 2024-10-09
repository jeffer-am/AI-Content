<?php

declare(strict_types=1);

namespace AIMuseVendor\League\HTMLToMarkdown;

interface PreConverterInterface
{
    public function preConvert(ElementInterface $element): void;
}
