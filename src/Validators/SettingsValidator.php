<?php

namespace AIMuse\Validators;

use AIMuse\Contracts\Validator;
use AIMuse\Validators\Constraints\ValidApiKey;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints as Assert;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class SettingsValidator implements Validator
{
  public function validate(array $values): ConstraintViolationListInterface
  {
    $validator = Validation::createValidator();
    $constraint = new Assert\Collection([
      'fields' => [
        'openAiApiKey' => new Assert\Optional([
          new Assert\Type('string'),
          new ValidApiKey('openAI')
        ]),
        'googleAiApiKey' => new Assert\Optional([
          new Assert\Type('string'),
          new ValidApiKey('googleAI')
        ]),
        'openRouterApiKey' => new Assert\Optional([
          new Assert\Type('string'),
          new ValidApiKey('openRouter')
        ]),
        'textModel' => new Assert\Optional([
          new Assert\Type('string'),
          new Assert\NotBlank(),
        ]),
        'imageModel' => new Assert\Optional([
          new Assert\Type('string'),
          new Assert\NotBlank(),
        ]),
      ],
      'allowExtraFields' => true,
    ]);

    return $validator->validate($values, $constraint);
  }
}
