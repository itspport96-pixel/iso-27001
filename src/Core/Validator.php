<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $f) {
            if (!isset($this->data[$f]) || trim((string)$this->data[$f]) === '') {
                $this->errors[$f][] = "$f is required";
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "$field must be a valid email";
        }
        return $this;
    }

    public function min(string $field, int $len): self
    {
        if (isset($this->data[$field]) && strlen(trim((string)$this->data[$field])) < $len) {
            $this->errors[$field][] = "$field must be at least $len characters";
        }
        return $this;
    }

    public function max(string $field, int $len): self
    {
        if (isset($this->data[$field]) && strlen(trim((string)$this->data[$field])) > $len) {
            $this->errors[$field][] = "$field must not exceed $len characters";
        }
        return $this;
    }

    public function unique(string $field, string $table, string $column, ?int $excludeId = null): self
    {
        $db  = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = :val";
        $params = ['val' => $this->data[$field] ?? ''];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->errors[$field][] = "$field already exists";
        }
        return $this;
    }

    public function confirmed(string $field): self
    {
        $confirm = $field . '_confirmation';
        if (($this->data[$field] ?? '') !== ($this->data[$confirm] ?? '')) {
            $this->errors[$field][] = "$field confirmation does not match";
        }
        return $this;
    }

    public function integer(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "$field must be an integer";
        }
        return $this;
    }

    public function in(string $field, array $values): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field][] = "$field is invalid";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $msgs) {
            return $msgs[0];
        }
        return null;
    }
}
