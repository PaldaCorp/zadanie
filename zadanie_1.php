<?php

// Dolezite je poradie, najprv sa musi kontrolovat ci je $cislo bezo zvysku celociselne delitelne 15
for ($cislo = 1; $cislo <= 100; $cislo++) {
    if ($cislo % 15 == 0) {
        echo 'SuperFaktura' . '<br>';
    }
    else if ($cislo % 5 == 0) {
        echo 'Faktura' . '<br>';
    }
    else if ($cislo % 3 == 0) {
        echo 'Super' . '<br>';
    }
    else {
        echo $cislo . '<br>';
    }
}

?>