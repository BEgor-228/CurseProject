<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ReportDataService{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    public function getReportData(array $params): array{
        $period = $params['least_period'] ?? 'month';
        $interval = $period === 'halfyear' ? '6 months' : '1 month';
        $periodType = $period === 'halfyear' ? 'полгода' : 'месяц';

        $genre = $params['genre'] ?? 'all';
        $hall = $params['hall'] ?? 'all';
        $repertoireProfit = $params['repertoire_profit'] ?? 'all';
        $repertoirePerf = $params['repertoire_perf'] ?? '';

        $conn = $this->em->getConnection();

        // 1. Least Profitable Performances
        $sql1 = "
            SELECT Performance_title, Performance_data, total_revenue, :periodType AS period_type
            FROM Least_Profitable_Performances
            WHERE Performance_data >= CURRENT_DATE - INTERVAL '$interval'
            ORDER BY total_revenue ASC
            LIMIT 10
        ";
        $stmt1 = $conn->prepare($sql1);
        $leastPerformances = $stmt1->executeQuery(['periodType' => $periodType])->fetchAllAssociative();

        // 2. Genre Revenue Report
        if ($genre !== 'all') {
            $sql2 = "
                SELECT Performance_genre, total_revenue
                FROM Genre_Revenue_Report
                WHERE Performance_genre = :genre
            ";
            $stmt2 = $conn->prepare($sql2);
            $genreRevenue = $stmt2->executeQuery(['genre' => $genre])->fetchAllAssociative();
        } else {
            $sql2 = "
                SELECT Performance_genre, total_revenue
                FROM Genre_Revenue_Report
            ";
            $stmt2 = $conn->prepare($sql2);
            $genreRevenue = $stmt2->executeQuery()->fetchAllAssociative();
        }

        // 3. Hall Ticket Report
        if ($hall !== 'all') {
            $sql3 = "
                SELECT Hall_ID, total_tickets
                FROM Hall_Tickets_Report
                WHERE Hall_ID = :hall
            ";
            $stmt3 = $conn->prepare($sql3);
            $hallTickets = $stmt3->executeQuery(['hall' => $hall])->fetchAllAssociative();
        } else {
            $sql3 = "
                SELECT 'Все залы' AS hall_label, SUM(total_tickets) AS total_tickets
                FROM Hall_Tickets_Report
            ";
            $stmt3 = $conn->prepare($sql3);
            $hallTickets = $stmt3->executeQuery()->fetchAllAssociative();
        }

        // 4. Общая прибыль репертуаров
        if ($repertoireProfit !== 'all') {
            $sql4 = "
                SELECT Repertoire_title, Total_profit
                FROM Repertoire_Profit
                WHERE Repertoire_title = :rep
            ";
            $stmt4 = $conn->prepare($sql4);
            $repertoireProfits = $stmt4->executeQuery(['rep' => $repertoireProfit])->fetchAllAssociative();
        } else {
            $sql4 = "
                SELECT Repertoire_title, Total_profit
                FROM Repertoire_Profit
            ";
            $stmt4 = $conn->prepare($sql4);
            $repertoireProfits = $stmt4->executeQuery()->fetchAllAssociative();
        }

        // 5. Прибыль по спектаклям для репертуара
        $repertoirePerformanceProfits = [];
        if ($repertoirePerf) {
            $sql5 = "
                SELECT *
                FROM Repertoire_Performance_Profit
                WHERE Repertoire_title = :rep
            ";
            $stmt5 = $conn->prepare($sql5);
            $repertoirePerformanceProfits = $stmt5->executeQuery(['rep' => $repertoirePerf])->fetchAllAssociative();
        }

        return [
            'periodType' => $periodType,
            'leastPerformances' => $leastPerformances,
            'genreRevenue' => $genreRevenue,
            'hallTickets' => $hallTickets,
            'repertoireProfits' => $repertoireProfits,
            'repertoirePerf' => $repertoirePerf,
            'repertoirePerformanceProfits' => $repertoirePerformanceProfits,
        ];
    }
}