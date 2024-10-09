<?php

namespace AIMuse\Validators;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class GenerateTextValidator implements Validator
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
        'channel' => [
          new Assert\NotBlank(),
          new Assert\Type('string'),
        ],
        'component' => new Assert\Type('string'),
        'post' => new Assert\Optional([
          new Assert\Type('integer'),
        ]),
        'model' => new Assert\Optional([
          new Assert\Collection([
            'id' => [
              new Assert\Type('string'),
              new Assert\NotBlank()
            ],
            'settings' => new Assert\Optional([
              new Assert\Type('array'),
            ]),
          ]),
        ]),
        'preview' => new Assert\Optional([
          new Assert\Type('boolean'),
        ]),
        'messages' => new Assert\Optional([
          new Assert\Type('array'),
          new Assert\All([
            new Assert\Collection([
              'content' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
              ],
              'role' => [
                new Assert\Type('string'),
                new Assert\Choice(['user', 'model']),
              ],
            ]),
          ]),
        ]),
        'withoutSystemPrompt' => new Assert\Optional([
          new Assert\Type('boolean', 'The Without System Prompt {{ value }} is not a valid {{ type }}.'),
        ]),
        'contextLength' => new Assert\Optional([
          new Assert\Type('integer', 'The Context Length {{ value }} is not a valid {{ type }}.'),
        ]),
        'dataset' => new Assert\Optional([
          new Assert\Type('string', 'The Dataset {{ value }} is not a valid {{ type }}.'),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
