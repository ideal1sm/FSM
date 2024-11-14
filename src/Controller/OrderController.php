<?php

namespace App\Controller;

use App\Entity\Order;
use App\StateMachine\OrderStateMachine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    private OrderStateMachine $stateMachine;
    private EntityManagerInterface $entityManager;

    public function __construct(OrderStateMachine $stateMachine, EntityManagerInterface $entityManager)
    {
        $this->stateMachine = $stateMachine;
        $this->entityManager = $entityManager;
    }

    #[Route(path: "/order/{id}", name: "order_show")]
    public function show(Order $order): Response
    {
        // Получаем доступные переходы для текущего состояния заказа
        return $this->render('order/transition.html.twig', [
            'order' => $order,
            'availableTransitions' => $this->stateMachine->getAvailableTransitions($order->getState()),
            'unAvailableTransitions' => $this->stateMachine->getUnAvailableTransitions($order->getState()),
        ]);
    }

    #[Route(path: "/orders", name: "order_list")]
    public function list(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findAll();

        return $this->render('order/list.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route(path: "/order/{id}/transition/{transition}", name: "order_transition")]
    public function transition(Order $order, string $transition): Response
    {
        // Применяем переход к заказу
        if ($this->stateMachine->applyTransition($order, $transition)) {
            $this->entityManager->flush();
            return new Response("Переход '{$transition}' выполнен успешно!");
        }

        return new Response("Переход '{$transition}' недоступен для текущего состояния.");
    }
}
