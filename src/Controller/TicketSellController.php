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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TicketSellService;

class TicketSellController extends AbstractController{
    private TicketSellService $ticketSellService;

    public function __construct(TicketSellService $ticketSellService){
        $this->ticketSellService = $ticketSellService;
    }

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
        $data = $this->ticketSellService->getTicketSellData(
            $request,
            $session,
            $performanceRepository,
            $ticketRepository,
            $placeRepository
        );
        if (isset($data['error'])) {
            $this->addFlash('error', $data['error']);
            return $this->redirectToRoute('app_home');
        }
        return $this->render('sellticket.html.twig', $data);
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
        $result = $this->ticketSellService->buyTicket(
            $data,
            $session,
            $performanceRepository,
            $placeRepository,
            $viewerRepository,
            $ticketRepository,
            $entityManager
        );
        $status = ($result['success'] ?? false) ? 200 : 400;
        return new JsonResponse($result, $status);
    }
}