<?php 

namespace App\Repository;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // Game::class = 'App\Entity\Game'
        
        parent::__construct($registry, Game::class);
    }

    public function findData(int $itemCount = 20, int $page = 1, string $search = '', User $author = null): Paginator
    {
        $begin = ($page - 1) * $itemCount; // Calcul de l'offset

        $qb = $this->createQueryBuilder('g')
            ->addSelect('i, e, s')
            ->leftJoin('g.mainImage', 'i')
            ->leftJoin('g.editor', 'e')
            ->leftJoin('g.supports', 's')
            ->setMaxResults($itemCount) // LIMIT
            ->setFirstResult($begin)
        ;

        if ($search !== "") { // S'il y a une recherche
            $qb->where("g.title LIKE :search") // Ici ":search" est un paramètre défini juste après
                ->setParameter(':search', "%$search%")
            ;
        }

        if ($author !== null) {
            $qb->andWhere('g.author = :author')
                ->setParameter(':author', $author)
            ;
        }

        // Envoie la requête générée dans l'objet Paginator
        return new Paginator($qb->getQuery());
    }
}