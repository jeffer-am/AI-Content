<?php

namespace AIMuse\Validators\Playground;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class CreateMessageValidator implements Validator
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
        'role' => [
          new Assert\NotBlank(),
          new Assert\Choice(['user', 'model'])
        ],
        'meta' => new Assert\Optional([
          new Assert\Collection([
            'model' => new Assert\Optional([
              new Assert\Type('string'),
            ]),
            'service' => new Assert\Optional([
              new Assert\Type('string'),
            ]),
            'price' => new Assert\Optional([
              new Assert\Type(['float', 'integer']),
            ]),
            'templates' => new Assert\Optional([
              new Assert\Type('array'),
            ]),
          ])
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
