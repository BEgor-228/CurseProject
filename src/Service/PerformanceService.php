<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ListEntity;
use App\Repository\TakePerformanceRepository;
use App\Repository\RepertoireRepository;
use App\Repository\HallRepository;
use App\Repository\TicketRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PerformanceService{
    public function getTicketSellData(
        $request,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        TicketRepository $ticketRepository,
        PlaceRepository $placeRepository
    ) {
        if (!$session->get('user')) {
            return ['redirect' => true, 'route' => 'app_login'];
        }
        $performanceId = $request->query->get('performance');
        if (!$performanceId) {
            return ['redirect' => true, 'route' => 'app_home', 'error' => 'Не выбран спектакль.'];
        }
        $performance = $performanceRepository->find($performanceId);
        if (!$performance) {
            return ['redirect' => true, 'route' => 'app_home', 'error' => 'Спектакль не найден.'];
        }
        $hall = $performance->getHall();
        $hallNumber = $hall ? $hall->getHallId() : '—';
        $placesCount = $hall ? $hall->getHallCapacity() : 0;
        $tickets = $ticketRepository->findBy(['performance' => $performance]);
        $takenPlaces = [];
        foreach ($tickets as $ticket) {
            $place = $ticket->getPlace();
            if ($place) {
                $takenPlaces[] = $place->getPlaceNumber();
            }
        }
        $places = [];
        if ($hall) {
            $places = $placeRepository->findBy(['hall' => $hall]);
        }
        return [
            'performance' => $performance,
            'hallNumber' => $hallNumber,
            'placesCount' => $placesCount,
            'takenPlaces' => $takenPlaces,
            'places' => $places,
        ];
    }

    public function getPerformancesData(
        $request,
        EntityManagerInterface $entityManager,
        TakePerformanceRepository $performanceRepository,
        RepertoireRepository $repertoireRepository,
        HallRepository $hallRepository
    ) {
        $repertoires = $repertoireRepository->findAll();
        $halls = $hallRepository->findAll();
        $selectedRepertoireId = $request->get('repertoire');
        $error = null;
        $allPerformances = $performanceRepository->findAll();
        $performances = [];
        $availablePerformances = [];

        if ($selectedRepertoireId) {
            $performances = $entityManager->createQueryBuilder()
                ->select('p')
                ->from(\App\Entity\Performance::class, 'p')
                ->join('p.lists', 'l')
                ->where('l.repertoire = :repertoireId')
                ->setParameter('repertoireId', $selectedRepertoireId)
                ->getQuery()
                ->getResult();
            $performancesIds = array_map(fn($p) => $p->getPerformanceId(), $performances);
            $availablePerformances = array_filter($allPerformances, function($p) use ($performancesIds) {
                return !in_array($p->getPerformanceId(), $performancesIds);
            });
        }

        return [
            'repertoires' => $repertoires,
            'halls' => $halls,
            'selectedRepertoireId' => $selectedRepertoireId,
            'performances' => $performances,
            'availablePerformances' => $availablePerformances,
            'error' => $error,
        ];
    }

    public function handlePerformancesPost(
        Request $request,
        EntityManagerInterface $entityManager,
        TakePerformanceRepository $performanceRepository,
        RepertoireRepository $repertoireRepository,
        HallRepository $hallRepository
    ): ?array {
        $selectedRepertoireId = $request->get('repertoire');
        $action = $request->request->get('action');
        $repertoire = $selectedRepertoireId ? $repertoireRepository->find($selectedRepertoireId) : null;

        // Добавить спектакль
        if ($action === 'add' && $repertoire) {
            $addPerformanceId = $request->request->get('add_performance_id');
            $performance = $performanceRepository->find($addPerformanceId);
            if ($performance) {
                $existing = $entityManager->getRepository(ListEntity::class)->findOneBy([
                    'performance' => $performance,
                    'repertoire' => $repertoire
                ]);
                if (!$existing) {
                    $listEntity = new ListEntity();
                    $listEntity->setPerformance($performance);
                    $listEntity->setRepertoire($repertoire);
                    $entityManager->persist($listEntity);
                    $entityManager->flush();
                }
            }
            return ['redirect' => true, 'repertoire' => $selectedRepertoireId];
        }

        // Удалить спектакль
        if (str_starts_with($action, 'delete_') && $repertoire) {
            $deleteId = (int)str_replace('delete_', '', $action);
            $performance = $performanceRepository->find($deleteId);
            if ($performance) {
                $listEntity = $entityManager->getRepository(ListEntity::class)->findOneBy([
                    'performance' => $performance,
                    'repertoire' => $repertoire
                ]);
                if ($listEntity) {
                    $entityManager->remove($listEntity);
                    $entityManager->flush();
                }
            }
            return ['redirect' => true, 'repertoire' => $selectedRepertoireId];
        }

        // Сохранить изменения спектаклей
        $performancesData = $request->request->all('performances');
        foreach ($performancesData as $id => $fields) {
            $performance = $performanceRepository->find($id);
            if ($performance) {
                $performance->setPerformanceTitle($fields['title']);
                $performance->setPerformanceDescription($fields['description']);
                $performance->setPerformanceCastList($fields['castList']);
                $performance->setPerformanceDuration(new \DateTime($fields['duration']));
                $performance->setPerformanceData(new \DateTime($fields['date']));
                $performance->setPerformancePrice($fields['price']);
                $performance->setPerformanceGenre($fields['genre']);
                $hall = $hallRepository->find($fields['hall']);
                if ($hall) {
                    $performance->setHall($hall);
                }
                $entityManager->persist($performance);
            }
        }
        $entityManager->flush();
        return ['redirect' => true, 'repertoire' => $selectedRepertoireId];
    }
}