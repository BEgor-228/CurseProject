<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Service\ReportDataService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController{
    private ReportDataService $reportDataService;

    public function __construct(ReportDataService $reportDataService){
        $this->reportDataService = $reportDataService;
    }

    #[Route('/admin/least-profitable-pdf', name: 'admin_least_profitable_pdf', methods: ['POST'])]
    public function adminLeastProfitablePdf(Request $request): Response{
        $data = $this->reportDataService->getReportData($request->request->all());
        extract($data);

        // Формируем HTML для PDF
        $html = '<h2 style="text-align:center;">10 наименее доходных спектаклей за ' . $periodType . '</h2>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr><th>Название</th><th>Дата</th><th>Доход</th><th>Период</th></tr></thead><tbody>';
        foreach ($leastPerformances as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['performance_title'] ?? $row['Performance_title'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['performance_data'] ?? $row['Performance_data'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['total_revenue'] ?? $row['Total_revenue'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['period_type'] ?? $row['Period_type'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Таблица по жанру
        $html .= '<h2 style="text-align:center; margin-top:30px;">Общий доход по жанру</h2>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr><th>Жанр</th><th>Доход</th></tr></thead><tbody>';
        foreach ($genreRevenue as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['performance_genre'] ?? $row['Performance_genre'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['total_revenue'] ?? $row['Total_revenue'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Таблица по залу
        $html .= '<h2 style="text-align:center; margin-top:30px;">Продажи билетов для зала</h2>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr><th>Зал</th><th>Продано билетов</th></tr></thead><tbody>';
        foreach ($hallTickets as $row) {
            $html .= '<tr>';
            if (isset($row['hall_label'])) {
                $html .= '<td>' . htmlspecialchars($row['hall_label']) . '</td>';
            } elseif (isset($row['Hall_label'])) {
                $html .= '<td>' . htmlspecialchars($row['Hall_label']) . '</td>';
            } elseif (isset($row['Hall_ID'])) {
                $html .= '<td>' . 'Зал №' . htmlspecialchars($row['Hall_ID']) . '</td>';
            } elseif (isset($row['hall_id'])) {
                $html .= '<td>' . 'Зал №' . htmlspecialchars($row['hall_id']) . '</td>';
            } else {
                $html .= '<td></td>';
            }
            $html .= '<td>' . htmlspecialchars($row['total_tickets'] ?? $row['Total_tickets'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Общая прибыль репертуаров
        $html .= '<h2 style="text-align:center; margin-top:30px;">Общая прибыль репертуаров</h2>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr><th>Репертуар</th><th>Прибыль</th></tr></thead><tbody>';
        foreach ($repertoireProfits as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['repertoire_title'] ?? $row['Repertoire_title'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['total_profit'] ?? $row['Total_profit'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Прибыль по спектаклям для репертуара
        if ($repertoirePerf) {
            $html .= '<h2 style="text-align:center; margin-top:30px;">Прибыль по спектаклям для репертуара: ' . htmlspecialchars($repertoirePerf) . '</h2>';
            $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">';
            $html .= '<thead><tr><th>Репертуар</th><th>№</th><th>Спектакль</th><th>Прибыль</th></tr></thead><tbody>';
            foreach ($repertoirePerformanceProfits as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['repertoire_title'] ?? $row['Repertoire_title'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['performance_number'] ?? $row['Performance_number'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['performance_title'] ?? $row['Performance_title'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['ticket_profit'] ?? $row['Ticket_profit'] ?? '') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Генерация PDF через Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="analytics_report.pdf"',
            ]
        );
    }

    #[Route('/admin/least-profitable-excel', name: 'admin_least_profitable_excel', methods: ['POST'])]
    public function adminLeastProfitableExcel(Request $request): Response{
        $data = $this->reportDataService->getReportData($request->request->all());
        extract($data);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowNum = 1;

        $sheet->setCellValue('A'.$rowNum, '10 наименее доходных спектаклей за ' . $periodType);
        $rowNum += 2;
        $sheet->fromArray(['Название', 'Дата', 'Доход', 'Период'], null, 'A'.$rowNum);
        $rowNum++;
        foreach ($leastPerformances as $row) {
            $sheet->fromArray([
                $row['performance_title'] ?? $row['Performance_title'] ?? '',
                $row['performance_data'] ?? $row['Performance_data'] ?? '',
                $row['total_revenue'] ?? $row['Total_revenue'] ?? '',
                $row['period_type'] ?? $row['Period_type'] ?? ''
            ], null, 'A'.$rowNum);
            $rowNum++;
        }

        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'Общий доход по жанру');
        $rowNum += 2;
        $sheet->fromArray(['Жанр', 'Доход'], null, 'A'.$rowNum);
        $rowNum++;
        foreach ($genreRevenue as $row) {
            $sheet->fromArray([
                $row['performance_genre'] ?? $row['Performance_genre'] ?? '',
                $row['total_revenue'] ?? $row['Total_revenue'] ?? ''
            ], null, 'A'.$rowNum);
            $rowNum++;
        }

        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'Продажи билетов для зала');
        $rowNum += 2;
        $sheet->fromArray(['Зал', 'Продано билетов'], null, 'A'.$rowNum);
        $rowNum++;
        foreach ($hallTickets as $row) {
            $hallValue = '';
            if (isset($row['hall_label'])) {
                $hallValue = $row['hall_label'];
            } elseif (isset($row['Hall_label'])) {
                $hallValue = $row['Hall_label'];
            } elseif (isset($row['Hall_ID'])) {
                $hallValue = 'Зал №' . $row['Hall_ID'];
            } elseif (isset($row['hall_id'])) {
                $hallValue = 'Зал №' . $row['hall_id'];
            }
            $sheet->fromArray([
                $hallValue,
                $row['total_tickets'] ?? $row['Total_tickets'] ?? ''
            ], null, 'A'.$rowNum);
            $rowNum++;
        }

        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'Общая прибыль репертуаров');
        $rowNum += 2;
        $sheet->fromArray(['Репертуар', 'Прибыль'], null, 'A'.$rowNum);
        $rowNum++;
        foreach ($repertoireProfits as $row) {
            $sheet->fromArray([
                $row['repertoire_title'] ?? $row['Repertoire_title'] ?? '',
                $row['total_profit'] ?? $row['Total_profit'] ?? ''
            ], null, 'A'.$rowNum);
            $rowNum++;
        }

        $rowNum += 2;
        if ($repertoirePerf) {
            $sheet->setCellValue('A'.$rowNum, 'Прибыль по спектаклям для репертуара: ' . $repertoirePerf);
            $rowNum += 2;
            $sheet->fromArray(['Репертуар', '№', 'Спектакль', 'Прибыль'], null, 'A'.$rowNum);
            $rowNum++;
            foreach ($repertoirePerformanceProfits as $row) {
                $sheet->fromArray([
                    $row['repertoire_title'] ?? $row['Repertoire_title'] ?? '',
                    $row['performance_number'] ?? $row['Performance_number'] ?? '',
                    $row['performance_title'] ?? $row['Performance_title'] ?? '',
                    $row['ticket_profit'] ?? $row['Ticket_profit'] ?? ''
                ], null, 'A'.$rowNum);
                $rowNum++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'excel_report');
        $writer->save($temp_file);
        return $this->file($temp_file, 'analytics_report.xlsx', 'attachment');
    }

    #[Route('/admin/least-profitable-word', name: 'admin_least_profitable_word', methods: ['POST'])]
    public function adminLeastProfitableWord(Request $request): Response{
        $data = $this->reportDataService->getReportData($request->request->all());
        extract($data);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'width' => 100, 
        ];
        $cellStyle = ['valign' => 'center'];

        // 1. 10 наименее доходных спектаклей
        $section->addTitle('10 наименее доходных спектаклей за ' . $periodType, 1);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000, $cellStyle)->addText('Название');
        $table->addCell(1500, $cellStyle)->addText('Дата');
        $table->addCell(1500, $cellStyle)->addText('Доход');
        $table->addCell(1500, $cellStyle)->addText('Период');
        foreach ($leastPerformances as $row) {
            $table->addRow();
            $table->addCell(2000, $cellStyle)->addText($row['performance_title'] ?? $row['Performance_title'] ?? '');
            $table->addCell(1500, $cellStyle)->addText($row['performance_data'] ?? $row['Performance_data'] ?? '');
            $table->addCell(1500, $cellStyle)->addText(($row['total_revenue'] ?? $row['Total_revenue'] ?? '') . ' руб.');
            $table->addCell(1500, $cellStyle)->addText($row['period_type'] ?? $row['Period_type'] ?? '');
        }

        $section->addTextBreak(2);

        // 2. Общий доход по жанру
        $section->addTitle('Общий доход по жанру', 1);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(3000, $cellStyle)->addText('Жанр');
        $table->addCell(2000, $cellStyle)->addText('Доход');
        foreach ($genreRevenue as $row) {
            $table->addRow();
            $table->addCell(3000, $cellStyle)->addText($row['performance_genre'] ?? $row['Performance_genre'] ?? '');
            $table->addCell(2000, $cellStyle)->addText(($row['total_revenue'] ?? $row['Total_revenue'] ?? '') . ' руб.');
        }

        $section->addTextBreak(2);

        // 3. Продажи билетов для зала
        $section->addTitle('Продажи билетов для зала', 1);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(3000, $cellStyle)->addText('Зал');
        $table->addCell(2000, $cellStyle)->addText('Продано билетов');
        foreach ($hallTickets as $row) {
            $table->addRow();
            $hallValue = '';
            if (isset($row['hall_label'])) {
                $hallValue = $row['hall_label'];
            } elseif (isset($row['Hall_label'])) {
                $hallValue = $row['Hall_label'];
            } elseif (isset($row['Hall_ID'])) {
                $hallValue = 'Зал №' . $row['Hall_ID'];
            } elseif (isset($row['hall_id'])) {
                $hallValue = 'Зал №' . $row['hall_id'];
            }
            $table->addCell(3000, $cellStyle)->addText($hallValue);
            $table->addCell(2000, $cellStyle)->addText($row['total_tickets'] ?? $row['Total_tickets'] ?? '');
        }

        $section->addTextBreak(2);

        // 4. Общая прибыль репертуаров
        $section->addTitle('Общая прибыль репертуаров', 1);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(3000, $cellStyle)->addText('Репертуар');
        $table->addCell(2000, $cellStyle)->addText('Прибыль');
        foreach ($repertoireProfits as $row) {
            $table->addRow();
            $table->addCell(3000, $cellStyle)->addText($row['repertoire_title'] ?? $row['Repertoire_title'] ?? '');
            $table->addCell(2000, $cellStyle)->addText(($row['total_profit'] ?? $row['Total_profit'] ?? '') . ' руб.');
        }

        // 5. Прибыль по спектаклям для репертуара
        if ($repertoirePerf) {
            $section->addTextBreak(2);
            $section->addTitle('Прибыль по спектаклям для репертуара: ' . $repertoirePerf, 1);
            $table = $section->addTable($tableStyle);
            $table->addRow();
            $table->addCell(2000, $cellStyle)->addText('Репертуар');
            $table->addCell(1000, $cellStyle)->addText('№');
            $table->addCell(2000, $cellStyle)->addText('Спектакль');
            $table->addCell(1500, $cellStyle)->addText('Прибыль');
            foreach ($repertoirePerformanceProfits as $row) {
                $table->addRow();
                $table->addCell(2000, $cellStyle)->addText($row['repertoire_title'] ?? $row['Repertoire_title'] ?? '');
                $table->addCell(1000, $cellStyle)->addText($row['performance_number'] ?? $row['Performance_number'] ?? '');
                $table->addCell(2000, $cellStyle)->addText($row['performance_title'] ?? $row['Performance_title'] ?? '');
                $table->addCell(1500, $cellStyle)->addText(($row['ticket_profit'] ?? $row['Ticket_profit'] ?? '') . ' руб.');
            }
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'word_report');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($temp_file);
        return $this->file($temp_file, 'analytics_report.docx', 'attachment');
    }
}