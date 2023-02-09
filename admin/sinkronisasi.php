<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
$cfg->page_title = "Sinkronisasi Data";

if(!empty($school_id)) 
{
    require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
    ?>
    <h4>Sinkronisasi File</h4>
    <div class="">
        <div>Download informasi dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Download file dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Upload informasi ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Upload file ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Update informasi ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>


    <h4>Sinkronisasi Database</h4>
    <div class="">
        <div>Download informasi dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Download query dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Eksekusi query dari sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Upload informasi ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <div class="">
        <div>Upload query ke sync hub</div>
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>


    <?php
    require_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>