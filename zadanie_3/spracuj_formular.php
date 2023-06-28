<?php

require_once('SpracujXml.php'); // Vklada sa trieda na spracovanie xml suboru

if (!empty($_POST['ico'])) { // Kontrola ci bolo zadane ICO
    try { 
        $xml = new SpracujXml($_POST['ico']); // Vytvara sa objekt a vykona sa konstruktor
    
        $udaje = $xml->nacitajUdaje($nastavenia); // V poli $udaje su vsetky udaje vytiahnute z xml suboru a to podla pola $nastavenia
                                                  // definovaneho v SpracujXml.php
        if (!empty($udaje['Zadané IČO nebolo nájdené.'][0])) { // Ak bola nacitana tato hodnota, znamena to, ze sa nenasli ziadne udaje pre dane ICO
            header('Location: index.php?oznamenie=' . urlencode('Zadané IČO neexistuje !')); exit(); // Opat presmerovanie s oznamenim
        }
        else if (empty($udaje['Názov firmy'])) { // Ak sa nenacital ani nazov firmy
            header('Location: index.php?oznamenie=' . urlencode('Nepodarilo sa načítať údaje.')); exit(); // Opat presmerovanie s oznamenim
        }
    }
    catch (SpracujXmlException $e) {
        header('Location: index.php?oznamenie=' . urlencode($e->getMessage())); exit(); // Ak sa vyskytla bezna chyba, presmerovanie na index.php s oznamenim
    }
    catch (FileException $e) {
        header('Location: index.php?oznamenie=' . urlencode($e->getMessage())); exit(); // Ak sa vyskytla suborova chyba, presmerovanie na index.php s oznamenim
    }
}
else {
    header('Location: index.php'); exit(); // Ak nebolo zadane ICO, presmerovanie spat na index.php 
}

?>
<!-- Jednoducha sablona kde sa iba vypisuju udaje z pola $udaje, ktore sa naplni podla premennej $nastavenia zo suboru SpracujXml.php -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Zadajte IČO</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>
    <body>
        <div class="container py-5">
            <div class="row mb-5">
                <div class="col-md-9 me-md-auto">
                    <h1 class="fw-bolder ps-5"><?php echo $udaje['Názov firmy'][0]; ?></h1>
                </div>
                <div class="col-auto ms-auto mt-3 mt-md-0 ms-md-0 me-md-5">
                    <a href="index.php" class="col-auto btn btn-primary">Späť</a>
                </div>
            </div>
            <div class="row bg-light gy-3" style="border-radius:12px;">
                <?php foreach ($udaje as $nazov => $udaj): ?>
                    <div class="col-md-4 border-bottom" >
                        <h5 class="ps-5 py-2"><?php echo $nazov; ?></h5>
                    </div>
                    <div class="col-md-8 border-bottom">
                        <?php foreach ($udaj as $hodnota): ?>
                            <p class="lead py-2 ps-5 ps-md-0"><?php echo $hodnota; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>