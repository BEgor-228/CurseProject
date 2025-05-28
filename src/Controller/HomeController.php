<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ListEntity;
use App\Entity\Performance;
use App\Repository\HallRepository;
use App\Repository\PlaceRepository;
use App\Repository\TicketRepository;
use App\Repository\ViewerRepository;
use App\Repository\ManagerRepository;
use App\Repository\RepertoireRepository;
use App\Repository\AdministratorRepository;
use App\Repository\TakePerformanceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController{
    private ViewerRepository $viewerRepository;
    private ManagerRepository $managerRepository;
    private AdministratorRepository $administratorRepository;
    private HallRepository $hallRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ViewerRepository $viewerRepository,
        ManagerRepository $managerRepository,
        AdministratorRepository $administratorRepository,
        HallRepository $hallRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->viewerRepository = $viewerRepository;
        $this->managerRepository = $managerRepository;
        $this->administratorRepository = $administratorRepository;
        $this->hallRepository = $hallRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, TakePerformanceRepository $performanceRepository): Response{
        $repertoires = $performanceRepository->findAllRepertoires();
        $selectedRepertoireId = $request->query->get('repertoire');
        $selectedGenre = $request->query->get('genre');
        $selectedDate = $request->query->get('date');

        if ($selectedRepertoireId || $selectedGenre || $selectedDate) {
            $performances = $performanceRepository->findByFilters(
                $selectedRepertoireId ?: null,
                $selectedGenre ?: null,
                $selectedDate ? new \DateTime($selectedDate) : null
            );
        } else {
            $performances = $performanceRepository->findAll();
        }

        return $this->render('home.html.twig', [
            'repertoires' => $repertoires,
            'performances' => $performances,
            'selectedRepertoireId' => $selectedRepertoireId,
            'selectedGenre' => $selectedGenre,
            'selectedDate' => $selectedDate,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response{
        return $this->render('about.html.twig');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session): Response{
        $error = null;

        if ($request->isMethod('POST')) {
            $role = $request->request->get('role');
            $mail = $request->request->get('mail');
            $password = $request->request->get('password');

            $user = null;
            $roleName = '';
            $pass = null;
            $fullname = null;
            $email = null;

            switch ($role) {
                case 'viewer':
                    $user = $this->viewerRepository->findOneByEmail($mail);
                    $roleName = 'Пользователь';
                    if ($user) {
                        $pass = $user->getViewerPassword();
                        $fullname = $user->getViewerFullname();
                        $email = $user->getViewerMail();
                    }
                    break;
                case 'manager':
                    $user = $this->managerRepository->findOneByEmail($mail);
                    $roleName = 'Менеджер';
                    if ($user) {
                        $pass = $user->getManagerPassword();
                        $fullname = $user->getManagerFullname();
                        $email = $user->getManagerMail();
                    }
                    break;
                case 'admin':
                    $user = $this->administratorRepository->findOneByEmail($mail);
                    $roleName = 'Администратор';
                    if ($user) {
                        $pass = $user->getAdministratorPassword();
                        $fullname = $user->getAdministratorFullname();
                        $email = $user->getAdministratorMail();
                    }
                    break;
                default:
                    $error = 'Неверная роль.';
                    break;
            }

            if ($user && password_verify($password, $pass)) {
                $session->set('user', [
                    'fullname' => $fullname,
                    'email' => $email,
                    'role' => $role,
                ]);
                return $this->redirectToRoute('app_profile');
            } else {
                $error = $user ? 'Неверный пароль.' : 'Пользователь не найден.';
            }
        }

        return $this->render('login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(SessionInterface $session, TicketRepository $ticketRepository): Response{
        $user = $session->get('user');
        if (!$user) {
            $this->addFlash('error', 'Необходимо войти в аккаунт.');
            return $this->redirectToRoute('app_login');
        }

        $tickets = [];
        if ($user['role'] === 'viewer') {
            $viewer = $this->viewerRepository->findOneByEmail($user['email']);
            if ($viewer) {
                $tickets = $ticketRepository->findBy(['viewer' => $viewer]);
            }
        }

        return $this->render('profile.html.twig', [
            'user' => $user,
            'tickets' => $tickets,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response{
        $session->clear();
        $this->addFlash('success', 'Вы успешно вышли.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/ticketsell', name: 'app_ticketsell')]
    public function ticketSell(
        Request $request,
        SessionInterface $session,
        TakePerformanceRepository $performanceRepository,
        TicketRepository $ticketRepository,
        PlaceRepository $placeRepository
    ): Response{
        if (!$session->get('user')) {
            $this->addFlash('error', 'Необходимо войти в аккаунт.');
            return $this->redirectToRoute('app_login');
        }

        $performanceId = $request->query->get('performance');
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
        if ($hall) {
            $places = $placeRepository->findBy(['hall' => $hall]);
        }
        return $this->render('sellticket.html.twig', [
            'performance' => $performance,
            'hallNumber' => $hallNumber,
            'placesCount' => $placesCount,
            'takenPlaces' => $takenPlaces,
            'places' => $places,
        ]);
    }

    //Для редактирования спектаклей
    #[Route('/performances', name: 'app_performances', methods: ['GET', 'POST'])]
    public function performances(
        Request $request,
        EntityManagerInterface $entityManager,
        TakePerformanceRepository $performanceRepository,
        RepertoireRepository $repertoireRepository,
        HallRepository $hallRepository
    ): Response {
        $repertoires = $repertoireRepository->findAll();
        $halls = $hallRepository->findAll();
        $selectedRepertoireId = $request->get('repertoire');
        $error = null;

        // Для формы "Добавить спектакль"
        $allPerformances = $performanceRepository->findAll();
        $performances = [];
        $availablePerformances = [];

        if ($selectedRepertoireId) {
            // Получить спектакли в репертуаре
            $performances = $entityManager->createQueryBuilder()
                ->select('p')
                ->from(\App\Entity\Performance::class, 'p')
                ->join('p.lists', 'l')
                ->where('l.repertoire = :repertoireId')
                ->setParameter('repertoireId', $selectedRepertoireId)
                ->getQuery()
                ->getResult();

            // Получить спектакли, которых нет в репертуаре
            $performancesIds = array_map(fn($p) => $p->getPerformanceId(), $performances);
            $availablePerformances = array_filter($allPerformances, function($p) use ($performancesIds) {
                return !in_array($p->getPerformanceId(), $performancesIds);
            });
        }

        // Обработка POST-запроса
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $repertoire = $selectedRepertoireId ? $repertoireRepository->find($selectedRepertoireId) : null;

            // Добавить спектакль
            if ($action === 'add' && $repertoire) {
                $addPerformanceId = $request->request->get('add_performance_id');
                $performance = $performanceRepository->find($addPerformanceId);
                if ($performance) {
                    // Проверить, нет ли уже такой связи
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
                return $this->redirectToRoute('app_performances', ['repertoire' => $selectedRepertoireId]);
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
                return $this->redirectToRoute('app_performances', ['repertoire' => $selectedRepertoireId]);
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
            return $this->redirectToRoute('app_performances', ['repertoire' => $selectedRepertoireId]);
        }

        return $this->render('performances.html.twig', [
            'repertoires' => $repertoires,
            'selectedRepertoireId' => $selectedRepertoireId,
            'performances' => $performances,
            'halls' => $halls,
            'availablePerformances' => $availablePerformances,
            'error' => $error,
        ]);
    }
}