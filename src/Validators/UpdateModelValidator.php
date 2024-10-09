<?php

namespace AIMuse\Validators;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class UpdateModelValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'id' => new Assert\Required([
          new Assert\Type('string'),
          new Assert\NotBlank(),
        ]),
        'settings' => new Assert\Required([
          new Assert\Type('array'),
        ])
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
