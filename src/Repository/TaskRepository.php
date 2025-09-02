<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

      /**
     * @return Task[]
     */
    // public function findDoneTasks(): array
    // {
    //     return $this->createQueryBuilder('t')
    //     ->where('t.isDone = :done')
    //     ->setParameter('done', true)
    //     ->getQuery()
    //     ->getResult();
    // }

    public function findByValueDoneTasks(bool $v_done): array
    {   
        return $this->findBy(['isDone' => $v_done]);
    }

    public function findByAuthor(\App\Entity\User $user): array
{
    return $this->findBy(['author' => $user]);
}
}