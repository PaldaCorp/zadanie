<?php

/*
 * Trieda na spracovanie beznych chyb
 * 
*/
class SpracujXmlException extends Exception {

    public function __toString() {
        return 'Subor: ' . $this->getFile() . ' Riadok: ' . $this->getLine() . ' Sprava: ' . $this->getMessage();
    }
}

/*
 * Trieda na spracovanie chyb pri praci so subormi
 * 
*/
class FileException extends Exception {

    public function __toString() {
        return 'Subor: ' . $this->getFile() . ' Riadok: ' . $this->getLine() . ' Sprava: ' . $this->getMessage();
    }
}

/*
 * Trieda na spracovanie xml suboru
 * Staci spravit male upravy a trieda bude pripravena aj na ine typy endpointov
 * 
*/
class  SpracujXml {

    public $endpoint; // Url z ktorej sa budu nacitat udaje vo forme xml suboru
    public $ico; // ICO firmy, zadava sa cez konstruktor
    public $skratky; // Subor skratky.txt stiahnuty z url: https://wwwinfo.mfcr.cz/ares/xml_doc/schemas/documentation/zkr_103.txt
    public $xml_subor; // Subor stiahnuty z url: https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=<ICO>, ktory sa este bude upravovat

    /*
     *  function __construct(string | int $ico)
     * 
     *  Konstruktor prijima jediny parameter a inicializuje a kontroluje format pre ICO,
     *  inicializuje premennu $xml_subor z url https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=<ICO>,
     *  spracuva subor skratky.txt a na jeho zaklade vytvara subor upravene_skratky.txt,
     *  na zaklade obsahu suboru upravene_skratky.txt upravuje samotne xml elementy na
     *  citatelnejsiu formu, teda nahradza skratky v nazvoch elementov ich popisnejsimi alternativami
     *  a vysledok uklada do premennej $xml_subor.
     * 
    */

    public function __construct($ico) {
        $this->ico = str_replace(' ', '', (string) $ico); // Samotne ICO zadane uzivatelom, odstranenie pripadnych medzier
        $this->overIco(); // Funkcia na kontrolu formatu pre ICO
        $this->endpoint = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico='; // Samotna Url na ktoru sa pripoji ICO
        // nacitanie xml suboru z url
        $this->xml_subor = @file_get_contents($this->endpoint . $this->ico);
        
        if ($this->xml_subor === false) {
            throw new FileException('Nepodarilo sa načítať súbor.');
        }
        // Kontrola existencie suboru upravene_skratky.txt, ak existuje nevytvara ho znovu
        if (!file_exists('upravene_skratky.txt')) {
            // Zo suboru skratky.txt sa odstranuju uvodzovky a meni sa poradie hodnot oddelenych lomitkom
            // a vytvara sa subor upravene_skratky.txt
            $this->spracujSkratky('skratky.txt');
        }
        // Prepisuje sa premenna $skratky jej upravenou verziou
        // podla suboru upravene_skratky.txt
        $this->skratky = $this->nacitajSkratky('upravene_skratky.txt');
        // Spracuje obsah xml suboru pomocou skratiek v subore upravene_skratky.txt
        // a vytvory tak popisnejsie nazvy elementov
        $this->spracujXmlSubor();
        //$this->zobraz($this->xml_subor); exit(); // Kontrola spravnosti upravenych nazvov xml elementov
    
    }

    /*
     * function overIco(): bool | SpracujXmlException()
     * Funkcia pomocou reg. vyrazov a algoritmu kontroluje spravnost formatu pre ICO
     * V pripade ze format pre ICO nie je spravny, vyhadzuje vynimku SpracujXmlException()
     * 
    */
    public function overIco() {
        $ico = $this->ico;
        $reg_vyraz = '#^[0-9]{2,8}$#'; // Reg. vyraz kontroluje spravnu dlzku retazca a tiez ci sa retazec sklada iba z cislic
        if (!preg_match($reg_vyraz, $ico)) { // V pripade nespravneho formatu vyhadzuje vynimku
            throw new SpracujXmlException('Chybný formát pre IČO.'); 
            return false;
        }
        return true;
    }

    /*
     * function zobraz(mixed $obj): void
     * 
     * Funkcia sluzi iba ako pomocna pri vyvoji,
     * vypisuje na obrazovku prehladne obsah premennej $obj
     * 
    */
    public function zobraz($obj) {
        echo '<pre>';
        print_r($obj);
        echo '</pre>';
    }

    /*
     * function spracujXmlSubor(): void
     * 
     * Funkcia pomocou reg. vyrazov nahradza skratky v nazve elementov xml suboru
     * popisnejsimi nazvami podla premennej $skratky, co je pole ktoreho kluce predstavuju skratky a hodnotami
     * su popisnejsie nazvy pre elementy v xml subore
     * 
    */
    private function spracujXmlSubor() {
        foreach ($this->skratky as $skratka => $popis) {
            $reg_vyraz = '#(</?)(.*?:)(' . $skratka . ')( |>)#';
            // Vymaz pripadneho menneho priestoru napr. D: alebo U: z elementov
            $this->xml_subor = preg_replace($reg_vyraz, '${1}' . $popis . '${4}', $this->xml_subor);
        }
        
        $reg_vyraz = '#(</?)([a-zA-Z]*?:)(.*?>)#';
        $this->xml_subor = preg_replace($reg_vyraz, '${1}${3}', $this->xml_subor); 
        // Vymaz pripadneho menneho priestoru napr.D: alebo U: z elementov napr. <D:NACE>
        // alebo <U:XXX>, ktorych skratky nie su v subore upravene_skratky.txt
    }

    /*
     * function spracujSkratky(string $nazov_suboru): bool
     * 
     * Funkcia prijima nazov suboru so skratkami ziskanymi z url: https://wwwinfo.mfcr.cz/ares/xml_doc/schemas/documentation/zkr_103.txt,
     * odstranuje z neho uvodzovky a obsah suboru nacita do pola $udaje, kde ako kluc uklada skratku a ako hodnotu uklada jej popis.
     * Z pola $udaje potom vytvara subor upravene_skratky.txt pomocou ktoreho sa potom vytvori premenna $this->skratky.
     * 
    */
    private function spracujSkratky($nazov_suboru) {
        $udaje = [];
        $obsah = "";

        $skratky = @file($nazov_suboru, FILE_IGNORE_NEW_LINES);
        if ($skratky === false) {
            throw new FileException('Chyba pri spracovaní súboru skratky.txt');
            return false;
        }
        foreach ($skratky as $riadok) {
            $riadok = str_replace('"', '', $riadok);
            $pole = explode('/', $riadok);
            $udaje[$pole[1]] = $pole[0];
        }

        foreach ($udaje as $skratka => $popis) {
            $obsah .= $skratka . "/" . $popis . "\n";
        }

        $value = @file_put_contents('upravene_skratky.txt', $obsah);
        if ($value === false) {
            throw new FileException('Súbor upravene_skratky.txt sa nepodarilo vytvoriť');
            return false;
        }

        return true;
    }

    /*
     * function nacitajSkratky(string $nazov_suboru): array
     * 
     * Funkcia nacita obsah suboru upravene_skratky.txt a prevedie ho na pole,
     * kde klucom je skratka a hodnotou je popis tejto skratky a toto pole vrati.
     * 
    */
    private function nacitajSkratky($nazov_suboru) {
        $udaje = [];

        $skratky = @file($nazov_suboru, FILE_IGNORE_NEW_LINES);
        if ($skratky === false) {
            throw new FileException('Chyba pri spracovaní súboru upravene_skratky.txt');
            return false;
        }
        foreach ($skratky as $riadok) {
            $pole = explode('/', $riadok);
            $udaje[$pole[0]] = $pole[1];
        }

        return $udaje;
    }

    /*
     * function nacitajUdaj(string $termin, string $xml_obsah): array
     * 
     * Funkcia je pomocnou funkciou pre funkciu nacitajUdaje().
     * Parameter $termin predstavuje konkretny jeden nazov elementu xml aj s pripadnymi atributmi, ak
     * su tieto zadane v hranatych zatvorkach[], napr. <Nazov_obce zdroj="RES">, kde $termin = 'Nazov_obce[zdroj="RES"]'.
     * Funkcia pomocou reg. vyrazov a funkcii na pracu s retazcami vytvori zaciatocny a koncovy element a vrati obsah
     * ktory je medzi nimi ako pole.
     * 
    */
    private function nacitajUdaj($termin, $xml_obsah) {
        $atribut = '';
        if (strpos($termin, '[')) {
            $reg_vyraz = '#\[(.*?)\]#';
            preg_match($reg_vyraz, $termin, $pole);
            $atribut = $pole[1];
            $termin = str_replace($pole[0], '', $termin);
            $element_zaciatok = '<' . $termin . '.*?' . $atribut . '.*?>\s*';
        }
        else {
            $element_zaciatok = '<' . $termin . '.*?>\s*';
        }
        $element_koniec = '</' . $termin . '>\s*';
        $reg_vyraz = '#' . $element_zaciatok . '(.*?)' . $element_koniec . '#sm';
        preg_match_all($reg_vyraz, $xml_obsah, $pole_udaj);
        return $pole_udaj[1];
    }

    /*
     * function nacitajUdaje(array $polozky_nazov): array
     * 
     * Funkcia nacitajUdaje() prijima ako jediny argument pole vo forme
     * kluc => $hodnota, kde $kluc je string ktory ma specialny format, ktory obsahuje
     * nazvy elementov xml s pripadnymi atributmi v hranatych zatvorkach, oddelenych znakom "->".
     * Hodnotou tohto pola je potom retazec, ktory je nazvom poloziek toho obsahu elementov , ktore tvoria jeho kluc.
     * Funkcia teda spracuje tento argument tak, ze ho transformuje do pola podla znaku "->" a jednotlive polozky
     * posiela do pomocnej funkcie nacitajUdaj() ako $termin, pricom druhy paraneter ktory posiela do tejto funkcie
     * je obsah suboru xml, ktory si predtym ulozi do pomocnej premennej $subor. Tato pomocna premenna $subor sa postupnym spracovanim zmensuje,
     * az pokym neostane iba posledny nazov elementu na spracovanie, pricom tento potom z pomocnej funkcie vrati pozadovany obsah,
     * ktory si funkcia uklada do pola $vysledok a toto pole vrati.
     * 
     */
    public function nacitajUdaje($polozky_nazov) { // parameter $polozky_nazov predstavuje pole $nastavenia, predane tejto funkcii
        $nazvy = [];
        $meno = '';
        $vysledok = [];
    
        foreach ($polozky_nazov as $polozka_nazov => $polozka_popis) {
            $nazvy = explode('->', $polozka_nazov);
            $meno = $polozka_popis;
            $subor = $this->xml_subor; // Vytvara sa pomocna premenna $subor, pretoze sa bude prepisovat
    
            foreach ($nazvy as $nazov) { // Iteracia stromovou strukturou xml dokumentu
                if (is_array($subor)) { // Kontrola ci $subor je pole, pretoze pomocna funkcia
                                        // nacitajUdaj() prijima ako druhy parameter retazec, avsak vystupom tejto funkcie je pole,
                                        // pricom v tomto cykle pouzivame vystup tejto pomocnej funkcie nacitajUdaj()
                                        // hned ako jej vstup pri kazdej iteracii cyklu
                    $subor = implode('', $subor); // Ak $subor je pole, zmen ho na string a daj ho ako druhy argument
                                                  // do funkcie nacitajUdaj()
                }
                // Funkcia nacitajUdaj() vrati $subor ako pole a $subor predstavuje obsah prveho elementu
                // teda ak $polozky_nazov su napr. ['Prvy->druhy' => 'Nazov'], tak to bude obsah xml elementu s nazvom Prvy, <Prvy><Druhy>...Obsah...</Druhy></Prvy>,
                // pri druhej iteracii cyklu bude v premennej $subor uz iba obsah elemntu <Druhy>...Obsah...</Druhy> a pod.
                $subor = $this->nacitajUdaj($nazov, $subor);
            }
    
            // V tomto cykle sa formatuje casovy udaj vzniku firmy
            foreach ($subor as $kluc => $udaj) {
                if ($meno == 'Dátum vzniku') {
                    $udaj = date('d. m. Y', strtotime($udaj));
                }
                $vysledok[$meno][] = $udaj; // Nakoniec sa vsetko uklada do asociativneho pola $vysledok, kde
                                            // $key predstavuje nazov polozky, ktory sa pouzije pri vypise udajov do stranky a
                                            // $value predstavuje jednoduche pole kde kazdy obsah elementu je prvkom tohto pola.
            }
        }

        // Tu sa iba spajaju dve polozky nacitane do pola $vysledok, aby sa dosiahol citatelnejsi format vypisu
        if (!empty($vysledok['Obory činností kód'])) {
            for ($i = 0; $i < count($vysledok['Obory činností kód']); $i++) {
                $vysledok['Obory činností (kód-názov)'][] = $vysledok['Obory činností kód'][$i] . ' - ' . $vysledok['Obory činností názov'][$i];
            }
        }
    
        return $vysledok;
    }
}

/*
 * Pole $nastavenia
 * 
 * je asociativne pole, kde kazdy kluc predstavuje cestu ku tomu nazvu elementu, ktoreho obsah nas zaujima,
 * napr. ['ICO' => 'IČO'] toto nam vypise ...obsah... elementu <ICO>...obsah...</ICO>, alebo tento
 * zapis ['Predmety_podnikani->Predmet_podnikani[zdroj="OR"]->Text' => 'Predmet podnikania OR'] nam vypise obsah elemntu, 
 * alebo elementov ak ich je viac s nazvom Text teda <Text></Text>, ktore sa nachadzaju v stromovej strukture:
 * <Predmety_podnikani>
 *     <Predmet_podnikani zdroj="OR">
 *         <Text>
 *             ...obsah 1...
 *         </Text>
 *         <Text>
 *             ...obsah 2...
 *         </Text>
 *     </Predmet_podnikani>
 * </Predmety_podnikani>
 * 
 * Funkcia nacitajUdaje() prijima ako svoj argument toto pole a na jeho zaklade potom vytvori asociativne pole, kde
 * $key bude $value z tohto pola a $value bude $key z tohto pola. Cize ak mam strukturu xml dokumentu:
 * <Predmety_podnikani>
 *     <Predmet_podnikani zdroj="OR">
 *         <Text>
 *             ...obsah 1...
 *         </Text>
 *         <Text>
 *             ...obsah 2...
 *         </Text>
 *     </Predmet_podnikani>
 * </Predmety_podnikani>
 * a pole $nastavenia je:
 * ['Predmety_podnikani->Predmet_podnikani[zdroj="OR"]->Text' => 'Predmet podnikania OR'], tak vystupom funkcie
 * nacitajUdaje($nastavenia) bude pole:
 * [
 *     'Predmet podnikania OR' => [
 *         '...obsah 1...',
 *         '...obsah 2...'
 *     ]
 * ]
 * 
*/
$nastavenia = [
    'Error_text' => 'Zadané IČO nebolo nájdené.', // Tato hodnota pola sa zobrazi iba ak stranka na endpointe nedokazala najst udaje pre zadaane ICO
    'Obchodni_firma' => 'Názov firmy',
    'ICO' => 'IČO',
    'Pravni_forma->Kod_PF' => 'Kód právnej formy',
    'Pravni_forma->Nazev_PF' => 'Názov právnej formy',
    'Datum_vzniku' => 'Dátum vzniku',
    'Kategorie_poctu_pracovniku' => 'Počet zamestnancov',
    'Nazev_statu' => 'Štát',
    'Nazev_obce' => 'Obec',
    'Nazev_casti_obce' => 'Názov časti obce',
    'Nazev_mestske_casti' => 'Názov mestskej časti',
    'Nazev_ulice' => 'Názov ulice',
    'Adresa_UIR->Cislo_domovni' => 'Číslo domu',
    'Adresa_UIR->Cislo_orientacni' => 'Číslo orientačné',
    'Adresa_UIR->PSC' => 'PSČ',
    'NACE' => 'Kód podnikania',
    'Predmety_podnikani->Predmet_podnikani[zdroj="OR"]->Text' => 'Predmet podnikania OR',
    'Predmety_podnikani->Predmet_podnikani[zdroj="RZP"]->Text' => 'Predmet podnikania RZP',
    'Obory_cinnosti->Obor_cinnosti->Kod' => 'Obory činností kód',
    'Obory_cinnosti->Obor_cinnosti->Text' => 'Obory činností názov'
];

?>