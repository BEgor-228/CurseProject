<?php

namespace App\Controller;

use App\Entity\Viewer;
use App\Entity\Manager;
use App\Entity\Administrator;
use App\Repository\ViewerRepository;
use App\Repository\ManagerRepository;
use App\Repository\AdministratorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController{
    #[Route('/register', name: 'register_select')]
    public function select(): Response{
        return $this->render('register_select.html.twig');
    }

    #[Route('/register/user', name: 'register_user')]
    public function registerUser(Request $request, EntityManagerInterface $em, ViewerRepository $viewerRepo): Response{
        return $this->handleRegistration($request, $em, $viewerRepo, 'viewer', 'Пользователь');
    }

    #[Route('/register/manager', name: 'register_manager')]
    public function registerManager(Request $request, EntityManagerInterface $em, ManagerRepository $managerRepo, AdministratorRepository $adminRepo): Response{
        return $this->handleRegistration($request, $em, $managerRepo, 'manager', 'Менеджер', $adminRepo);
    }

    #[Route('/register/admin', name: 'register_admin')]
    public function registerAdmin(Request $request, EntityManagerInterface $em, AdministratorRepository $adminRepo): Response{
        return $this->handleRegistration($request, $em, $adminRepo, 'admin', 'Администратор');
    }

    private function handleRegistration(
        Request $request,
        EntityManagerInterface $em,
        $repo,
        string $role,
        string $roleName,
        AdministratorRepository $adminRepo = null
    ): Response {
        $error = null;
        $fullname = $request->request->get('fullname');
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');

        $mailField = match ($role) {
            'viewer' => 'viewerMail',
            'manager' => 'managerMail',
            'admin' => 'administratorMail',
            default => throw new \InvalidArgumentException('Неверная роль.'),
        };

        if ($request->isMethod('POST')) {
            if (!$fullname || !$mail || !$password) {
                $error = 'Все поля обязательны для заполнения.';
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Некорректный email.';
            } elseif (strlen($password) < 6) {
                $error = 'Пароль должен быть не менее 6 символов.';
            } else {
                $existing = match ($role) {
                    'viewer' => $repo->findOneByEmail($mail),
                    'manager' => $repo->findOneByEmail($mail),
                    'admin' => $repo->findOneByEmail($mail),
                    default => null,
                };

                if ($existing) {
                    $error = 'Пользователь с такой почтой уже существует.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $entity = match ($role) {
                        'viewer' => (new Viewer())
                            ->setViewerFullname($fullname)
                            ->setViewerMail($mail)
                            ->setViewerPassword($hashed),
                        'manager' => (new Manager())
                            ->setManagerFullname($fullname)
                            ->setManagerMail($mail)
                            ->setManagerPassword($hashed),
                        'admin' => (new Administrator())
                            ->setAdministratorFullname($fullname)
                            ->setAdministratorMail($mail)
                            ->setAdministratorPassword($hashed),
                        default => throw new \InvalidArgumentException('Неверная роль.'),
                    };

                    if ($role === 'manager') {
                        $admins = $adminRepo->findAll();
                        if (count($admins) > 0) {
                            $randomAdmin = $admins[array_rand($admins)];
                            $entity->setAdministrator($randomAdmin);
                        } else {
                            $defaultAdmin = (new Administrator())
                                ->setAdministratorFullname('Администратор по умолчанию')
                                ->setAdministratorMail('admin@socialtheater.com')
                                ->setAdministratorPassword(password_hash('admin123', PASSWORD_DEFAULT));
                            $em->persist($defaultAdmin);
                            $em->flush();
                            $entity->setAdministrator($defaultAdmin);
                        }
                    }
                    $em->persist($entity);
                    $em->flush();
                    $this->addFlash('success', 'Регистрация прошла успешно.');
                    return $this->redirectToRoute('app_home');
                }
            }
        }

        return $this->render('register_form.html.twig', [
            'roleName' => $roleName,
            'error' => $error,
            'fullname' => $fullname,
            'mail' => $mail,
        ]);
    }
}