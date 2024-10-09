<?php

namespace AIMuse\Validators\Datasets\Conversations;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class CreateBulkConversationValidator implements Validator
{
  public $constraint;

  public function __construct()
  {
    $conversationConstraint = (new CreateConversationValidator())->constraint;
    unset($conversationConstraint->fields['dataset_id']);

    $this->constraint = new Assert\Collection([
      'fields' => [
        'conversations' => new Assert\Required([
          new Assert\Type('array'),
          new Assert\All([
            $conversationConstraint
          ])
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
