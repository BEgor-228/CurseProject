<?php

namespace App\Controller;

use App\Repository\TakePerformanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, TakePerformanceRepository $performanceRepository): Response{
        $repertoires = $performanceRepository->findAllRepertoires();
        $selectedRepertoireId = $request->query->get('repertoire');
        $selectedGenre = $request->query->get('genre');
        $selectedDate = $request->query->get('date');

        // Если не выбран ни один фильтр, показываем все спектакли
        if ($selectedRepertoireId || $selectedGenre || $selectedDate) {
            $performances = $performanceRepository->findByFilters(
                $selectedRepertoireId ?: null,
                $selectedGenre ?: null,
                $selectedDate ?: null
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
}