<?php

namespace AIMuse\Validators\Templates\Categories;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class CreateValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'name' => [
        new Assert\NotBlank(),
        new Assert\Type('string'),
      ],
    ]);

    return $validator->validate($values, $constraint);
  }
}
