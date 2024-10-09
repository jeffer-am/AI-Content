<?php

namespace AIMuse\Validators;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class GenerateImageValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'templates' => new Assert\Optional([
          new Assert\Type('array'),
        ]),
        'prompt' => new Assert\Optional(),
        'model' => new Assert\Optional([
          new Assert\Collection([
            'id' => new Assert\Type('string'),
            'settings' => new Assert\Optional([
              new Assert\Type('array'),
            ])
          ])
        ]),
        'component' => new Assert\Type('string'),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
