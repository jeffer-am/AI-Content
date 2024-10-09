<?php

namespace AIMuse\Validators\Datasets;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class ListDatasetsValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'search' => new Assert\Optional([
          new Assert\Type('string'),
        ]),
        'sort' => new Assert\Optional([
          new Assert\Collection([
            'field' => new Assert\Required([
              new Assert\Type('string'),
            ]),
            'order' => new Assert\Optional([
              new Assert\Choice(['asc', 'desc']),
            ]),
          ]),
        ]),
        'page' => new Assert\Optional([
          new Assert\Type('int'),
        ]),
        'limit' => new Assert\Optional([
          new Assert\Type('int'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
