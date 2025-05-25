<?php

namespace App\Controller;

use App\Entity\Viewer;
use App\Entity\Manager;
use App\Entity\Administrator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController{
    #[Route('/register', name: 'register_select')]
    public function select(): Response{
        return $this->render('register_select.html.twig');
    }

    #[Route('/register/user', name: 'register_user')]
    public function registerUser(Request $request, EntityManagerInterface $em): Response{
        return $this->handleRegistration($request, $em, Viewer::class, 'Пользователь');
    }

    #[Route('/register/manager', name: 'register_manager')]
    public function registerManager(Request $request, EntityManagerInterface $em): Response{
        return $this->handleRegistration($request, $em, Manager::class, 'Менеджер');
    }

    #[Route('/register/admin', name: 'register_admin')]
    public function registerAdmin(Request $request, EntityManagerInterface $em): Response{
        return $this->handleRegistration($request, $em, Administrator::class, 'Администратор');
    }

    private function handleRegistration(Request $request, EntityManagerInterface $em, string $entityClass, string $roleName): Response{
        $error = null;
        $fullname = $request->request->get('fullname');
        $mail = $request->request->get('mail');
        $password = $request->request->get('password');
        $mailField = match ($entityClass) {
            \App\Entity\Viewer::class => 'viewerMail',
            \App\Entity\Manager::class => 'managerMail',
            \App\Entity\Administrator::class => 'administratorMail',
            default => 'mail'
        };

        if ($request->isMethod('POST')) {
            if (!$fullname || !$mail || !$password) {
                $error = 'Все поля обязательны для заполнения.';
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Некорректный email.';
            } elseif (strlen($password) < 6) {
                $error = 'Пароль должен быть не менее 6 символов.';
            } else {
                $repo = $em->getRepository($entityClass);
                $existing = $repo->findOneBy([$mailField => $mail]);
                if ($existing) {
                    $error = 'Пользователь с такой почтой уже существует.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $entity = new $entityClass();
                    if ($entity instanceof \App\Entity\Viewer) {
                        $entity->setViewerFullname($fullname);
                        $entity->setViewerMail($mail);
                        $entity->setViewerPassword($hashed);
                    } elseif ($entity instanceof \App\Entity\Manager) {
                        $entity->setManagerFullname($fullname);
                        $entity->setManagerMail($mail);
                        $entity->setManagerPassword($hashed);
                        // Получаем случайного администратора
                        $adminRepo = $em->getRepository(Administrator::class);
                        $admins = $adminRepo->findAll();
                        if (count($admins) > 0) {
                            $randomAdmin = $admins[array_rand($admins)];
                            $entity->setAdministrator($randomAdmin);
                        } else {
                            $error = 'Нет доступных администраторов для назначения.';
                        }
                    } elseif ($entity instanceof \App\Entity\Administrator) {
                        $entity->setAdministratorFullname($fullname);
                        $entity->setAdministratorMail($mail);
                        $entity->setAdministratorPassword($hashed);
                    }
                    if (!$error) {
                        $em->persist($entity);
                        $em->flush();
                        return $this->redirectToRoute('app_home');
                    }
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