<?php

namespace AIMuse\Validators\Datasets;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class ExportDatasetValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'type' => new Assert\Choice(['jsonl', 'csv']),
        'limit' => new Assert\Type('int'),
        'offset' => new Assert\Type('int'),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
