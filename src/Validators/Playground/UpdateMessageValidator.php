<?php

namespace AIMuse\Validators\Playground;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class UpdateMessageValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'content' => [
          new Assert\NotBlank(),
          new Assert\Type('string'),
        ],
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
