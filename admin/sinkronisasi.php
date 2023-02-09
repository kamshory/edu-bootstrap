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
            padding: 2px 0;
        }
        .sync-label{
            padding: 2px 0;
        }
    </style>

    <script>
        $(document).ready(function(){
            $(document).on('click', '#start-sync', function(e){
                startSync();
            })
        });

        
    </script>
    <script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.assets/script/sync.js"></script>
    <button class="btn btn-success" id="start-sync">Mulai Sinkronisasi</button>
    <div class="sync-container">
        <h4>Sinkronisasi File</h4>
        <div class="sync-item" data-type="file" data-direction="down" data-step="1">
            <div class="sync-label">Download informasi dari sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="file" data-direction="down" data-step="3">
            <div class="sync-label">Download file sync dari sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="file" data-direction="down" data-step="5">
            <div class="sync-label">Download file pengguna dari sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="file" data-direction="up" data-step="1">
            <div class="sync-label">Upload file pengguna ke sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="file" data-direction="up" data-step="3">
            <div class="sync-label">Upload file sync ke sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="file" data-direction="up" data-step="5">
            <div class="sync-label">Upload informasi ke sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

    </div>
    <div class="sync-container">

        <h4>Sinkronisasi Database</h4>
        <div class="sync-item" data-type="database" data-direction="down" data-step="1">
            <div class="sync-label">Download informasi dari sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="database" data-direction="down" data-step="3">
            <div class="sync-label">Download file sync dari sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="database" data-direction="down" data-step="5">
            <div class="sync-label">Eksekusi query dari file sync</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="database" data-direction="up" data-step="3">
            <div class="sync-label">Upload file sync ke sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="sync-item" data-type="database" data-direction="up" data-step="5">
            <div class="sync-label">Upload informasi ke sync hub</div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>


    <?php
    require_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>