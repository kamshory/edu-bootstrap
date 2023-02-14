<?php
include_once dirname(__FILE__) . "/lib.inc/functions-pico.php";
include_once dirname(__FILE__) . "/lib.inc/sessions.php";
$pageTitle = "Siswa";
require_once dirname(__FILE__) . "/lib.inc/cfg.pagination.php";
if (isset($_GET['school_id'])) {
  $school_id = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
}
if (empty(@$student_id) && empty(@$teacher_id)) 
{
  include_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
  
  if(isset($school_id) && !empty($school_id)) 
  {
    $sql = "SELECT `edu_school`.*, 
    (SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student`
    WHERE `edu_student`.`school_id` = `edu_school`.`school_id` AND `edu_student`.`gender` = 'M') AS `M`,
    (SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student`
    WHERE `edu_student`.`school_id` = `edu_school`.`school_id` AND `edu_student`.`gender` = 'W') AS `W`
    FROM `edu_school`
    WHERE `edu_school`.`school_id` = '$school_id' 
    ";


    $stmt = $database->executeQuery($sql);
    if ($stmt->rowCount() > 0) {
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
      <h3>Jumlah Siswa <?php echo $data['name']; ?></h3>
      <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
        <tr>
          <td>Laki-Laki</td>
          <td><?php echo $data['M']; ?> orang</td>
        </tr>
        <tr>
          <td>Perempuan</td>
          <td><?php echo $data['W']; ?> orang</td>
        </tr>
        <tr>
          <td>Jumlah</td>
          <td><?php echo $data['M'] + $data['W']; ?> orang</td>
        </tr>
      </table>
    <?php
    }
  }
  include_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
  exit();
}

if (@$_GET['option'] == 'detail') {
  include_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
  $edit_key = kh_filter_input(INPUT_GET, "student_id", FILTER_SANITIZE_STRING_NEW);
  $nt = '';
  $sql = "SELECT `edu_student`.* ,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_create`) AS `admin_create`,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_student`.`admin_edit`) AS `admin_edit`,
(SELECT `edu_class`.`name` FROM `edu_class` WHERE `edu_class`.`class_id` = `edu_student`.`class_id` limit 0,1) AS `class_id`
FROM `edu_student` 
WHERE `edu_student`.`student_id` = '$edit_key'
";

  $stmt = $database->executeQuery($sql);
  if ($stmt->rowCount() > 0) 
  {
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <form name="formedu_student" action="" method="post" enctype="multipart/form-data">
      <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
        <tr>
          <td>NIS</td>
          <td><?php echo $data['reg_number'];?> </td>
        </tr>
        <tr>
          <td>NISN</td>
          <td><?php echo $data['reg_number_national'];?> </td>
        </tr>
        <tr>
          <td>Tingkat</td>
          <td><?php
          echo $picoEdu->getGradeName($data['grade_id']);
              
              ?>
          <td>
        </tr>
        <tr>
          <td>Kelas</td>
          <td><?php echo $data['class_id'];?> </td>
        </tr>
        <tr>
          <td>Nama</td>
          <td><?php echo $data['name'];?> </td>
        </tr>
        <tr>
          <td>Jenis Kelamin</td>
          <td><?php echo $picoEdu->getGenderName($data['gender']);?> </td>
        </tr>
        </table>
        <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
        <tr>
          <td></td>
          <td><input type="button" name="showall" id="showall" value="Tampilkan Semua" class="btn com-button btn-primary" onclick="window.location='<?php echo 'siswa.php'; ?>'" /></td>
        </tr>
      </table>
    </form>
  <?php
  } else {
  ?>
    <div class="warning">Data tidak ditemukan. <a href="<?php echo 'siswa.php'; ?>">Klik di sini untuk kembali.</a></div>
  <?php
  }
  include_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
} else {
  include_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
  $class_id = kh_filter_input(INPUT_GET, "class_id", FILTER_SANITIZE_STRING_NEW);
  ?>
  <div class="search-control">
    <form id="searchform" name="form1" method="get" action="">
      <span class="search-label">Kelas</span>
      <select class="form-control input-select" name="class_id" id="class_id">
        <option value=""></option>
        <?php
        $sql2 = "SELECT * FROM `edu_class` WHERE `active` = true AND `school_id` = '$school_id' ORDER BY `sort_order` ASC ";
        
        echo $picoEdu->createFilterDb(
          $sql2,
          array(
            'attributeList'=>array(
              array('attribute'=>'value', 'source'=>'class_id')
            ),
            'selectCondition'=>array(
              'source'=>'class_id',
              'value'=>$class_id
            ),
            'caption'=>array(
              'delimiter'=>PicoEdu::RAQUO,
              'values'=>array(
                'name'
              )
            )
          )
        );
        
    
        ?>
      </select>
      <span class="search-label">Nama Siswa</span>
      <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
      <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
    </form>
  </div>
  <div class="search-result">
    <?php
    $sql_filter = "";
    
    if($pagination->getQuery()) {
      $pagination->appendQueryName('q');
      $sql_filter .= " AND (`edu_student`.`name` like '%" . addslashes($pagination->getQuery()) . "%' )";
    }
    if ($class_id != 0) {
      $pagination->appendQueryName('class_id');
      $sql_filter .= " AND (`edu_student`.`class_id` = '$class_id' )";
    }

    $nt = '';


    $sql = "SELECT `edu_student`.* , `edu_class`.`name` AS `class_id`, `edu_class`.`sort_order` AS `sort_order`
    FROM `edu_student`
    LEFT JOIN (`edu_class`) ON (`edu_class`.`class_id` = `edu_student`.`class_id`)
    WHERE (1=1) $sql_filter
    ORDER BY `sort_order` ASC, `edu_student`.`name` ASC
    ";

    $stmt = $database->executeQuery($sql);
    $pagination->setTotalRecord($stmt->rowCount());
    if ($class_id == 0) 
    {
      $stmt = $database->executeQuery($sql . $pagination->getLimitSql());
    }

    $pagination->setTotalRecordWithLimit($stmt->rowCount());
    if($pagination->getTotalRecordWithLimit() > 0) {
        $pagination->createPagination('siswa.php', true);
        $paginationHTML = $pagination->buildHTML();

    ?>
      <form name="form1" method="post" action="">
        <style type="text/css">
          @media screen and (max-width:599px) {

            .hide-some-cell tr td:nth-child(2),
            .hide-some-cell tr td:nth-child(3),
            .hide-some-cell tr td:nth-child(5) {
              display: none;
            }
          }

          @media screen and (max-width:399px) {
            .hide-some-cell tr td:nth-child(7) {
              display: none;
            }
          }
        </style>
        <?php
        if ($class_id == 0) {
        ?>
          <div class="d-flex search-pagination search-pagination-top">
            <div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
            <div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
          </div>
        <?php
        }
        ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
          <thead>
            <tr>
              <td width="25">No</td>
              <td>NIS</td>
              <td>NISN</td>
              <td>Nama</td>
              <td>Tingkat</td>
              <td>Kelas</td>
              <td>L/P</td>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = $pagination->getOffset();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $data) {
              $no++;
            ?>
              <tr class="<?php echo $picoEdu->getRowClass($data);?>">
                <td align="right"><?php echo $no;?> </td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['reg_number']; ?></a></td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['reg_number_national']; ?></a></td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['name']; ?></a></td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['grade_id']; ?></a></td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['class_id']; ?></a></td>
                <td><a href="<?php echo 'siswa.php'; ?>?option=detail&student_id=<?php echo $data['student_id']; ?>"><?php echo $data['gender']; ?></a></td>
                </tr>
              <?php
            }
              ?>
          </tbody>
        </table>
        <?php
        if ($class_id == 0) {
        ?>
          <div class="d-flex search-pagination search-pagination-bottom">
            <div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML; ?></div>
            <div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
          </div>
        <?php
        }
        ?>
      </form>
    <?php
    } else if (@$_GET['q'] != '') {
    ?>
      <div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
    <?php
    } else {
    ?>
      <div class="warning">Data tidak ditemukan.</div>
    <?php
    }
    ?>
  </div>

<?php
  include_once dirname(__FILE__) . "/lib.inc/footer.php"; //NOSONAR
}
?>