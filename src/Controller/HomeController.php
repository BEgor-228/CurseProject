<?php

namespace App\Controller;

use App\Repository\TakePerformanceRepository;
use App\Repository\ViewerRepository;
use App\Repository\ManagerRepository;
use App\Repository\AdministratorRepository;
use App\Repository\TicketRepository;
use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController{
    private ViewerRepository $viewerRepository;
    private ManagerRepository $managerRepository;
    private AdministratorRepository $administratorRepository;

    public function __construct(
        ViewerRepository $viewerRepository,
        ManagerRepository $managerRepository,
        AdministratorRepository $administratorRepository
    ) {
        $this->viewerRepository = $viewerRepository;
        $this->managerRepository = $managerRepository;
        $this->administratorRepository = $administratorRepository;
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
                dump($session->get('user'));
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


    
}
