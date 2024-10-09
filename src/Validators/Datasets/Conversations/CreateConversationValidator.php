<?php

namespace AIMuse\Validators\Datasets\Conversations;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class CreateConversationValidator implements Validator
{
  public $constraint;

  public function __construct()
  {
    $this->constraint = new Assert\Collection([
      'fields' => [
        'dataset_id' => new Assert\Required([
          new Assert\Type('integer'),
        ]),
        'prompt' => new Assert\Required([
          new Assert\Type('string'),
        ]),
        'response' => new Assert\Required([
          new Assert\Type('string'),
        ]),
        'priority' => new Assert\Optional([
          new Assert\Type('integer'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);
  }

  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();

    return $validator->validate($values, $this->constraint);
  }
}
