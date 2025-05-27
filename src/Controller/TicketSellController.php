<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\TakePerformanceRepository;
use App\Repository\TicketRepository;
use App\Repository\PlaceRepository;
use App\Repository\ViewerRepository;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class TicketSellController extends AbstractController{
    #[Route('/ticketsell', name: 'app_ticketsell')]
    public function ticketSell(
        Request $request,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        TicketRepository $ticketRepository,
        PlaceRepository $placeRepository
    ): Response {
        if (!$session->get('user')) {
            $this->addFlash('error', 'Необходимо войти в аккаунт.');
            return $this->redirectToRoute('app_login');
        }

        $performanceId = $request->query->get('performance');
        $placeNumber = $request->query->get('placeNumber');

        if (!$performanceId) {
            $this->addFlash('error', 'Не выбран спектакль.');
            return $this->redirectToRoute('app_home');
        }

        $performance = $performanceRepository->find($performanceId);
        if (!$performance) {
            $this->addFlash('error', 'Спектакль не найден.');
            return $this->redirectToRoute('app_home');
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
            } else {
                $selectedPlace = null;  
            }
        }

        return $this->render('sellticket.html.twig', [
            'performance' => $performance,
            'hallNumber' => $hallNumber,
            'placesCount' => $placesCount,
            'takenPlaces' => $takenPlaces,
            'places' => $places,
            'ticketDetails' => $ticketDetails,
            'selectedPlaceNumber' => $placeNumber,
        ]);
    }

    #[Route('/ticketsell/buy', name: 'app_ticketsell_buy', methods: ['POST'])]
    public function buyTicket(
        Request $request,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        PlaceRepository $placeRepository,
        ViewerRepository $viewerRepository,
        TicketRepository $ticketRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$session->get('user')) {
            return new JsonResponse(['success' => false, 'message' => 'Необходимо войти в аккаунт.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $performanceId = $data['performanceId'] ?? null;
        $placeNumber = $data['placeNumber'] ?? null;
        $hallNumber = $data['hallNumber'] ?? null;
        $price = $data['price'] ?? null;

        if (!$performanceId || !$placeNumber || !$hallNumber || !$price) {
            return new JsonResponse(['success' => false, 'message' => 'Недостаточно данных для покупки билета.'], 400);
        }

        $performance = $performanceRepository->find($performanceId);
        if (!$performance) {
            return new JsonResponse(['success' => false, 'message' => 'Спектакль не найден.'], 404);
        }

        $hall = $performance->getHall();
        if (!$hall || $hall->getHallId() != $hallNumber) {
            return new JsonResponse(['success' => false, 'message' => 'Зал не найден или не соответствует спектаклю.'], 404);
        }

        $place = $placeRepository->findOneByHallAndNumber($hall, $placeNumber);
        if (!$place) {
            return new JsonResponse(['success' => false, 'message' => 'Место не найдено.'], 404);
        }

        $existingTicket = $ticketRepository->findOneBy([
            'performance' => $performance,
            'place' => $place
        ]);
        if ($existingTicket) {
            return new JsonResponse(['success' => false, 'message' => 'Место уже занято.'], 400);
        }

        $user = $session->get('user');
        $viewer = $viewerRepository->findOneByEmail($user['email']);
        if (!$viewer) {
            return new JsonResponse(['success' => false, 'message' => 'Пользователь не найден.'], 404);
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
                return new JsonResponse(['success' => false, 'message' => 'Не удалось сгенерировать уникальный код билета.'], 500);
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

        return new JsonResponse(['success' => true, 'message' => 'Билет успешно куплен.', 'ticketCode' => $ticketCode]);
    }
}