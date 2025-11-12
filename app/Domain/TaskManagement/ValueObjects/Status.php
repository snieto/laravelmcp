<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\ValueObjects;

/**
 * Status Value Object
 *
 * Representa el estado de una tarea en el ciclo de vida.
 * Inmutable y type-safe usando PHP 8.1+ enums.
 */
enum Status: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';

    /**
     * Obtiene el nombre legible del estado
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::REVIEW => 'En Revisión',
            self::COMPLETED => 'Completada',
            self::BLOCKED => 'Bloqueada',
        };
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::REVIEW => 'yellow',
            self::COMPLETED => 'green',
            self::BLOCKED => 'red',
        };
    }

    /**
     * Verifica si puede transicionar a otro estado
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [
                self::IN_PROGRESS,
                self::BLOCKED,
            ]),
            self::IN_PROGRESS => in_array($newStatus, [
                self::REVIEW,
                self::BLOCKED,
                self::PENDING,
            ]),
            self::REVIEW => in_array($newStatus, [
                self::COMPLETED,
                self::IN_PROGRESS,
                self::BLOCKED,
            ]),
            self::COMPLETED => in_array($newStatus, [
                self::IN_PROGRESS, // Reabrir tarea
            ]),
            self::BLOCKED => in_array($newStatus, [
                self::PENDING,
                self::IN_PROGRESS,
            ]),
        };
    }

    /**
     * Obtiene las transiciones disponibles desde este estado
     *
     * @return array<self>
     */
    public function availableTransitions(): array
    {
        $transitions = [];
        foreach (self::cases() as $status) {
            if ($this->canTransitionTo($status)) {
                $transitions[] = $status;
            }
        }
        return $transitions;
    }

    /**
     * Verifica si la tarea está en un estado final
     */
    public function isFinal(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Verifica si la tarea está activa (en trabajo)
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::IN_PROGRESS,
            self::REVIEW,
        ]);
    }

    /**
     * Obtiene todos los estados como array asociativo
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $status) {
            $result[$status->value] = $status->label();
        }
        return $result;
    }
}
