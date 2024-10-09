<?php

namespace AIMuse\Validators\Constraints;

use AIMuse\Services\OpenAI\OpenAI;
use AIMuseVendor\GuzzleHttp\Exception\ClientException;
use AIMuse\Services\OpenRouter\OpenRouter;
use AIMuseVendor\Symfony\Component\Validator\Constraint;
use AIMuse\Services\GoogleAI\GoogleAI;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuseVendor\Symfony\Component\Validator\ConstraintValidator;

class ValidApiKeyValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    if ($value == '') {
      return true;
    }

    $valid = false;
    if ($constraint->mode == 'openAI') {
      $valid = $this->validateOpenAI($value);
    } elseif ($constraint->mode == 'googleAI') {
      $valid = $this->validateGoogleAI($value);
    } elseif ($constraint->mode == 'openRouter') {
      $valid = $this->validateOpenRouter($value);
    }

    if (!$valid) {
      $violationBuilder =  $this->context->buildViolation($constraint->message);
      $violationBuilder->setParameter('{{ mode }}', $constraint->mode);

      if ($constraint->mode == 'google') {
        $violationBuilder->setParameter('{{ value }}', $value['project_id'] ?? '');
      } else {
        $violationBuilder->setParameter('{{ value }}', $value);
      }

      $violationBuilder->addViolation();
    }

    return $valid;
  }

  private function validateGoogleAI($value)
  {
    try {
      $client = GoogleAI::client($value);
      $client->models()->get();
      return true;
    } catch (\Throwable $th) {
      return false;
    }
  }

  private function validateOpenAI($value)
  {
    try {
      $client = OpenAI::client($value);
      $client->models()->get();
      return true;
    } catch (\Throwable $th) {
      return false;
    }
  }

  private function validateOpenRouter($value)
  {
    try {
      $client = OpenRouter::client($value);
      $client->auth()->key();
      return true;
    } catch (ClientException $e) {
      if ($e->getCode() === 401) {
        return false;
      }
    } catch (\Throwable $th) {
      return false;
    }
  }
}
