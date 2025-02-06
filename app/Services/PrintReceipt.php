<?php

namespace App\Services;

use Exception;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrintReceipt
{
    public function printReceipt($sale_ref, $cashier_name, $transaction_source, $sale_date_time, $items, $currency, $total_amount, $amount_paid, $change) {
        try {
            $selected_printer = get_selected_printer();

            if ($selected_printer == "OneNote (Desktop)" || $selected_printer == "Microsoft Print to PDF") {
                $filePath = getenv('USERPROFILE').'\\Documents\\'. $sale_ref .'.txt';
                $this->generateTextFile($filePath, $sale_ref, $cashier_name, $transaction_source, $sale_date_time, $items, $currency, $total_amount, $amount_paid, $change);
                return "Receipt saved as text file!";
            }

            $connector = new WindowsPrintConnector($selected_printer);
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("Midlands State University\n");
            $printer->text("Canteen Shop\n");
            $printer->setEmphasis(false);
            $printer->text("------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Sale Ref: $sale_ref\n");
            $printer->text("Cashier: $cashier_name\n");
            $printer->text("Trans: $transaction_source\n");
            $printer->text("Date: " . date('Y-m-d H:i:s', strtotime($sale_date_time)) . "\n");
            $printer->text("------------------------------\n");

            foreach ($items as $item) {
                $printer->text($item['name'] . " x" . $item['qty'] . "  " . $currency . number_format($item['total_price'], 2) . "\n");
            }
            $printer->text("------------------------------\n");

            $printer->setEmphasis(true);
            $printer->text("Total: " . $currency . number_format($total_amount, 2) . "\n");
            $printer->setEmphasis(false);
            if($transaction_source == "CASH") {
                $printer->text("Amount Paid: " . $currency . number_format($amount_paid, 2) . "\n");
                $printer->text("Change: " . $currency . number_format($change, 2) . "\n");
            }
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return "Receipt printed successfully!";
        } catch (Exception $e) {
            return "Couldn't print receipt: " . $e->getMessage();
        }
    }

    private function generateTextFile($filePath, $sale_ref, $cashier_name, $transaction_source, $sale_date_time, $items, $currency, $total_amount, $amount_paid, $change) {
        $content = "";
        $content .= "Midlands State University\n";
        $content .= "Canteen Shop\n";
        $content .= "------------------------------\n";
        $content .= "Sale Ref: $sale_ref\n";
        $content .= "Cashier: $cashier_name\n";
        $content .= "Trans: $transaction_source\n";
        $content .= "Date: " . date('Y-m-d H:i:s', strtotime($sale_date_time)) . "\n";
        $content .= "------------------------------\n";

        foreach ($items as $item) {
            $content .= $item['name'] . " x" . $item['qty'] . "  " . $currency . number_format($item['total_price'], 2) . "\n";
        }
        $content .= "------------------------------\n";
        $content .= "Total: " . $currency . number_format($total_amount, 2) . "\n";
        if($transaction_source == "CASH"){
            $content .= "Amount Paid: " . $currency . number_format($amount_paid, 2) . "\n";
            $content .= "Change: " . $currency . number_format($change, 2) . "\n";
        }

        file_put_contents($filePath, $content);
    }
}
