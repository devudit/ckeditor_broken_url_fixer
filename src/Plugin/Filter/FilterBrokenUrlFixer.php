<?php
/**
 * Ckeditor Broken URL Fixer - Ckeditor broken url fixer, It removes broken
 * external and internal url but keeps text.
 *
 * @package     ckeditor_broken_url_fixer
 * @author      Udit Rawat <eklavyarwt@gmail.com>
 * @link        http://sarovarcreative.com/
 * @copyright   SarovarCreative
 * Date:        05/09/2020
 * Time:        07:46 PM
 */


namespace Drupal\ckeditor_broken_url_fixer\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to fix broken urls from content.
 *
 * @Filter(
 *   id = "filter_broken_url_fixer",
 *   title = @Translation("Remove broken url from content."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class FilterBrokenUrlFixer extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    $result = new FilterProcessResult($text);

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    // Get all a tag having href attribute.
    foreach ($xpath->query('//a[@href]') as $node) {
      $url = $node->getAttribute('href');
      if (!$this->validateUrl($url)) {
        // Create text node.
        $link_text = new \DomText($node->nodeValue);
        // insert it before the link node.
        $node->parentNode->insertBefore($link_text, $node);
        // And remove the link node.
        $node->parentNode->removeChild($node);
      }
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Remove broken url from content.');
  }

  /**
   * Validate url headers.
   *
   * @param null $url
   *
   * @return bool
   */
  private function validateUrl($url = NULL) {
    if ($url) {
      try {
        $headers = get_headers($url);
        $headers = (is_array($headers)) ? implode("\n ", $headers) : $headers;
        return (bool) preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
      } catch (\Exception $e) {
        // Return false.
      }
    }
    return FALSE;
  }

}
