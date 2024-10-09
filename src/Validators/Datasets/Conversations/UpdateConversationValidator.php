<?php

namespace AIMuse\Validators\Datasets\Conversations;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class UpdateConversationValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'dataset_id' => new Assert\Optional([
          new Assert\IsNull([], 'Dataset ID cannot be updated'),
          new Assert\NotBlank([], 'Dataset ID cannot be updated'),
        ]),
        'prompt' => new Assert\Optional([
          new Assert\Type('string'),
        ]),
        'response' => new Assert\Optional([
          new Assert\Type('string'),
        ]),
        'priority' => new Assert\Optional([
          new Assert\Type('integer'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
