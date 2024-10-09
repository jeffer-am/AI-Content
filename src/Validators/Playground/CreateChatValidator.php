<?php

namespace AIMuse\Validators\Playground;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class CreateChatValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'message' => new Assert\Collection([
          'content' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
          ],
          'meta' => new Assert\Optional([
            new Assert\Collection([
              'templates' => new Assert\Optional([
                new Assert\Type('array'),
              ]),
            ])
          ])
        ]),
        'meta' => new Assert\Optional([
          new Assert\Collection([
            'model' => new Assert\Optional([
              new Assert\Type('array'),
            ]),
            'dataset' => new Assert\Optional([
              new Assert\Type('string'),
            ]),
          ])
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
