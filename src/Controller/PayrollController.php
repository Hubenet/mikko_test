<?php

namespace App\Controller;

use App\Service\PayrollService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PayrollController extends AbstractController
{
    public function __construct(private PayrollService $payrollService) {}

    #[Route('/payments/csv', name: 'payments_csv')]
    public function csv(): Response
    {
        $rows = $this->payrollService->getPaymentDatesForRemainderOfYear();
        $year = (new \DateTimeImmutable())->format('Y');
        $filename = sprintf('payment-dates-%s.csv', $year);

        $fp = fopen('php://memory', 'r+');
        fputcsv($fp, ['Month', 'Salary date', 'Bonus date']);
        foreach ($rows as $row) {
            fputcsv($fp, [$row['month'], $row['salary'], $row['bonus']]);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
