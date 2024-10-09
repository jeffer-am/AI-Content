<?php

namespace AIMuse\Helpers;

use WP_Post;
use WC_Product;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuseVendor\League\HTMLToMarkdown\HtmlConverter;

class PromptHelper
{
  private static HtmlConverter $converter;

  public static function replaceVariables(string $prompt, int $postId = null)
  {
    $prompt = static::replaceSiteVariables($prompt);

    if ($postId) {
      $post = get_post($postId);

      if ($post) {
        $prompt = static::replacePostVariables($post, $prompt);

        if (function_exists('wc_get_product') && $post->post_type === 'product') {
          $product = wc_get_product($post);
          $prompt = static::replaceProductVariables($product, $prompt);
        }
      }
    }

    return $prompt;
  }

  public static function replaceSiteVariables(string $prompt)
  {
    $variables = [
      'site_title' => get_bloginfo('title'),
      'site_language' => get_bloginfo('language'),
      'site_url' => get_bloginfo('url'),
    ];

    foreach ($variables as $key => $value) {
      $prompt = str_replace('{{' . $key . '}}', $value, $prompt);
    }

    return $prompt;
  }

  public static function replacePostVariables(WP_Post $post, string $prompt)
  {
    if (!$post) {
      return $prompt;
    }

    $replacers = [
      'post_title' => 'PostTitle',
      'post_content' => 'PostContent',
      'post_excerpt' => 'PostExcerpt',
      'post_tags' => 'PostTags',
      'post_categories' => 'PostCategories',
    ];

    foreach ($replacers as $key => $method) {
      if (strpos($prompt, '{{' . $key . '}}') !== false) {
        $prompt = static::{"replace$method"}($post, $prompt);
      }
    }

    return $prompt;
  }

  public static function replacePostTitle(WP_Post $post, string $prompt)
  {
    return str_replace('{{post_title}}', $post->post_title, $prompt);
  }

  public static function replacePostContent(WP_Post $post, string $prompt)
  {
    return str_replace('{{post_content}}', static::convertHtmlToMarkdown($post->post_content), $prompt);
  }

  public static function replacePostExcerpt(WP_Post $post, string $prompt)
  {
    return str_replace('{{post_excerpt}}', $post->post_excerpt, $prompt);
  }

  public static function replacePostTags(WP_Post $post, string $prompt)
  {
    return str_replace('{{post_tags}}', static::getCommaSeparatedTerms($post->ID, 'post_tag'), $prompt);
  }

  public static function replacePostCategories(WP_Post $post, string $prompt)
  {
    return str_replace('{{post_categories}}', static::getCommaSeparatedTerms($post->ID, 'category'), $prompt);
  }

  public static function replaceProductVariables(WC_Product $product, string $prompt)
  {
    $replacers = [
      'product_name' => 'ProductName',
      'product_short_description' => 'ProductShortDescription',
      'product_description' => 'ProductDescription',
      'product_attributes' => 'ProductAttributes',
      'product_tags' => 'ProductTags',
      'product_price' => 'ProductPrice',
      'product_weight' => 'ProductWeight',
      'product_length' => 'ProductLength',
      'product_width' => 'ProductWidth',
      'product_height' => 'ProductHeight',
      'product_sku' => 'ProductSku',
      'product_purchase_note' => 'ProductPurchaseNote',
      'product_categories' => 'ProductCategories',
    ];

    foreach ($replacers as $key => $method) {
      if (strpos($prompt, '{{' . $key . '}}') !== false) {
        $prompt = static::{"replace$method"}($product, $prompt);
      }
    }

    return $prompt;
  }

  public static function replaceProductName(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_name}}', $product->get_name(), $prompt);
  }

  public static function replaceProductShortDescription(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_short_description}}', static::convertHtmlToMarkdown($product->get_short_description()), $prompt);
  }

  public static function replaceProductDescription(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_description}}', static::convertHtmlToMarkdown($product->get_description()), $prompt);
  }

  public static function replaceProductAttributes(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_attributes}}', implode(',', array_map(function ($attribute) {
      return $attribute->get_name();
    }, $product->get_attributes())), $prompt);
  }

  public static function replaceProductTags(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_tags}}', static::getCommaSeparatedTerms($product->get_id(), 'product_tag'), $prompt);
  }

  public static function replaceProductPrice(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_price}}', $product->get_price(), $prompt);
  }

  public static function replaceProductWeight(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_weight}}', $product->get_weight(), $prompt);
  }

  public static function replaceProductLength(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_length}}', $product->get_length(), $prompt);
  }

  public static function replaceProductWidth(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_width}}', $product->get_width(), $prompt);
  }

  public static function replaceProductHeight(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_height}}', $product->get_height(), $prompt);
  }

  public static function replaceProductSku(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_sku}}', $product->get_sku(), $prompt);
  }

  public static function replaceProductPurchaseNote(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_purchase_note}}', $product->get_purchase_note(), $prompt);
  }

  public static function replaceProductCategories(WC_Product $product, string $prompt)
  {
    return str_replace('{{product_categories}}', static::getCommaSeparatedTerms($product->get_id(), 'product_cat'), $prompt);
  }

  public static function getCommaSeparatedTerms(int $id, string $taxonomy)
  {
    $terms = wp_get_post_terms($id, $taxonomy, ['fields' => 'all']);
    $names = array_map(function ($term) {
      return $term->name;
    }, $terms);

    return implode(',', $names);
  }

  private static function convertHtmlToMarkdown(string $html)
  {
    try {
      return static::converter()->convert($html);
    } catch (\InvalidArgumentException $e) {
      return $html;
    } catch (\Throwable $th) {
      Log::error('An error occured when converting HTML to Markdown', [
        'error' => $th,
        'trace' => $th->getTraceAsString(),
      ]);

      throw $th;
    }
  }

  private static function converter()
  {
    if (!isset(self::$converter)) {
      self::$converter = new HtmlConverter();
    }

    return self::$converter;
  }
}
