<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\ValueObjects;

/**
 * Priority Value Object
 *
 * Representa la prioridad de una tarea.
 * Inmutable y type-safe usando PHP 8.1+ enums.
 */
enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    /**
     * Obtiene el nombre legible de la prioridad
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Baja',
            self::MEDIUM => 'Media',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Crítica',
        };
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::CRITICAL => 'red',
        };
    }

    /**
     * Obtiene el score numérico de la prioridad (para ordenamiento)
     */
    public function score(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        };
    }

    /**
     * Compara esta prioridad con otra
     *
     * @return int -1 si es menor, 0 si es igual, 1 si es mayor
     */
    public function compare(self $other): int
    {
        return $this->score() <=> $other->score();
    }

    /**
     * Verifica si esta prioridad es mayor que otra
     */
    public function isHigherThan(self $other): bool
    {
        return $this->score() > $other->score();
    }

    /**
     * Verifica si esta prioridad es menor que otra
     */
    public function isLowerThan(self $other): bool
    {
        return $this->score() < $other->score();
    }

    /**
     * Verifica si requiere atención inmediata
     */
    public function requiresImmediateAttention(): bool
    {
        return in_array($this, [self::HIGH, self::CRITICAL]);
    }

    /**
     * Obtiene el emoji representativo
     */
    public function emoji(): string
    {
        return match($this) {
            self::LOW => '🔵',
            self::MEDIUM => '🟡',
            self::HIGH => '🟠',
            self::CRITICAL => '🔴',
        };
    }

    /**
     * Obtiene todas las prioridades como array asociativo
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $priority) {
            $result[$priority->value] = $priority->label();
        }
        return $result;
    }

    /**
     * Obtiene las prioridades ordenadas por score
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        $priorities = self::cases();
        usort($priorities, fn($a, $b) => $a->score() <=> $b->score());
        return $priorities;
    }
}
