<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;

class OrderController extends AbstractController
{
    public function __construct(
    private Registry $workflowRegistry,
    private EntityManagerInterface $entityManager
    ) {

    }

    #[Route(path: "/order/{id}", name: "order_show")]
    public function show(Order $order): Response
    {
        $workflow = $this->workflowRegistry->get($order);

        // Получаем доступные переходы для текущего состояния заказа
        return $this->render('order/transition.html.twig', [
            'order' => $order,
            'availableTransitions' => $workflow->getEnabledTransitions($order)
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

    #[Route("/order/{id}/confirm", name: "order_confirm")]
    public function confirm(Order $order): RedirectResponse
    {
        $order->setConfirmed(true);
        $this->entityManager->flush();

        return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
    }

    #[Route(path: "/order/{id}/transition/{transition}", name: "order_transition")]
    public function transition(Order $order, string $transition): Response
    {
        $workflow = $this->workflowRegistry->get($order);

        // Применяем переход к заказу
        if ($workflow->can($order, $transition)) {
            $workflow->apply($order, $transition);
            $this->entityManager->flush();
            return new Response("Переход '{$transition}' выполнен успешно!");
        }

        return new Response("Переход '{$transition}' недоступен для текущего состояния.");
    }
}
