<?php

namespace App\Controller;

use App\Service\HomeService;
use App\Service\PerformanceService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TakePerformanceRepository;
use App\Repository\RepertoireRepository;
use App\Repository\HallRepository;
use App\Repository\TicketRepository;
use App\Repository\ManagerRepository;
use App\Repository\CommandRepository;
use App\Repository\AdministratorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
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
    public function index(Request $request, TakePerformanceRepository $performanceRepository): Response
    {
        $data = $this->homeService->getHomeData($request, $performanceRepository);
        return $this->render('home.html.twig', $data);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session): Response
    {
        $result = $this->homeService->handleLogin($request, $session);
        if (isset($result['redirect']) && $result['redirect']) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('login.html.twig', [
            'error' => $result['error'] ?? null,
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(
        SessionInterface $session,
        TicketRepository $ticketRepository,
        CommandRepository $commandRepository,
        ManagerRepository $managerRepository,
        RepertoireRepository $repertoireRepository,
        TakePerformanceRepository $performanceRepository,
        AdministratorRepository $administratorRepository
    ): Response {
        $result = $this->homeService->getProfileData($session, $ticketRepository);

        $userData = $session->get('user');
        $commands = [];
        $managers = [];
        $currentAdminId = null;
        $adminPerformances = [];
        $adminManagers = [];
        $administrators = [];

        if ($userData && $userData['role'] === 'manager') {
            $manager = $managerRepository->findOneBy(['managerMail' => $userData['email']]);
            if ($manager) {
                $commands = $commandRepository->findBy(['manager' => $manager]);
            }
        }
        if ($userData && $userData['role'] === 'admin') {
            // Получаем администратора по email
            $admin = $this->entityManager->getRepository(\App\Entity\Administrator::class)
                ->findOneBy(['administratorMail' => $userData['email']]);
            if ($admin) {
                $currentAdminId = $admin->getAdministratorId();
                // Все команды, отправленные этим администратором
                $commands = $commandRepository->findBy(['administrator' => $admin]);
                // Все менеджеры, подчинённые этому админу
                $managers = $managerRepository->findBy(['administrator' => $admin]);
                // Получить все репертуары этого администратора
                $repertoires = $repertoireRepository->findBy(['administrator' => $currentAdminId]);
                // Получить все записи ListEntity для этих репертуаров
                $listRepository = $this->entityManager->getRepository(\App\Entity\ListEntity::class);
                $lists = $listRepository->findBy(['repertoire' => $repertoires]);
                // Собрать все спектакли
                foreach ($lists as $list) {
                    $performance = $list->getPerformance();
                    if ($performance) {
                        $adminPerformances[] = $performance;
                    }
                }
                // Получить всех менеджеров этого администратора
                $adminManagers = $managerRepository->findBy(['administrator' => $currentAdminId]);
                $administrators = $administratorRepository->findAll();
            }
        }

        $result['admin_commands'] = $commands;
        $result['admin_managers'] = $managers;
        $result['commands'] = $commands;
        $result['admin_performances'] = $adminPerformances;
        $result['admin_managers'] = $adminManagers;
        $result['administrators'] = $administrators;
        $result['currentAdminId'] = $currentAdminId;

        if (isset($result['redirect']) && $result['redirect']) {
            $this->addFlash('error', 'Необходимо войти в аккаунт.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile.html.twig', $result);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', 'Вы успешно вышли.');
        return $this->redirectToRoute('app_home');
    }

    //Для редактирования спектаклей
    #[Route('/performances', name: 'app_performances', methods: ['GET', 'POST'])]
    public function performances(
        Request $request,
        TakePerformanceRepository $performanceRepository,
        RepertoireRepository $repertoireRepository,
        HallRepository $hallRepository,
        SessionInterface $session,
        ManagerRepository $managerRepository
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

        $userData = $session->get('user');
        $user = null;
        if ($userData && $userData['role'] === 'manager') {
            $user = $managerRepository->findOneBy(['managerMail' => $userData['email']]);
        }
        $data = $this->performanceService->getPerformancesData(
            $request,
            $this->entityManager,
            $performanceRepository,
            $repertoireRepository,
            $hallRepository
        );
        $data['user'] = $user;
        return $this->render('performances.html.twig', $data);
    }

    #[Route('/changeRepertoire', name: 'app_change_repertoire', methods: ['GET', 'POST'])]
    public function changeRepertoire(
        Request $request,
        RepertoireRepository $repertoireRepository,
        AdministratorRepository $administratorRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        $user = $session->get('user');
        $currentAdminId = null;
        if ($user && $user['role'] === 'admin') {
            $admin = $entityManager->getRepository(\App\Entity\Administrator::class)->findOneBy(['administratorMail' => $user['email']]);
            if ($admin) {
                $currentAdminId = $admin->getAdministratorId();
            }
        }
        $repertoires = $currentAdminId
            ? $repertoireRepository->findBy(['administrator' => $currentAdminId])
            : [];
        $administrators = $administratorRepository->findAll();
        $error = null;

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if (isset($data['repertoires'])) {
                foreach ($data['repertoires'] as $id => $fields) {
                    $rep = $repertoireRepository->find($id);
                    if ($rep) {
                        $rep->setRepertoireTitle($fields['title']);
                        $rep->setRepertoireSize((int)$fields['size']);
                        $administrator = $administratorRepository->find($fields['administrator']);
                        if ($administrator) {
                            $rep->setAdministrator($administrator);
                        }
                        $entityManager->persist($rep);
                    }
                }
            }
            if (
                !empty($data['new_title']) &&
                !empty($data['new_size']) &&
                !empty($data['new_administrator'])
            ) {
                $newRep = new \App\Entity\Repertoire();
                $newRep->setRepertoireTitle($data['new_title']);
                $newRep->setRepertoireSize((int)$data['new_size']);
                $administrator = $administratorRepository->find($data['new_administrator']);
                if ($administrator) {
                    $newRep->setAdministrator($administrator);
                    $entityManager->persist($newRep);
                } else {
                    $error = 'Выберите администратора для нового репертуара!';
                }
            }
            elseif (
                !empty($data['new_title']) ||
                !empty($data['new_size']) ||
                !empty($data['new_administrator'])
            ) {
                $error = 'Для добавления нового репертуара заполните все поля!';
            }

            $entityManager->flush();
            if (!$error) {
                return $this->redirectToRoute('app_change_repertoire');
            }
        }

        return $this->render('changeRepertoire.html.twig', [
            'repertoires' => $repertoires,
            'administrators' => $administrators,
            'error' => $error,
            'currentAdminId' => $currentAdminId,
        ]);
    }

    #[Route('/command/delete/{id}', name: 'command_delete', methods: ['POST'])]
    public function deleteCommand(
        int $id,
        CommandRepository $commandRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        $command = $commandRepository->find($id);
        if ($command) {
            $entityManager->remove($command);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/command/add', name: 'command_add', methods: ['POST'])]
    public function addCommand(
        Request $request,
        SessionInterface $session,
        CommandRepository $commandRepository,
        ManagerRepository $managerRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $userData = $session->get('user');
        if ($userData && $userData['role'] === 'admin') {
            $admin = $entityManager->getRepository(\App\Entity\Administrator::class)
                ->findOneBy(['administratorMail' => $userData['email']]);
            $content = $request->request->get('command_content');
            $managerId = $request->request->get('manager_id');
            $manager = $managerRepository->find($managerId);
            if ($admin && $manager && $content) {
                $command = new \App\Entity\Command();
                $command->setCommandContent($content);
                $command->setAdministrator($admin);
                $command->setManager($manager);
                $entityManager->persist($command);
                $entityManager->flush();
            }
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/admin/performances/save', name: 'admin_performances_save', methods: ['POST'])]
    public function adminPerformancesSave(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        ManagerRepository $managerRepository,
        TakePerformanceRepository $performanceRepository
    ): Response {
        $userData = $session->get('user');
        if (!$userData || $userData['role'] !== 'admin') {
            $this->addFlash('error', 'Доступ запрещён.');
            return $this->redirectToRoute('app_profile');
        }

        $data = $request->request->all('performances');
        if ($data) {
            foreach ($data as $performanceId => $fields) {
                $performance = $performanceRepository->find($performanceId);
                if ($performance && isset($fields['manager'])) {
                    $manager = $managerRepository->find($fields['manager']);
                    if ($manager) {
                        $performance->setManager($manager);
                        $entityManager->persist($performance);
                    }
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Менеджеры спектаклей обновлены.');
        }
        return $this->redirectToRoute('app_profile');
    }
}