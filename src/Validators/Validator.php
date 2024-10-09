<?php

namespace AIMuse\Validators;

use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class Validator
{
  public static function toArray(ConstraintViolationListInterface $validator)
  {
    $errors = [];

    foreach ($validator as $violation) {
      $errors[] = [
        'property' => $violation->getPropertyPath(),
        'message' => $violation->getMessage(),
      ];
    }

    return $errors;
  }
}
