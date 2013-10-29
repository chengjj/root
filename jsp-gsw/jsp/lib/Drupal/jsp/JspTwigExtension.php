<?php

namespace Drupal\jsp;

use Drupal\Core\Template\TwigExtension;

class JspTwigExtension extends TwigExtension {

  public function getFilters() {
    return array(
      'truncate_utf8' => new \Twig_Filter_Function('truncate_utf8'),
      'truncate' => new \Twig_Filter_Function('truncate_utf8'),
    );
  }
  public function getName() {
    return 'jsp.twig_extension';
  }
}

