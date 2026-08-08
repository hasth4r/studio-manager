<?php
$img = imagecreatefrompng("public/assets/images/enso8_logo_Slim.png");
$width = imagesx($img);
$height = imagesy($img);

$colors = [];
for($x = 0; $x < $width; $x += 5) {
    for($y = 0; $y < $height; $y += 5) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        
        // ignore near white or near black or transparent
        $colors = imagecolorsforindex($img, $rgb);
        if ($colors['alpha'] > 100) continue;
        if ($r > 240 && $g > 240 && $b > 240) continue;
        if ($r < 15 && $g < 15 && $b < 15) continue;
        
        $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
        if (!isset($color_count[$hex])) $color_count[$hex] = 0;
        $color_count[$hex]++;
    }
}
arsort($color_count);
print_r(array_slice($color_count, 0, 5));
