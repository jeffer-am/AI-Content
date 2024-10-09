<?php

namespace AIMuse\Validators;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class HistoryTableValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'filters' => new Assert\Optional([
          new Assert\Collection([
            'model' => new Assert\Optional([
              new Assert\Type('string'),
            ]),
            'service' => new Assert\Optional([
              new Assert\Type('string'),
            ]),
            'user_id' => new Assert\Optional([
              new Assert\Type('int'),
            ]),
            'date' => new Assert\Optional([
              new Assert\Collection([
                'from' => new Assert\Type('string'),
                'to' => new Assert\Type('string'),
              ]),
            ])
          ]),
        ]),
        'sort' => new Assert\Optional([
          new Assert\Collection([
            'field' => new Assert\Type('string'),
            'order' => new Assert\Type('string'),
          ]),
        ]),
        'page' => new Assert\Optional([
          new Assert\Type('integer'),
        ]),
        'limit' => new Assert\Optional([
          new Assert\Type('integer'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
