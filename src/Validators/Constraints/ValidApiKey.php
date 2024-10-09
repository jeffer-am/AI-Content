<?php

namespace AIMuse\Validators\Constraints;

use AIMuseVendor\Symfony\Component\Validator\Constraint;

class ValidApiKey extends Constraint
{
  public string $message = 'The value is not a valid {{ mode }} API key.';
  public string $mode = 'openAI';

  public function __construct(
    string $mode,
    array $groups = null,
    mixed $payload = null
  ) {
    parent::__construct([], $groups, $payload);

    $this->mode = $mode;
  }

  public function validatedBy()
  {
    return ValidApiKeyValidator::class;
  }
}
