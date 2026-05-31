<?php

namespace App\Services;

class PngChartService
{
    private const COLORS = ['#1e3a5f', '#1a7a3a', '#b8860b', '#b91c1c', '#0e7490', '#6f42c1', '#d946ef', '#f97316'];
    private const SCALE = 2;

    private function getFontPath(): ?string
    {
        $fontPaths = [
            storage_path('app/fonts/DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/local/share/fonts/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ];
        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    private function px(int $v): int
    {
        return $v * self::SCALE;
    }

    private function hexToAlloc(string $hex, $img)
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($img, $r, $g, $b);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function lightenHex(string $hex, float $amount = 0.85): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf('#%02x%02x%02x', round($r + (255 - $r) * $amount), round($g + (255 - $g) * $amount), round($b + (255 - $b) * $amount));
    }

    private function drawText($img, int $sizePt, int $x, int $y, string $text, $color, string $align = 'left'): void
    {
        $font = $this->getFontPath();
        $px = $this->px($sizePt) * 4 / 3;
        $x = $this->px($x);
        $y = $this->px($y);

        if ($font === null) {
            $gdFont = max(1, min(5, (int)($sizePt * 1.2)));
            if ($align === 'right') {
                $x -= strlen($text) * imagefontwidth($gdFont);
            } elseif ($align === 'center') {
                $x -= (int)((strlen($text) * imagefontwidth($gdFont)) / 2);
            }
            imagestring($img, $gdFont, $x, $y - (int)$px, $text, $color);
            return;
        }

        if ($align === 'right') {
            $bbox = imagettfbbox($px, 0, $font, $text);
            $x -= ($bbox[2] - $bbox[0]);
        } elseif ($align === 'center') {
            $bbox = imagettfbbox($px, 0, $font, $text);
            $x -= (int)(($bbox[2] - $bbox[0]) / 2);
        }

        imagettftext($img, $px, 0, $x, $y, $color, $font, $text);
    }

    private function textWidthPx(int $sizePt, string $text): int
    {
        $font = $this->getFontPath();
        if ($font === null) {
            return (int)(strlen($text) * $sizePt * 0.6);
        }
        $px = $this->px($sizePt) * 4 / 3;
        $bbox = imagettfbbox($px, 0, $font, $text);
        return (int)(($bbox[2] - $bbox[0]) / self::SCALE);
    }

    private function niceStep(float $range, int $targetSteps = 5): float
    {
        if ($range <= 0) return 1;
        $rough = $range / $targetSteps;
        $mag = pow(10, floor(log10($rough)));
        $norm = $rough / $mag;
        if ($norm <= 1) return $mag;
        if ($norm <= 2) return 2 * $mag;
        if ($norm <= 5) return 5 * $mag;
        return 10 * $mag;
    }

    private function niceMax(float $dataMax, float $step): float
    {
        return $step <= 0 ? $dataMax : ceil($dataMax / $step) * $step;
    }

    private function toPng($img): string
    {
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);
        return 'data:image/png;base64,' . base64_encode($data);
    }

    public function doughnut(array $slices, int $displayW = 300, int $displayH = 260, ?string $centerLabel = null): string
    {
        $total = array_sum(array_column($slices, 'value'));
        if ($total <= 0) {
            return $this->emptyChart($displayW, $displayH);
        }

        $legendFont = 8;
        $rowH = 18;
        $legendGap = 16;
        $numSlices = count(array_filter($slices, fn($s) => $s['value'] > 0));
        $legendBlockH = $numSlices * $rowH;
        $pieAreaH = $displayH - $legendBlockH - $legendGap;

        $w = $this->px($displayW);
        $h = $this->px($displayH);
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        $cx = (int)($displayW / 2);
        $cy = (int)($pieAreaH / 2);
        $outerR = min($cx, $cy) - 14;
        $innerR = (int)($outerR * 0.58);

        $angle = 0;
        foreach ($slices as $slice) {
            if ($slice['value'] <= 0) continue;
            $sweep = ($slice['value'] / $total) * 360;
            $color = $this->hexToAlloc($slice['color'], $img);
            imagefilledarc($img, $this->px($cx), $this->px($cy), $this->px($outerR * 2), $this->px($outerR * 2), (int)round($angle), (int)round($angle + $sweep), $color, IMG_ARC_PIE);
            $angle += $sweep;
        }

        if ($innerR > 0) {
            $bgColor = $this->hexToAlloc('#ffffff', $img);
            imagefilledellipse($img, $this->px($cx), $this->px($cy), $this->px($innerR * 2), $this->px($innerR * 2), $bgColor);
        }

        if ($centerLabel !== null) {
            $darkBlue = imagecolorallocate($img, 30, 58, 95);
            $this->drawText($img, 11, $cx, $cy + 5, $centerLabel, $darkBlue, 'center');
        }

        $ly = $pieAreaH + $legendGap;
        $textColor = imagecolorallocate($img, 51, 51, 51);
        $dotX = (int)($displayW / 2) - 80;
        foreach ($slices as $slice) {
            if ($slice['value'] <= 0) continue;
            $pct = round(($slice['value'] / $total) * 100, 1);
            $color = $this->hexToAlloc($slice['color'], $img);
            imagefilledrectangle($img, $this->px($dotX), $this->px($ly), $this->px($dotX + 10), $this->px($ly + 10), $color);
            $this->drawText($img, $legendFont, $dotX + 14, $ly + 8, $slice['label'] . ' (' . $pct . '%)', $textColor);
            $ly += $rowH;
        }

        return $this->toPng($img);
    }

    public function lineChart(array $datasets, array $labels, int $displayW = 520, int $displayH = 220): string
    {
        $allValues = [];
        foreach ($datasets as $ds) {
            $allValues = array_merge($allValues, $ds['data']);
        }
        $allValues = array_filter($allValues, fn($v) => $v !== null);
        if (empty($allValues)) {
            return $this->emptyChart($displayW, $displayH);
        }

        $w = $this->px($displayW);
        $h = $this->px($displayH);
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        $pad = ['top' => 20, 'right' => 20, 'bottom' => 30, 'left' => 40];
        $plotW = $displayW - $pad['left'] - $pad['right'];
        $plotH = $displayH - $pad['top'] - $pad['bottom'];
        $dataMax = max($allValues);
        $step = $this->niceStep(max($dataMax, 1));
        $yMax = $this->niceMax(max($dataMax, 1), $step);
        $yMin = 0;
        $range = max($yMax - $yMin, 1);
        $n = count($labels);
        $stepX = $n > 1 ? $plotW / ($n - 1) : 0;

        $gridColor = $this->hexToAlloc('#e8ecf1', $img);
        $labelColor = imagecolorallocate($img, 119, 119, 119);
        $axisFont = 7;

        for ($i = 0; $i <= 5; $i++) {
            $val = $yMax - ($range / 5) * $i;
            $y = (int)($pad['top'] + ($plotH / 5) * $i);
            imageline($img, $this->px($pad['left']), $this->px($y), $this->px($displayW - $pad['right']), $this->px($y), $gridColor);
            $this->drawText($img, $axisFont, $pad['left'] - 4, $y + 3, (string)round($val), $labelColor, 'right');
        }

        foreach ($labels as $i => $label) {
            $x = (int)($pad['left'] + $i * $stepX);
            $this->drawText($img, $axisFont, $x, $displayH - $pad['bottom'] + 14, $label, $labelColor, 'center');
        }

        foreach ($datasets as $ds) {
            $fillColorRgb = $this->hexToRgb($ds['fillColor'] ?? $this->lightenHex($ds['color'], 0.9));
            $fillColor = imagecolorallocate($img, ...$fillColorRgb);
            $lineColor = $this->hexToAlloc($ds['color'], $img);

            $points = [];
            $areaPoints = [[$pad['left'], $pad['top'] + $plotH]];
            foreach ($ds['data'] as $i => $val) {
                $x = (int)($pad['left'] + $i * $stepX);
                $y = (int)($pad['top'] + $plotH - (($val - $yMin) / $range) * $plotH);
                $points[] = [$x, $y];
                $areaPoints[] = [$x, $y];
            }
            $areaPoints[] = [(int)($pad['left'] + ($n - 1) * $stepX), $pad['top'] + $plotH];

            $flatArea = [];
            foreach ($areaPoints as $p) {
                $flatArea[] = $this->px($p[0]);
                $flatArea[] = $this->px($p[1]);
            }
            imagefilledpolygon($img, $flatArea, $fillColor);

            for ($i = 0; $i < count($points) - 1; $i++) {
                imageline($img, $this->px($points[$i][0]), $this->px($points[$i][1]), $this->px($points[$i + 1][0]), $this->px($points[$i + 1][1]), $lineColor);
            }

            foreach ($points as $p) {
                imagefilledellipse($img, $this->px($p[0]), $this->px($p[1]), $this->px(6), $this->px(6), $lineColor);
                imagefilledellipse($img, $this->px($p[0]), $this->px($p[1]), $this->px(3), $this->px(3), $white);
            }
        }

        if (count($datasets) > 1) {
            $lx = $displayW - $pad['right'] - 8;
            $ly = $pad['top'] + 8;
            foreach ($datasets as $ds) {
                $dotColor = $this->hexToAlloc($ds['color'], $img);
                imagefilledrectangle($img, $this->px($lx - 48), $this->px($ly - 3), $this->px($lx - 38), $this->px($ly + 5), $dotColor);
                $this->drawText($img, 8, $lx - 4, $ly + 5, $ds['label'], $labelColor, 'right');
                $ly += 14;
            }
        }

        return $this->toPng($img);
    }

    public function barChart(array $series, array $labels, int $displayW = 460, int $displayH = 240): string
    {
        if (empty($labels)) {
            return $this->emptyChart($displayW, $displayH);
        }

        $w = $this->px($displayW);
        $h = $this->px($displayH);
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        $pad = ['top' => 24, 'right' => 16, 'bottom' => 56, 'left' => 42];
        $plotW = $displayW - $pad['left'] - $pad['right'];
        $plotH = $displayH - $pad['top'] - $pad['bottom'];

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

        $gridColor = $this->hexToAlloc('#e8ecf1', $img);
        $labelColor = imagecolorallocate($img, 119, 119, 119);
        $axisFont = 7;
        $legendFont = 8;

        for ($i = 0; $i <= 5; $i++) {
            $val = $yMax - ($range / 5) * $i;
            $y = (int)($pad['top'] + ($plotH / 5) * $i);
            imageline($img, $this->px($pad['left']), $this->px($y), $this->px($displayW - $pad['right']), $this->px($y), $gridColor);
            $this->drawText($img, $axisFont, $pad['left'] - 4, $y + 3, (string)round($val), $labelColor, 'right');
        }

        $isStacked = !empty($series[0]['stacked']);
        foreach ($labels as $i => $label) {
            $groupX = $pad['left'] + $i * $groupWidth;
            $labelText = mb_strlen($label) > 14 ? mb_substr($label, 0, 13) . '.' : $label;
            $this->drawText($img, $axisFont, (int)($groupX + $groupWidth / 2), $displayH - $pad['bottom'] + 14, $labelText, imagecolorallocate($img, 85, 85, 85), 'center');

            if ($isStacked) {
                $cumHeight = 0;
                foreach ($series as $si => $s) {
                    $val = $s['data'][$i] ?? 0;
                    $barH = (int)(($val / $range) * $plotH);
                    $barY = $pad['top'] + $plotH - $cumHeight - $barH;
                    $x = (int)($groupX + $groupPad);
                    $bw = (int)($groupWidth * 0.7);
                    $color = $this->hexToAlloc($s['color'], $img);
                    imagefilledrectangle($img, $this->px($x), $this->px($barY), $this->px($x + $bw), $this->px($barY + max($barH, 0)), $color);
                    $cumHeight += $barH;
                }
            } else {
                foreach ($series as $si => $s) {
                    $val = $s['data'][$i] ?? 0;
                    $barH = (int)(($val / $range) * $plotH);
                    $barY = $pad['top'] + $plotH - $barH;
                    $x = (int)($groupX + $groupPad + $si * $barWidth);
                    $color = $this->hexToAlloc($s['color'], $img);
                    imagefilledrectangle($img, $this->px($x), $this->px($barY), $this->px($x + $barWidth - 1), $this->px($barY + max($barH, 0)), $color);
                }
            }
        }

        $ly = 8;
        foreach ($series as $s) {
            $color = $this->hexToAlloc($s['color'], $img);
            imagefilledrectangle($img, $this->px($pad['left']), $this->px($ly), $this->px($pad['left'] + 10), $this->px($ly + 8), $color);
            $this->drawText($img, $legendFont, $pad['left'] + 14, $ly + 7, $s['label'], imagecolorallocate($img, 85, 85, 85));
            $ly += 14;
        }

        return $this->toPng($img);
    }

    public function horizontalBar(array $bars, int $displayW = 420, int $displayH = 240): string
    {
        if (empty($bars)) {
            return $this->emptyChart($displayW, $displayH);
        }

        $maxBars = 8;
        if (count($bars) > $maxBars) {
            $bars = array_slice($bars, 0, $maxBars);
        }

        $w = $this->px($displayW);
        $h = $this->px($displayH);
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        $pad = ['top' => 12, 'right' => 40, 'bottom' => 12, 'left' => 120];
        $plotW = $displayW - $pad['left'] - $pad['right'];
        $plotH = $displayH - $pad['top'] - $pad['bottom'];
        $n = count($bars);
        $barH = min(22, (int)(($plotH - ($n - 1) * 6) / $n));
        $totalBarSpace = $barH * $n + 6 * ($n - 1);
        $offsetY = $pad['top'] + (int)(($plotH - $totalBarSpace) / 2);
        $maxVal = max(array_column($bars, 'value')) ?: 1;
        $range = max($maxVal * 1.15, 1);

        $gridColor = $this->hexToAlloc('#e8ecf1', $img);
        $labelColor = imagecolorallocate($img, 119, 119, 119);
        $textColor = imagecolorallocate($img, 51, 51, 51);
        $labelFont = 8;
        $axisFont = 7;
        $valueFont = 7;

        for ($i = 0; $i <= 4; $i++) {
            $val = ($range / 4) * $i;
            $x = (int)($pad['left'] + ($val / $range) * $plotW);
            imageline($img, $this->px($x), $this->px($pad['top']), $this->px($x), $this->px($displayH - $pad['bottom']), $gridColor);
            $this->drawText($img, $axisFont, $x, $displayH - $pad['bottom'] + 10, (string)round($val), $labelColor, 'center');
        }

        foreach ($bars as $i => $bar) {
            $y = $offsetY + $i * ($barH + 6);
            $label = mb_strlen($bar['label']) > 22 ? mb_substr($bar['label'], 0, 21) . '.' : $bar['label'];
            $this->drawText($img, $labelFont, $pad['left'] - 6, $y + (int)($barH / 2) + 3, $label, $textColor, 'right');

            $barW = (int)(($bar['value'] / $range) * $plotW);
            $color = $this->hexToAlloc($bar['color'] ?? self::COLORS[$i % count(self::COLORS)], $img);
            imagefilledrectangle($img, $this->px($pad['left']), $this->px($y), $this->px($pad['left'] + max($barW, 0)), $this->px($y + $barH), $color);

            $boldColor = imagecolorallocate($img, 51, 51, 51);
            $this->drawText($img, $valueFont, $pad['left'] + $barW + 4, $y + (int)($barH / 2) + 3, (string)$bar['value'], $boldColor);
        }

        return $this->toPng($img);
    }

    private function emptyChart(int $displayW, int $displayH): string
    {
        $w = $this->px($displayW);
        $h = $this->px($displayH);
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        $gray = imagecolorallocate($img, 153, 153, 153);
        $this->drawText($img, 10, (int)($displayW / 2), (int)($displayH / 2), 'Sin datos', $gray, 'center');
        return $this->toPng($img);
    }
}