<?php

require_once('SpracujXml.php');

if (!empty($_POST['ico'])) {
    try {
        $xml = new SpracujXml($_POST['ico']);
    
        $udaje = $xml->nacitajUdaje($nastavenia);
        if (!empty($udaje['Zadané IČO nebolo nájdené.'][0])) {
            header('Location: index.php?oznamenie=' . urlencode('Zadané IČO neexistuje !')); exit();
        }
        else if (empty($udaje['Názov firmy'])) {
            header('Location: index.php?oznamenie=' . urlencode('Nepodarilo sa načítať údaje.')); exit();
        }
    }
    catch (SpracujXmlException $e) {
        header('Location: index.php?oznamenie=' . urlencode($e->getMessage())); exit();
    }
    catch (FileException $e) {
        header('Location: index.php?oznamenie=' . urlencode($e->getMessage())); exit();
    }
}
else {
    header('Location: index.php'); exit();
}

?>
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