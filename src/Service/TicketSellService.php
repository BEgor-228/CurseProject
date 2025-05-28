<?php

namespace App\Service;

use App\Repository\TakePerformanceRepository;
use App\Repository\TicketRepository;
use App\Repository\PlaceRepository;
use App\Repository\ViewerRepository;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TicketSellService{
    public function getTicketSellData(
        Request $request,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        TicketRepository $ticketRepository,
        PlaceRepository $placeRepository
    ): array {
        $performanceId = $request->query->get('performance');
        $placeNumber = $request->query->get('placeNumber');

        $performance = $performanceRepository->find($performanceId);
        if (!$performance) {
            return ['error' => 'Спектакль не найден.'];
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
        $selectedPlace = null;
        $ticketDetails = null;
        if ($hall) {
            $places = $placeRepository->findBy(['hall' => $hall]);
            if ($placeNumber) {
                $selectedPlace = $placeRepository->findOneByHallAndNumber($hall, $placeNumber);
                if ($selectedPlace && !in_array($placeNumber, $takenPlaces)) {
                    $status = $selectedPlace->getPlaceStatus();
                    switch ($status) {
                        case 'Партер': $placePrice = 300; break;
                        case 'Амфитеатр': $placePrice = 500; break;
                        case 'Ложа': $placePrice = 400; break;
                        default: $placePrice = 0;
                    }
                    $ticketDetails = [
                        'title' => $performance->getPerformanceTitle(),
                        'genre' => $performance->getPerformanceGenre(),
                        'duration' => $performance->getPerformanceDuration(),
                        'hallNumber' => $hallNumber,
                        'placeNumber' => $placeNumber,
                        'placeStatus' => $status,
                        'perfPrice' => $performance->getPerformancePrice(),
                        'placePrice' => $placePrice,
                        'totalPrice' => $performance->getPerformancePrice() + $placePrice,
                        'performanceId' => $performanceId,
                    ];
                }
            }
        }

        return [
            'performance' => $performance,
            'hallNumber' => $hallNumber,
            'placesCount' => $placesCount,
            'takenPlaces' => $takenPlaces,
            'places' => $places,
            'ticketDetails' => $ticketDetails,
            'selectedPlaceNumber' => $placeNumber,
        ];
    }

    public function buyTicket(
        array $data,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        PlaceRepository $placeRepository,
        ViewerRepository $viewerRepository,
        TicketRepository $ticketRepository,
        EntityManagerInterface $entityManager
    ): array {
        $performanceId = $data['performanceId'] ?? null;
        $placeNumber = $data['placeNumber'] ?? null;
        $hallNumber = $data['hallNumber'] ?? null;
        $price = $data['price'] ?? null;

        if (!$performanceId || !$placeNumber || !$hallNumber || !$price) {
            return ['success' => false, 'message' => 'Недостаточно данных для покупки билета.'];
        }

        $performance = $performanceRepository->find($performanceId);
        if (!$performance) {
            return ['success' => false, 'message' => 'Спектакль не найден.'];
        }

        $hall = $performance->getHall();
        if (!$hall || $hall->getHallId() != $hallNumber) {
            return ['success' => false, 'message' => 'Зал не найден или не соответствует спектаклю.'];
        }

        $place = $placeRepository->findOneByHallAndNumber($hall, $placeNumber);
        if (!$place) {
            return ['success' => false, 'message' => 'Место не найдено.'];
        }

        $existingTicket = $ticketRepository->findOneBy([
            'performance' => $performance,
            'place' => $place
        ]);
        if ($existingTicket) {
            return ['success' => false, 'message' => 'Место уже занято.'];
        }

        $user = $session->get('user');
        $viewer = $viewerRepository->findOneByEmail($user['email']);
        if (!$viewer) {
            return ['success' => false, 'message' => 'Пользователь не найден.'];
        }

        // Генерация случайного 10-значного кода из букв и цифр
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxAttempts = 10;
        $attempt = 0;
        do {
            $ticketCode = '';
            for ($i = 0; $i < 10; $i++) {
                $ticketCode .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $existingCode = $ticketRepository->findOneBy(['ticketCode' => $ticketCode]);
            $attempt++;
            if ($attempt >= $maxAttempts) {
                return ['success' => false, 'message' => 'Не удалось сгенерировать уникальный код билета.'];
            }
        } while ($existingCode);

        $ticket = new Ticket();
        $ticket->setViewer($viewer);
        $ticket->setPerformance($performance);
        $ticket->setHall($hall);
        $ticket->setPlace($place);
        $ticket->setPrice((int)$price);
        $ticket->setTicketPurchaseDate(new \DateTime());
        $ticket->setTicketCode($ticketCode);

        $entityManager->persist($ticket);
        $entityManager->flush();

        return ['success' => true, 'message' => 'Билет успешно куплен.', 'ticketCode' => $ticketCode];
    }
}