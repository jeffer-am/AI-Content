<?php

namespace AIMuse\Validators\Templates;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class UpdateValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'id' => [
          new Assert\NotBlank(),
          new Assert\Type('integer'),
        ],
        'name' => [
          new Assert\NotBlank(),
          new Assert\Type('string'),
        ],
        'category_id' => [
          new Assert\NotBlank(),
          new Assert\Type('integer'),
        ],
        'type' => [
          new Assert\NotBlank(),
          new Assert\Type('string'),
        ],
        'prompt' => [
          new Assert\NotBlank(),
          new Assert\Type('string'),
        ],
        'capabilities' => [
          new Assert\Type('array'),
        ],
        'dataset_slug' => new Assert\Optional([
          new Assert\Type('string'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
