<?php

namespace AIMuse\Contracts;

use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

interface Validator
{
  public function validate(array $values): ConstraintViolationListInterface;
}
