<?php
class Quran{

    public $suratName = array(
        "1"=>array("Al Fatihah", "Pembuka", 7),
        "2"=>array("Al Baqarah", "Sapi Betina", 286),
        "3"=>array("Ali Imran", "Keluarga Imran", 200),
        "4"=>array("An Nisa", "Wanita"),
        "5"=>array("Al Ma'idah", "Jamuan"),
        "6"=>array("Al An'am", "Hewan Ternak"),
        "7"=>array("Al-A'raf", "Tempat yang Tertinggi"),
        "8"=>array("Al-Anfal", "Harta Rampasan Perang"),
        "9"=>array("At-Taubah", "Pengampunan"),
        "10"=>array("Yunus", "Nabi Yunus"),
        "11"=>array("Hud", "Nabi Hud"),
        "12"=>array("Yusuf", "Nabi Yusu"),
        "13"=>array("Ar-Ra'd", "Guruh"),
        "14"=>array("Ibrahim", "Nabi Ibrahim"),
        "15"=>array("Al-Hijr", "Gunung Al Hijr"),
        "16"=>array("An-Nahl", "Lebah"),
        "17"=>array("Al-Isra'", "Perjalanan Malam"),
        "18"=>array("Al-Kahf", "Penghuni-penghuni Gua"),
        "19"=>array("Maryam", "Maryam"),
        "20"=>array("Ta Ha", "Ta Ha"),
        "21"=>array("Al-Anbiya", "Nabi-Nabi"),
        "22"=>array("Al-Hajj", "Haji"),
        "23"=>array("Al-Mu'minun", "Orang-orang mukmin"),
        "24"=>array("An-Nur", "Cahaya"),
        "25"=>array("Al-Furqan", "Pembeda"),
        "26"=>array("Asy-Syu'ara'", "Penyair"),
        "27"=>array("An-Naml", "Semut"),
        "28"=>array("Al-Qasas", "Kisah-kisah"),
        "29"=>array("Al-'Ankabut", "Laba-laba"),
        "30"=>array("Ar-Rum", "Bangsa Romawi"),
        "31"=>array("Luqman", "Keluarga Luqman"),
        "32"=>array("As-Sajdah", "Sajdah"),
        "33"=>array("Al-Ahzab", "Golongan-golongan yang Bersekutu"),
        "34"=>array("Saba'", "Kaum Saba'"),
        "35"=>array("Fatir", "Pencipta"),
        "36"=>array("Ya Sin", "Yaasiin"),
        "37"=>array("As-Saffat", "Barisan-barisan"),
        "38"=>array("Sad", "Shaad"),
        "39"=>array("Az-Zumar", "Rombongan-rombongan"),
        "40"=>array("Ghafir", "Yang Mengampuni"),
        "41"=>array("Fussilat", "Yang Dijelaskan"),
        "42"=>array("Asy-Syura", "Musyawarah"),
        "43"=>array("Az-Zukhruf", "Perhiasan"),
        "44"=>array("Ad-Dukhan", "Kabut"),
        "45"=>array("Al-Jasiyah", "Yang Bertekuk Lutut"),
        "46"=>array("Al-Ahqaf", "Bukit-bukit Pasir"),
        "47"=>array("Muhammad", "Nabi Muhammad"),
        "48"=>array("Al-Fath", "Kemenangan"),
        "49"=>array("Al-Hujurat", "Kamar-kamar"),
        "50"=>array("Qaf", "Qaaf"),
        "51"=>array("Az-Zariyat", "Angin yang Menerbangkan"),
        "52"=>array("At-Tur", "Bukit"),
        "53"=>array("An-Najm", "Bintang"),
        "54"=>array("Al-Qamar", "Bulan"),
        "55"=>array("Ar-Rahman", "Yang Maha Pemurah"),
        "56"=>array("Al-Waqi'ah", "Hari Kiamat"),
        "57"=>array("Al-Hadid", "Besi"),
        "58"=>array("Al-Mujadilah", "Wanita yang Mengajukan Gugatan"),
        "59"=>array("Al-Hasyr", "Pengusiran"),
        "60"=>array("Al-Mumtahanah", "Wanita yang Diuji"),
        "61"=>array("As-Saff", "Satu Barisan"),
        "62"=>array("Al-Jumu'ah", "Hari Jum'at"),
        "63"=>array("Al-Munafiqun", "Orang-orang yang Munafik"),
        "64"=>array("At-Tagabun", "Hari Dinampakkan Kesalahan-kesalahan"),
        "65"=>array("At-Talaq", "Talak"),
        "66"=>array("At-Tahrim", "Mengharamkan"),
        "67"=>array("Al-Mulk", "Kerajaan"),
        "68"=>array("Al-Qalam", "Pena"),
        "69"=>array("Al-Haqqah", "Hari Kiamat"),
        "70"=>array("Al-Ma'arij", "Tempat Naik"),
        "71"=>array("Nuh", "Nabi Nuh"),
        "72"=>array("Al-Jinn", "Jin"),
        "73"=>array("Al-Muzzammil", "Orang yang Berselimut"),
        "74"=>array("Al-Muddassir", "Orang yang Berkemul"),
        "75"=>array("Al-Qiyamah", "Kiamat"),
        "76"=>array("Al-Insan", "Manusia"),
        "77"=>array("Al-Mursalat", "Malaikat-Malaikat Yang Diutus"),
        "78"=>array("An-Naba'", "Berita Besar"),
        "79"=>array("An-Nazi'at", "Malaikat-Malaikat Yang Mencabut"),
        "80"=>array("'Abasa", "Ia Bermuka Masam"),
        "81"=>array("At-Takwir", "Menggulung"),
        "82"=>array("Al-Infitar", "Terbelah"),
        "83"=>array("Al-Tatfif", "Orang-orang yang Curang"),
        "84"=>array("Al-Insyiqaq", "Terbelah"),
        "85"=>array("Al-Buruj", "Gugusan Bintang"),
        "86"=>array("At-Tariq", "Yang Datang di Malam Hari"),
        "87"=>array("Al-A'la", "Yang Paling Tinggi"),
        "88"=>array("Al-Gasyiyah", "Hari Pembalasan"),
        "89"=>array("Al-Fajr", "Fajar"),
        "90"=>array("Al-Balad", "Negeri"),
        "91"=>array("Asy-Syams", "Matahari"),
        "92"=>array("Al-Lail", "Malam"),
        "93"=>array("Ad-Duha", "Waktu Matahari Sepenggalahan Naik"),
        "94"=>array("Al-Insyirah", "Melapangkan"),
        "95"=>array("At-Tin", "Buah Tin"),
        "96"=>array("Al-'Alaq", "Segumpal Darah"),
        "97"=>array("Al-Qadr", "Kemuliaan"),
        "98"=>array("Al-Bayyinah", "Pembuktian"),
        "99"=>array("Az-Zalzalah", "Kegoncangan"),
        "100"=>array("Al-'Adiyat", "Berlari Kencang"),
        "101"=>array("Al-Qari'ah", "Hari Kiamat"),
        "102"=>array("At-Takasur", "Bermegah-megahan"),
        "103"=>array("Al-'Asr", "Masa"),
        "104"=>array("Al-Humazah", "Pengumpat"),
        "105"=>array("Al-Fil", "Gajah"),
        "106"=>array("Quraisy", "Suku Quraisy"),
        "107"=>array("Al-Ma'un", "Barang-barang yang Berguna"),
        "108"=>array("Al-Kausar", "Nikmat yang Berlimpah"),
        "109"=>array("Al-Kafirun", "Orang-orang Kafir"),
        "110"=>array("An-Nasr", "Pertolongan"),
        "111"=>array("Al-Lahab", "Gejolak Api"),
        "112"=>array("Al-Ikhlas", "Ikhlas"),
        "113"=>array("Al-Falaq", "Waktu Subuh"),
        "114"=>array("An-Nas", "Umat Manusia"),
        );        

    public function getAyat($s)
    {
        $max = @$this->suratName[$s][2];
        return array();
    }

    public function markTranslation($data, $arr)
    {
        foreach($data as $key=>$row)
        {
            foreach($arr as $v)
            {
                $v = str_replace('"', '', $v);
                $original = $data[$key]['translation'];
                $rep = "/$v/i";
                $new = preg_replace($rep, "<em>\$0</em>", $original);
                $data[$key]['translation'] = $new;
            }
        }
        return $data;
    }

    public function getAyatLabel($ayatKey)
    {
        $arr = explode(":", $ayatKey);
        return $this->suratName[(int) $arr[0]][0]. ' : '.((int) $arr[1]);
    }

    public function arabicNumber($number)
    {
        $num = sprintf("%d", $number);
        return str_replace(array(
            '0', 
            '1', 
            '2', 
            '3', 
            '4', 
            '5', 
            '6', 
            '7', 
            '8', 
            '9'
        ), array(
            '٠', 
            '١', 
            '٢', 
            '٣', 
            '٤', 
            '٥', 
            '٦', 
            '٧', 
            '٨', 
            '٩'
        ), $num);
    }

    public function buildMenu()
    {
        $menu = "";
        foreach($this->suratName as $number=>$names)
        {
            $menu .= '<li><a href="./?s='.$number.'">'.htmlspecialchars($names[0]).'</a></li>';
        }
        return $menu;
    }
    public function buildJuz()
    {
        $menu = "";
        for($number = 1; $number <=30; $number++)
        {
            $menu .= '<li><a href="./?j='.$number.'">Juz '.$number.'</a></li>';
        }
        return $menu;
    }
}
$quran = new \Quran();

$q = @$_GET['q'];
$s = @$_GET['s'];

$scroll = @$_GET['scroll'];


$q = preg_replace('/[^A-Za-z0-9\-\"\' ]/', '', $q); 
$q = str_replace("'", '"', $q);
$result = array();
if($s != '')
{
    $result = $quran->getAyat($s);
}
else 
{
    $result = $quran->getAyat(1);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Al Quran</title>
    <meta http-equiv="Content-Type" content="text/html" charset=utf-8" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Planetbiru">
    <meta name="generator" content="Planetbiru">
 
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="lib.vendors/bootstrap/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="lib.style/signin.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="lib.favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="lib.favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="lib.favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="lib.favs/manifest.json">
    <link rel="mask-icon" href="lib.favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="lib.favs/favicon.ico">
    <meta name="msapplication-config" content="lib.favs/browserconfig.xml">
    <meta name="theme-color" content="#3558BE">

    <script>
        var surat = '<?php echo $s;?>';
        var scroll = '<?php echo $scroll;?>';
    </script>
    
</head>

<body translate="no">
    <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Daftar Isi</h3>
            </div>

            <ul class="list-unstyled components">
                <div class="list-surat">
                    <h4>Daftar Surat</h4>
                    <ul>
                        <?php echo $quran->buildMenu();
                        ?>
                    </ul>
                    
                </div>

                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Link</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="https://planetbiru.com">Our Website</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="https://planetbiru.com">About</a>
                </li>
                <li>
                    <a href="https://planetbiru.com">Contact</a>
                </li>
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="https://github.com/kamshory/Al-Quran" class="download">Download source</a>
                </li>
                <li>
                    <a href="https://planetbiru.com" class="article">Our Website</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content  -->
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">

                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                        <span>Sidebar</span>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="https://planetbiru.com">Website</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="https://www.youtube.com/channel/UCY-qziSbBmJ7iZj-cXqmcMg">YouTube</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="search-form">
                <form action="" method="get">
                    <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($q);?>" autocomplete="off">
                    <input type="submit" value="Cari" class="btn btn-sm btn-success">
                </form>
            </div>

            <div class="main-content">
                <?php
    foreach($result as $data)
    {
        ?>
                <div class="ayat-item" data-anchor="<?php echo str_replace(':', '', $data['ayat_key']);?>">
                    <div class="text arab">
                        <?php
            echo ($data['text']);
            echo ' '.$quran->arabicNumber($data['ayat']);
            ?>

                    </div>
                    <div class="text translation">
                        <?php
            echo $data['translation'];
            ?>
                    </div>
                    <div class="sound">
                        <audio onended="endAudio(this)" onplay="playAudio(this)"
                            data-ayat-key="<?php echo str_replace(':', '', $data['ayat_key']);?>"
                            data-src="<?php echo $audio->createAudio($data);?>"
                            controls></audio>
                    </div>

                    <div class="link-surat">
                        <a
                            href="./?s=<?php echo $data['surat'];?>&scroll=<?php echo str_replace(':', '', $data['ayat_key']);?>">
                            <?php
                    echo $quran->getAyatLabel($data['ayat_key']);
                ?>
                        </a>
                    </div>
                </div>
                <?php
    }
    ?>

            </div>
        </div>
    </div>

    <button type="button" class="btn btn-success btn-floating btn-lg" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>


</body>

</html>