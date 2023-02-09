<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
$cfg->page_title = "Sinkronisasi Data";

if(!empty($school_id)) 
{
    require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
    ?>
    <style>
        .sync-container{
            margin-bottom: 20px;
        }
        .sync-item{
            padding: 5px 0;
        }
    </style>
    <div class="sync-container">
    <h4>Sinkronisasi File</h4>
    <div class="sync-item">
        <div>Download informasi dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Download file sync dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Download file pengguna dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Upload file pengguna ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Upload file sync ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Upload informasi ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    </div>
    <div class="sync-container">

    <h4>Sinkronisasi Database</h4>
    <div class="sync-item">
        <div>Download informasi dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Download file sync dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Eksekusi query dari file sync</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Upload informasi ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="sync-item">
        <div>Upload file sync ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    </div>


    <?php
    require_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>