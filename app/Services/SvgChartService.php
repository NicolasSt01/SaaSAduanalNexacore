<?php

namespace App\Services;

class SvgChartService
{
    private const COLORS = ['#1e3a5f', '#1a7a3a', '#b8860b', '#b91c1c', '#0e7490', '#6f42c1', '#d946ef', '#f97316'];

    private bool $asImage;

    public function __construct(bool $asImage = true)
    {
        $this->asImage = $asImage;
    }

    private function render(string $svg, int $width, int $height): string
    {
        if (!$this->asImage) {
            return $svg;
        }

        try {
            $fullSvg = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">%s</svg>',
                $width * 2,
                $height * 2,
                $width,
                $height,
                $svg
            );

            $imagick = new \Imagick();
            $imagick->setBackgroundColor(new \ImagickPixel('white'));
            $imagick->readImageBlob($fullSvg);
            $imagick->setImageFormat('png');
            $imagick->resizeImage($width * 2, $height * 2, \Imagick::FILTER_LANCZOS, 1);
            $png = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();
            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Throwable $e) {
            \Log::warning('SvgChartService: Imagick conversion failed, falling back to SVG inline', ['error' => $e->getMessage()]);
            return sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">%s</svg>', $width, $height, $width, $height, $svg);
        }
    }

    public function doughnut(array $slices, int $width = 240, int $height = 180, ?string $centerLabel = null): string
    {
        $total = array_sum(array_column($slices, 'value'));
        if ($total <= 0) {
            return $this->emptyChart($width, $height);
        }

        $cx = (int)($height / 2);
        $cy = (int)($height / 2);
        $outerR = $cy - 12;
        $innerR = (int)($outerR * 0.58);
        $legendX = $cx * 2 + 16;

        $svg = '<g>';
        $angle = -M_PI / 2;

        foreach ($slices as $slice) {
            if ($slice['value'] <= 0) {
                continue;
            }
            $sweep = ($slice['value'] / $total) * 2 * M_PI;
            if ($sweep < 0.005) {
                $angle += $sweep;
                continue;
            }
            $endAngle = $angle + $sweep;
            $svg .= $this->doughnutSlice($cx, $cy, $outerR, $innerR, $angle, $endAngle, $slice['color']);
            $angle = $endAngle;
        }

        if ($centerLabel !== null) {
            $svg .= sprintf('<text x="%d" y="%d" text-anchor="middle" font-size="11" font-weight="bold" fill="#1e3a5f">%s</text>', $cx, $cy + 4, $this->escape($centerLabel));
        }

        $svg .= '</g>';

        $ly = 14;
        foreach ($slices as $slice) {
            if ($slice['value'] <= 0) {
                continue;
            }
            $pct = round(($slice['value'] / $total) * 100, 1);
            $svg .= sprintf('<rect x="%d" y="%d" width="8" height="8" rx="1" fill="%s"/>', $legendX, $ly - 6, $slice['color']);
            $svg .= sprintf('<text x="%d" y="%d" font-size="7" fill="#333">%s (%s%%)</text>', $legendX + 12, $ly + 1, $this->escape($slice['label']), $pct);
            $ly += 14;
        }

        return $this->render($svg, $width, $height);
    }

    public function lineChart(array $datasets, array $labels, int $width = 440, int $height = 170): string
    {
        $allValues = [];
        foreach ($datasets as $ds) {
            $allValues = array_merge($allValues, $ds['data']);
        }
        $allValues = array_filter($allValues, fn($v) => $v !== null);
        if (empty($allValues)) {
            return $this->emptyChart($width, $height);
        }

        $pad = ['top' => 16, 'right' => 16, 'bottom' => 26, 'left' => 34];
        $plotW = $width - $pad['left'] - $pad['right'];
        $plotH = $height - $pad['top'] - $pad['bottom'];
        $dataMax = max($allValues);
        $step = $this->niceStep(max($dataMax, 1));
        $yMax = $this->niceMax(max($dataMax, 1), $step);
        $yMin = 0;
        $range = max($yMax - $yMin, 1);
        $n = count($labels);
        $stepX = $n > 1 ? $plotW / ($n - 1) : 0;

        $svg = '<g>';

        for ($i = 0; $i <= 5; $i++) {
            $val = $yMax - ($range / 5) * $i;
            $y = $pad['top'] + ($plotH / 5) * $i;
            $svg .= sprintf('<line x1="%d" y1="%.1f" x2="%d" y2="%.1f" stroke="#e8ecf1" stroke-width="0.5"/>', $pad['left'], $y, $width - $pad['right'], $y);
            $svg .= sprintf('<text x="%d" y="%.1f" text-anchor="end" font-size="7" fill="#777">%.0f</text>', $pad['left'] - 3, $y + 2.5, $val);
        }

        foreach ($labels as $i => $label) {
            $x = $pad['left'] + $i * $stepX;
            $svg .= sprintf('<text x="%.1f" y="%d" text-anchor="middle" font-size="7" fill="#777">%s</text>', $x, $height - $pad['bottom'] + 14, $this->escape($label));
        }

        foreach ($datasets as $ds) {
            $points = [];
            $first = true;
            $areaPoints = [sprintf('%.1f,%.1f', $pad['left'], $pad['top'] + $plotH)];

            foreach ($ds['data'] as $i => $val) {
                $x = $pad['left'] + $i * $stepX;
                $y = $pad['top'] + $plotH - (($val - $yMin) / $range) * $plotH;
                $points[] = sprintf('%.1f,%.1f', $x, $y);
                $areaPoints[] = sprintf('%.1f,%.1f', $x, $y);
            }

            $areaPoints[] = sprintf('%.1f,%.1f', $pad['left'] + ($n - 1) * $stepX, $pad['top'] + $plotH);

            $fillColor = $ds['fillColor'] ?? $this->lighten($ds['color'], 0.9);
            $svg .= sprintf('<polygon points="%s" fill="%s"/>', implode(' ', $areaPoints), $fillColor);
            $svg .= sprintf('<polyline points="%s" fill="none" stroke="%s" stroke-width="1.5" stroke-linejoin="round"/>', implode(' ', $points), $ds['color']);

            foreach ($ds['data'] as $i => $val) {
                if ($val === null) {
                    continue;
                }
                $x = $pad['left'] + $i * $stepX;
                $y = $pad['top'] + $plotH - (($val - $yMin) / $range) * $plotH;
                $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="2.5" fill="%s" stroke="#fff" stroke-width="1"/>', $x, $y, $ds['color']);
            }
        }

        if (count($datasets) > 1) {
            $lx = $width - $pad['right'] - 5;
            $ly = $pad['top'] + 2;
            foreach ($datasets as $ds) {
                $svg .= sprintf('<rect x="%d" y="%d" width="8" height="4" rx="1" fill="%s"/>', $lx - 40, $ly, $ds['color']);
                $svg .= sprintf('<text x="%d" y="%d" font-size="7" fill="#555" text-anchor="end">%s</text>', $lx - 2, $ly + 4, $this->escape($ds['label']));
                $ly += 12;
            }
        }

        $svg .= '</g>';

        return $this->render($svg, $width, $height);
    }

    public function barChart(array $series, array $labels, int $width = 360, int $height = 180): string
    {
        if (empty($labels)) {
            return $this->emptyChart($width, $height);
        }

        $pad = ['top' => 20, 'right' => 16, 'bottom' => 50, 'left' => 34];
        $plotW = $width - $pad['left'] - $pad['right'];
        $plotH = $height - $pad['top'] - $pad['bottom'];

        $allValues = [];
        foreach ($series as $s) {
            $allValues = array_merge($allValues, $s['data']);
        }
        $dataMax = max($allValues) ?: 1;
        $step = $this->niceStep($dataMax);
        $yMax = $this->niceMax($dataMax, $step);
        $range = max($yMax, 1);

        $numBars = count($labels);
        $numSeries = count($series);
        $groupWidth = $plotW / $numBars;
        $barWidth = max(4, ($groupWidth * 0.7) / $numSeries);
        $groupPad = ($groupWidth - $barWidth * $numSeries) / 2;

        $svg = '<g>';

        for ($i = 0; $i <= 5; $i++) {
            $val = $yMax - ($range / 5) * $i;
            $y = $pad['top'] + ($plotH / 5) * $i;
            $svg .= sprintf('<line x1="%d" y1="%.1f" x2="%d" y2="%.1f" stroke="#e8ecf1" stroke-width="0.5"/>', $pad['left'], $y, $width - $pad['right'], $y);
            $svg .= sprintf('<text x="%d" y="%.1f" text-anchor="end" font-size="7" fill="#777">%.0f</text>', $pad['left'] - 3, $y + 2.5, $val);
        }

        $isStacked = !empty($series[0]['stacked']);
        foreach ($labels as $i => $label) {
            $groupX = $pad['left'] + $i * $groupWidth;
            $labelText = mb_strlen($label) > 10 ? mb_substr($label, 0, 9) . '.' : $label;
            $svg .= sprintf('<text x="%.1f" y="%d" text-anchor="end" font-size="6.5" fill="#555" transform="rotate(-40, %.1f, %d)">%s</text>', $groupX + $groupWidth / 2, $height - $pad['bottom'] + 12, $groupX + $groupWidth / 2, $height - $pad['bottom'] + 12, $this->escape($labelText));

            if ($isStacked) {
                $cumHeight = 0;
                foreach ($series as $si => $s) {
                    $val = $s['data'][$i] ?? 0;
                    $barH = ($val / $range) * $plotH;
                    $barY = $pad['top'] + $plotH - $cumHeight - $barH;
                    $x = $groupX + $groupPad;
                    $barW = $groupWidth * 0.7;
                    $svg .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" rx="1"/>', $x, $barY, $barW, max($barH, 0), $s['color']);
                    $cumHeight += $barH;
                }
            } else {
                foreach ($series as $si => $s) {
                    $val = $s['data'][$i] ?? 0;
                    $barH = ($val / $range) * $plotH;
                    $barY = $pad['top'] + $plotH - $barH;
                    $x = $groupX + $groupPad + $si * $barWidth;
                    $svg .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" rx="1"/>', $x, $barY, $barWidth - 1, max($barH, 0), $s['color']);
                }
            }
        }

        $svg .= '</g>';

        $ly = 4;
        foreach ($series as $s) {
            $svg .= sprintf('<rect x="%d" y="%d" width="8" height="6" rx="1" fill="%s"/>', $pad['left'], $ly, $s['color']);
            $svg .= sprintf('<text x="%d" y="%d" font-size="7" fill="#555">%s</text>', $pad['left'] + 12, $ly + 5, $this->escape($s['label']));
            $ly += 11;
        }

        return $this->render($svg, $width, $height);
    }

    public function horizontalBar(array $bars, int $width = 320, int $height = 180): string
    {
        if (empty($bars)) {
            return $this->emptyChart($width, $height);
        }

        $maxBars = 8;
        if (count($bars) > $maxBars) {
            $bars = array_slice($bars, 0, $maxBars);
        }

        $pad = ['top' => 8, 'right' => 30, 'bottom' => 8, 'left' => 90];
        $plotW = $width - $pad['left'] - $pad['right'];
        $plotH = $height - $pad['top'] - $pad['bottom'];
        $n = count($bars);
        $barH = min(18, ($plotH - ($n - 1) * 4) / $n);
        $totalBarSpace = $barH * $n + 4 * ($n - 1);
        $offsetY = $pad['top'] + ($plotH - $totalBarSpace) / 2;
        $maxVal = max(array_column($bars, 'value')) ?: 1;
        $range = max($maxVal * 1.15, 1);

        $svg = '<g>';

        for ($i = 0; $i <= 4; $i++) {
            $val = ($range / 4) * $i;
            $x = $pad['left'] + ($val / $range) * $plotW;
            $svg .= sprintf('<line x1="%.1f" y1="%d" x2="%.1f" y2="%d" stroke="#e8ecf1" stroke-width="0.5"/>', $x, $pad['top'], $x, $height - $pad['bottom']);
            $svg .= sprintf('<text x="%.1f" y="%d" text-anchor="middle" font-size="7" fill="#777">%.0f</text>', $x, $height - $pad['bottom'] + 8, $val);
        }

        foreach ($bars as $i => $bar) {
            $y = $offsetY + $i * ($barH + 4);
            $label = mb_strlen($bar['label']) > 16 ? mb_substr($bar['label'], 0, 15) . '.' : $bar['label'];
            $svg .= sprintf('<text x="%d" y="%.1f" text-anchor="end" font-size="7" fill="#333">%s</text>', $pad['left'] - 4, $y + $barH / 2 + 2.5, $this->escape($label));

            $barW = ($bar['value'] / $range) * $plotW;
            $svg .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" rx="2"/>', (float)$pad['left'], $y, max($barW, 0), $barH, $bar['color'] ?? self::COLORS[$i % count(self::COLORS)]);

            $svg .= sprintf('<text x="%.1f" y="%.1f" font-size="7" font-weight="bold" fill="#333">%d</text>', $pad['left'] + $barW + 3, $y + $barH / 2 + 3, $bar['value']);
        }

        $svg .= '</g>';

        return $this->render($svg, $width, $height);
    }

    private function doughnutSlice(float $cx, float $cy, float $ro, float $ri, float $a1, float $a2, string $color): string
    {
        $large = ($a2 - $a1) > M_PI ? 1 : 0;

        $ox1 = $cx + $ro * cos($a1);
        $oy1 = $cy + $ro * sin($a1);
        $ox2 = $cx + $ro * cos($a2);
        $oy2 = $cy + $ro * sin($a2);
        $ix1 = $cx + $ri * cos($a1);
        $iy1 = $cy + $ri * sin($a1);
        $ix2 = $cx + $ri * cos($a2);
        $iy2 = $cy + $ri * sin($a2);

        if (abs($a2 - $a1) >= 2 * M_PI - 0.01) {
            $mid = ($a1 + $a2) / 2;
            $mx1 = $cx + $ro * cos($mid);
            $my1 = $cy + $ro * sin($mid);
            $mx2 = $cx + $ri * cos($mid);
            $my2 = $cy + $ri * sin($mid);

            return sprintf(
                '<path d="M %.2f,%.2f A %.2f,%.2f 0 1 1 %.2f,%.2f A %.2f,%.2f 0 1 1 %.2f,%.2f L %.2f,%.2f A %.2f,%.2f 0 1 1 %.2f,%.2f A %.2f,%.2f 0 1 1 %.2f,%.2f Z" fill="%s"/>',
                $ox1, $oy1, $ro, $ro, $mx1, $my1, $ro, $ro, $ox2, $oy2,
                $ix2, $iy2, $ri, $ri, $mx2, $my2, $ri, $ri, $ix1, $iy1,
                $color
            );
        }

        return sprintf(
            '<path d="M %.2f,%.2f A %.2f,%.2f 0 %d 1 %.2f,%.2f L %.2f,%.2f A %.2f,%.2f 0 %d 0 %.2f,%.2f Z" fill="%s"/>',
            $ox1, $oy1, $ro, $ro, $large, $ox2, $oy2,
            $ix2, $iy2, $ri, $ri, $large, $ix1, $iy1,
            $color
        );
    }

    private function emptyChart(int $width, int $height): string
    {
        $svg = sprintf('<text x="%d" y="%d" text-anchor="middle" font-size="10" fill="#999">Sin datos</text>', $width / 2, $height / 2);
        return $this->render($svg, $width, $height);
    }

    private function niceStep(float $range, int $targetSteps = 5): float
    {
        if ($range <= 0) {
            return 1;
        }
        $rough = $range / $targetSteps;
        $mag = pow(10, floor(log10($rough)));
        $norm = $rough / $mag;
        if ($norm <= 1) {
            return $mag;
        }
        if ($norm <= 2) {
            return 2 * $mag;
        }
        if ($norm <= 5) {
            return 5 * $mag;
        }
        return 10 * $mag;
    }

    private function niceMax(float $dataMax, float $step): float
    {
        if ($step <= 0) {
            return $dataMax;
        }
        return ceil($dataMax / $step) * $step;
    }

    private function lighten(string $hex, float $amount = 0.85): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = round($r + (255 - $r) * $amount);
        $g = round($g + (255 - $g) * $amount);
        $b = round($b + (255 - $b) * $amount);
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}