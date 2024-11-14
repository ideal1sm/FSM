<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', options: ['default' => 'created'])]
    private string $marking = 'created'; // Это маркировка для Symfony Workflow

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarking(): string
    {
        return $this->marking;
    }

    public function setMarking(string $marking = 'created'): Order
    {
        $this->marking = $marking;

        return $this;
    }
}
