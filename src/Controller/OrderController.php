<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\OrderWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderWorkflowService $workflowService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route(path: "/order/{id}", name: "order_show")]
    public function show(Order $order): Response
    {
        // Получаем доступные переходы для текущего состояния заказа
        return $this->render('order/transition.html.twig', [
            'order' => $order,
            'availableTransitions' => $this->workflowService->getEnabledTransitions($order)
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
        if ($this->workflowService->applyTransition($order, $transition)) {
            $this->entityManager->flush();
            return new Response("Переход '{$transition}' выполнен успешно!");
        }

        return new Response("Переход '{$transition}' недоступен для текущего состояния.");
    }
}
