<?php

class ExcelExporter {

    /**
     * Takes plan data and streams it to the browser as a downloadable CSV file.
     * @param array $data The full plan data object.
     */
    public function exportAsCsv(array $data) {
        $filename = "VietTransit_Travel_Plan_" . date('Y-m-d') . ".csv";

        // Set headers to force download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Open the output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM to fix UTF-8 in Excel
        fputs($output, "\xEF\xBB\xBF");

        // Add title
        fputcsv($output, ['AI Travel Plan from VietTransit']);
        fputcsv($output, []); // Blank line

        // Add table headers
        fputcsv($output, ['Day', 'Location', 'Activity', 'Estimated Cost (VND)', 'Notes']);

        // Add plan rows
        foreach ($data['plan'] as $row) {
            fputcsv($output, [
                $row['day'],
                $row['location'],
                $row['activity'],
                $row['cost'],
                $row['notes']
            ]);
        }

        // Add summary section
        fputcsv($output, []); // Blank line
        fputcsv($output, ['--- SUMMARY ---']);
        fputcsv($output, ['Total Estimated Cost (VND)', $data['summary']['total_cost']]);
        fputcsv($output, []); // Blank line
        fputcsv($output, ['Cost Breakdown']);

        foreach ($data['summary']['cost_breakdown'] as $key => $value) {
            fputcsv($output, [str_replace('_', ' ', $key), $value]);
        }
        
        fputcsv($output, []); // Blank line
        fputcsv($output, ['--- TRAVEL TIPS ---']);
        // A simple way to put tips in CSV, removing HTML tags
        fputcsv($output, [strip_tags($data['tips'])]);

        fclose($output);
        exit();
    }
}