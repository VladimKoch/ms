<?php

// 1. Načteme Composer autoloader (mapu souborů)
require __DIR__ . '/../vendor/autoload.php';

echo "<h1>Test existence DataGridu</h1>";

// 2. Zkusíme, jestli PHP o té třídě ví
$trida = 'Ublaboo\DataGrid\DataGrid';

if (class_exists($trida)) {
    echo "<h2 style='color: green'>✅ HURÁ! Třída '$trida' existuje a je načtená.</h2>";
    echo "<p>Cesta k souboru: " . (new ReflectionClass($trida))->getFileName() . "</p>";
} else {
    echo "<h2 style='color: red'>❌ CHYBA: Třída '$trida' nebyla nalezena.</h2>";
    
    echo "<h3>Co vidím ve složce vendor/ublaboo?</h3>";
    $cesta = __DIR__ . '/../vendor/ublaboo';
    if (is_dir($cesta)) {
        echo "<pre>" . print_r(scandir($cesta), true) . "</pre>";
    } else {
        echo "Složka '$cesta' vůbec neexistuje!";
    }
}