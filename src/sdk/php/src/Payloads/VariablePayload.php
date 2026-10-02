<?php

namespace AcidIndustries\Livelog\Payloads;

use PHPUnit\Event\Runtime\Runtime;
use ReflectionClass;
use ReflectionProperty;

final class VariablePayload extends Payload {
    private const MAX_DEPTH = 8;
    private const MAX_VALUES = 1_000;

    private readonly array $_value;
    private readonly ?string $_label;

    public function __construct(
        mixed $value,
        ?string $label = null,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null,
    ) {
        parent::__construct(
            'dump',
            $sessionId,
            $requestId,
            $source
        );

        $remaining = self::MAX_VALUES;

        $this->_value = self::normalize($value, 0, $remaining);
        $this->_label = $label;
    }

    protected function data(): array {
        return [
            'label' => $this->_label,
            'value' => $this->_value,
        ];
    }

    private function normalize(
        mixed $value,
        int $depth,
        int &$remaining
    ): array {
        $remaining--;

        if (is_null($value)) {
            return ['kind' => 'null'];
        }

        if (is_bool($value)) {
            return ['kind' => 'bool', 'value' => $value];
        }

        if (is_int($value)) {
            return ['kind' => 'integer', 'value' => $value];
        }

        if (is_float($value)) {
            return is_finite($value)
                ? ['kind' => 'float', 'value' => $value]
                : ['kind' => 'string', 'value' => (string)$value];
        }

        if (is_string($value)) {
            return ['kind' => 'string', 'value' => (string)$value];
        }

        if (!is_array($value) && !is_object($value)) {
            return [
                'kind' => 'string',
                'value' => '[' . get_debug_type($value) . ']'
            ];
        }

        if ($depth >= self::MAX_DEPTH) {
            return ['kind' => 'string', 'value' => '[max depth reached]'];
        }

        if (is_array($value)) {
            return $this->handleArray($value, $depth, $remaining);
        } else {
            return $this->handleObject($value, $depth, $remaining);
        }
    }

    private function handleArray(array $value, int $depth, int $remaining): array {
        $entries = ['kind' => 'array', 'items' => []];
        $values = &$entries['items'];
        foreach ($value as $key => $item) {
            $values[] = [
                'key' => $key,
                'value' => $this->normalize($item, $depth + 1, $remaining),
            ];
        }

        return $entries;
    }

    private function handleObject(object $value, int $depth, int $remaining): array {
        $reflection = new ReflectionClass($value);
        $entries = ['kind' => 'object', 'class' => $reflection->getName(), 'properties' => []];
        $properties = &$entries['properties'];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $properties[] = [
                'name' => $property->getName(),
                'value' => $this->normalize($property->getValue($value), $depth + 1, $remaining)
            ];
        }

        return $entries;
    }
}
