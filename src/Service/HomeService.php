<?php

namespace App\Service;

use App\Repository\TakePerformanceRepository;
use App\Repository\ViewerRepository;
use App\Repository\ManagerRepository;
use App\Repository\AdministratorRepository;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeService{
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

    public function getHomeData($request, TakePerformanceRepository $performanceRepository){
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
        return [
            'repertoires' => $repertoires,
            'performances' => $performances,
            'selectedRepertoireId' => $selectedRepertoireId,
            'selectedGenre' => $selectedGenre,
            'selectedDate' => $selectedDate,
        ];
    }

    public function handleLogin($request, SessionInterface $session){
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
                return ['redirect' => true];
            } else {
                $error = $user ? 'Неверный пароль.' : 'Пользователь не найден.';
            }
        }
        return ['error' => $error];
    }
    public function getProfileData(SessionInterface $session, TicketRepository $ticketRepository){
        $user = $session->get('user');
        if (!$user) {
            return ['redirect' => true];
        }
        $tickets = [];
        if ($user['role'] === 'viewer') {
            $viewer = $this->viewerRepository->findOneByEmail($user['email']);
            if ($viewer) {
                $tickets = $ticketRepository->findBy(['viewer' => $viewer]);
            }
        }
        return [
            'user' => $user,
            'tickets' => $tickets,
        ];
    }
}