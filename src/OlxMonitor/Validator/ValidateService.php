<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Validator;

use Autodoctor\OlxWatcher\Exceptions\ValidateException;

class ValidateService
{
    /**
     * @throws ValidateException
     */
    public static function validated(array $rules): array
    {
        return (new self())->validate($rules);
    }

    /**
     * @throws ValidateException
     */
    public function validate(array $rules): array
    {
        $data = filter_var_array($_REQUEST, $rules);
        $errors = $this->validationErrors($data);

        if ($data === false || $errors) {
            throw new ValidateException(
                sprintf(
                    'Invalid entered data: "%s"',
                    $this->toString($errors)
                )
            );
        }
        return $data;
    }

    protected function validationErrors(array $data): array
    {
        return array_filter($data, fn($value) => ($value === false) || ($value === null));
    }

    protected function toString(array $errors): string
    {
        return implode(', ', array_keys($errors));
    }
}
