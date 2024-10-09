<?php

declare(strict_types=1);

namespace AIMuseVendor\League\HTMLToMarkdown;

use AIMuseVendor\League\HTMLToMarkdown\Converter\BlockquoteConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\CodeConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\CommentConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\ConverterInterface;
use AIMuseVendor\League\HTMLToMarkdown\Converter\DefaultConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\DivConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\EmphasisConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\HardBreakConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\HeaderConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\HorizontalRuleConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\ImageConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\LinkConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\ListBlockConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\ListItemConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\ParagraphConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\PreformattedConverter;
use AIMuseVendor\League\HTMLToMarkdown\Converter\TextConverter;

final class Environment
{
    /** @var Configuration */
    protected $config;

    /** @var ConverterInterface[] */
    protected $converters = [];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Configuration($config);
        $this->addConverter(new DefaultConverter());
    }

    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function addConverter(ConverterInterface $converter): void
    {
        if ($converter instanceof ConfigurationAwareInterface) {
            $converter->setConfig($this->config);
        }

        foreach ($converter->getSupportedTags() as $tag) {
            $this->converters[$tag] = $converter;
        }
    }

    public function getConverterByTag(string $tag): ConverterInterface
    {
        if (isset($this->converters[$tag])) {
            return $this->converters[$tag];
        }

        return $this->converters[DefaultConverter::DEFAULT_CONVERTER];
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function createDefaultEnvironment(array $config = []): Environment
    {
        $environment = new static($config);

        $environment->addConverter(new BlockquoteConverter());
        $environment->addConverter(new CodeConverter());
        $environment->addConverter(new CommentConverter());
        $environment->addConverter(new DivConverter());
        $environment->addConverter(new EmphasisConverter());
        $environment->addConverter(new HardBreakConverter());
        $environment->addConverter(new HeaderConverter());
        $environment->addConverter(new HorizontalRuleConverter());
        $environment->addConverter(new ImageConverter());
        $environment->addConverter(new LinkConverter());
        $environment->addConverter(new ListBlockConverter());
        $environment->addConverter(new ListItemConverter());
        $environment->addConverter(new ParagraphConverter());
        $environment->addConverter(new PreformattedConverter());
        $environment->addConverter(new TextConverter());

        return $environment;
    }
}
