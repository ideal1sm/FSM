<?php

namespace App\StateMachine;

use App\Entity\Order;

class OrderStateMachine
{
    private const STATE_CREATED = 'created';
    private const STATE_PAID = 'paid';
    private const STATE_SHIPPED = 'shipped';
    private const STATE_DELIVERED = 'delivered';

    private const TRANSITION_PAY = 'pay';
    private const TRANSITION_SHIP = 'ship';
    private const TRANSITION_DELIVER = 'deliver';

    // Определение всех переходов и допустимых состояний
    private array $transitions = [
        self::TRANSITION_PAY => [self::STATE_CREATED => self::STATE_PAID],
        self::TRANSITION_SHIP => [self::STATE_PAID => self::STATE_SHIPPED],
        self::TRANSITION_DELIVER => [self::STATE_SHIPPED => self::STATE_DELIVERED],
    ];

    public function applyTransition(Order $order, string $transition): bool
    {
        $currentState = $order->getState();

        // Проверка, возможен ли переход из текущего состояния
        if (isset($this->transitions[$transition][$currentState])) {
            $newState = $this->transitions[$transition][$currentState];
            $order->setState($newState);

            // Возможно добавление коллбэков, логгирования, уведомлений
            return true;
        }

        // Переход не возможен
        return false;
    }

    public function getAvailableTransitions(string $currentState): array
    {
        $available = [];

        foreach ($this->transitions as $transition => $states) {
            if (isset($states[$currentState])) {
                $available[] = $transition;
            }
        }

        return $available;
    }

    public function getUnAvailableTransitions(string $currentState): array
    {
        $unAvailable = [];

        foreach ($this->transitions as $transition => $states) {
            if (!isset($states[$currentState])) {
                $unAvailable[] = $transition;
            }
        }

        return $unAvailable;
    }
}