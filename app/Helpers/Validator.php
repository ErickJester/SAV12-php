<?php
/**
 * Validador simple
 */

class Validator {
    private array $errors = [];

    public function required(string $field, ?string $value, string $label = ''): self {
        if (empty(trim($value ?? ''))) {
            $this->errors[$field] = ($label ?: $field) . ' es requerido.';
        }
        return $this;
    }

    public function email(string $field, ?string $value): self {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'El correo no es válido.';
        }
        return $this;
    }

    public function minLength(string $field, ?string $value, int $min, string $label = ''): self {
        if ($value && strlen($value) < $min) {
            $this->errors[$field] = ($label ?: $field) . " debe tener al menos $min caracteres.";
        }
        return $this;
    }

    public function matches(string $field, ?string $value1, ?string $value2, string $message = ''): self {
        if ($value1 !== $value2) {
            $this->errors[$field] = $message ?: 'Los campos no coinciden.';
        }
        return $this;
    }

    public function inArray(string $field, ?string $value, array $allowed, string $label = ''): self {
        if ($value && !in_array($value, $allowed)) {
            $this->errors[$field] = ($label ?: $field) . ' no es válido.';
        }
        return $this;
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    public function firstError(): ?string {
        return reset($this->errors) ?: null;
    }
}
