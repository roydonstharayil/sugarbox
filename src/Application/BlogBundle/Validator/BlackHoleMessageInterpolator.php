<?php

namespace Application\MiamBundle\Validator;
use Symfony\Component\Validator\MessageInterpolator\MessageInterpolatorInterface;

class BlackHoleMessageInterpolator implements MessageInterpolatorInterface
{
  /**
   * {@inheritDoc}
   */
  public function interpolate($text, array $parameters = array())
  {
    $sources = array();
    $targets = array();

    foreach ($parameters as $key => $value)
    {
      $sources[] = '%'.$key.'%';
      $targets[] = $value;
    }

    return str_replace($sources, $targets, $text);
  }
}
