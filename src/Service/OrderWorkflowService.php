<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderWorkflowService
{
    private WorkflowInterface $workflow;

    public function __construct(WorkflowInterface $orderProcessStateMachine)
    {
        $this->workflow = $orderProcessStateMachine;
    }

    public function canTransition(Order $order, string $transition): bool
    {
        return $this->workflow->can($order, $transition);
    }

    public function applyTransition(Order $order, string $transition): bool
    {
        if ($this->workflow->can($order, $transition)) {
            $this->workflow->apply($order, $transition);
            return true;
        }

        return false;
    }

    public function getEnabledTransitions(Order $order): array
    {
        return $this->workflow->getEnabledTransitions($order);
    }
}