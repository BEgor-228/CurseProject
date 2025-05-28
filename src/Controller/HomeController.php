<?php

namespace App\Controller;

use App\Service\HomeService;
use App\Service\PerformanceService;
use Doctrine\ORM\EntityManagerInterface;
// use App\Entity\ListEntity;
use App\Repository\TakePerformanceRepository;
use App\Repository\RepertoireRepository;
use App\Repository\HallRepository;
use App\Repository\TicketRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController{
    private HomeService $homeService;
    private PerformanceService $performanceService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        HomeService $homeService,
        PerformanceService $performanceService,
        EntityManagerInterface $entityManager
    ) {
        $this->homeService = $homeService;
        $this->performanceService = $performanceService;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, TakePerformanceRepository $performanceRepository): Response{
        $data = $this->homeService->getHomeData($request, $performanceRepository);
        return $this->render('home.html.twig', $data);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response{
        return $this->render('about.html.twig');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session): Response{
        $result = $this->homeService->handleLogin($request, $session);
        if (isset($result['redirect']) && $result['redirect']) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('login.html.twig', [
            'error' => $result['error'] ?? null,
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(SessionInterface $session, TicketRepository $ticketRepository): Response{
        $result = $this->homeService->getProfileData($session, $ticketRepository);
        if (isset($result['redirect']) && $result['redirect']) {
            $this->addFlash('error', 'Необходимо войти в аккаунт.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('profile.html.twig', $result);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response{
        $session->clear();
        $this->addFlash('success', 'Вы успешно вышли.');
        return $this->redirectToRoute('app_home');
    }

    // #[Route('/ticketsell', name: 'app_ticketsell')]
    // public function ticketSell(
    //     Request $request,
    //     SessionInterface $session,
    //     TakePerformanceRepository $performanceRepository,
    //     TicketRepository $ticketRepository,
    //     PlaceRepository $placeRepository
    // ): Response{
    //     if (!$session->get('user')) {
    //         $this->addFlash('error', 'Необходимо войти в аккаунт.');
    //         return $this->redirectToRoute('app_login');
    //     }
    //     $performanceId = $request->query->get('performance');
    //     if (!$performanceId) {
    //         $this->addFlash('error', 'Не выбран спектакль.');
    //         return $this->redirectToRoute('app_home');
    //     }
    //     $performance = $performanceRepository->find($performanceId);
    //     if (!$performance) {
    //         $this->addFlash('error', 'Спектакль не найден.');
    //         return $this->redirectToRoute('app_home');
    //     }
    //     $hall = $performance->getHall();
    //     $hallNumber = $hall ? $hall->getHallId() : '—';
    //     $placesCount = $hall ? $hall->getHallCapacity() : 0;
    //     $tickets = $ticketRepository->findBy(['performance' => $performance]);
    //     $takenPlaces = [];
    //     foreach ($tickets as $ticket) {
    //         $place = $ticket->getPlace();
    //         if ($place) {
    //             $takenPlaces[] = $place->getPlaceNumber();
    //         }
    //     }
    //     $places = [];
    //     if ($hall) {
    //         $places = $placeRepository->findBy(['hall' => $hall]);
    //     }
    //     return $this->render('sellticket.html.twig', [
    //         'performance' => $performance,
    //         'hallNumber' => $hallNumber,
    //         'placesCount' => $placesCount,
    //         'takenPlaces' => $takenPlaces,
    //         'places' => $places,
    //     ]);
    // }

    //Для редактирования спектаклей
    #[Route('/performances', name: 'app_performances', methods: ['GET', 'POST'])]
    public function performances(
        Request $request,
        TakePerformanceRepository $performanceRepository,
        RepertoireRepository $repertoireRepository,
        HallRepository $hallRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $result = $this->performanceService->handlePerformancesPost(
                $request,
                $this->entityManager,
                $performanceRepository,
                $repertoireRepository,
                $hallRepository
            );
            if ($result && $result['redirect']) {
                return $this->redirectToRoute('app_performances', ['repertoire' => $result['repertoire']]);
            }
        }
        $data = $this->performanceService->getPerformancesData(
            $request,
            $this->entityManager,
            $performanceRepository,
            $repertoireRepository,
            $hallRepository
        );
        return $this->render('performances.html.twig', $data);
    }
}