<?php

namespace App\Services;

class SimplePdfService
{
    public function textReport(string $title, array $lines): string
    {
        $pages = array_chunk($lines, 42);
        if ($pages === []) {
            $pages = [[]];
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $kids = [];
        $nextObject = 4;

        foreach ($pages as $index => $pageLines) {
            $pageObject = $nextObject++;
            $contentObject = $nextObject++;
            $kids[] = "{$pageObject} 0 R";
            $content = $this->contentStream($index === 0 ? $title : "{$title} (continued)", $pageLines);
            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObject} 0 R >>";
            $objects[$contentObject] = "<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= str_pad((string) $offsets[$id], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function contentStream(string $title, array $lines): string
    {
        $commands = ["BT", "/F1 18 Tf", "54 744 Td", '('.$this->escape($title).') Tj', "/F1 10 Tf"];
        foreach ($lines as $line) {
            foreach ($this->wrap((string) $line, 96) as $wrappedLine) {
                $commands[] = "0 -15 Td";
                $commands[] = '('.$this->escape($wrappedLine).') Tj';
            }
        }
        $commands[] = "ET";

        return implode("\n", $commands);
    }

    private function wrap(string $line, int $width): array
    {
        $wrapped = wordwrap($line, $width, "\n", true);

        return explode("\n", $wrapped);
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\(', '\)', ' ', ' '], $value);
    }
}
