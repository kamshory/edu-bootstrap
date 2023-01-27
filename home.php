<?php
include_once dirname(__FILE__)."/lib.inc/auth.php";
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/home-new.min.css">
<title><?php echo $cfg->app_name;?></title>
</head>

<body>

<div class="banner">
<h1><?php echo $cfg->app_name;?></h1>
<h2>Small Device for Big Goals</h2>
</div>

<div class="container">
    <div class="main-menu">
        <div class="menu-item menu-item-student">
            <div><a href="siswa">Siswa</a></div>
        </div>
        <div class="menu-item menu-item-teacher">
            <div><a href="guru">Guru</a></div>
        </div>
        <div class="menu-item menu-item-school">
            <div><a href="admin">Sekolah</a></div>
        </div>
    </div>
    
    
    <div class="naration">
        <div class="article">
            <h3>Artikel</h3>
			<?php
            $sql = "select `edu_info`.`time_create` , left(`edu_info`.`time_create`, 7) as `month`, count(*) as `count`
            from `edu_info` 
            where `edu_info`.`active` = '1'
            group by `month`
            order by `edu_info`.`info_id` desc
            ";
            $stmt = $database->executeQuery($sql);
            if($stmt->rowCount() > 0)
            {
            ?>
           <ul>
           <?php
           $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

           foreach($rows as $data)
           {
                $period = translateDate(date('F Y', strtotime($data['month'].'-01')));
               ?>
                <li><a href="info.php?option=archive&period=<?php echo $data['month'];?>"><?php echo $period;?> (<?php echo $data['count'];?>)</a></li>
                <?php
           }
           ?>
           </ul>
           <?php
            }
            ?>
        </div>
        <div class="article">
            <h3>Informasi</h3>

			<?php
            $sql = "select `edu_info`.* 
            from `edu_info` 
            where `edu_info`.`active` = '1'
            order by `edu_info`.`info_id` desc
            limit 0, 10
            ";
            $stmt = $database->executeQuery($sql);
            if($stmt->rowCount() > 0)
            {
            ?>
           <ul>
           <?php
           $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

           foreach($rows as $data)
           {
               ?>
                <li><a href="info.php?info_id=<?php echo $data['info_id'];?>"><?php echo $data['name'];?></a></li>
                <?php
           }
           ?>
           </ul>
           <?php
            }
            ?>

        </div>
    </div>
</div>
</body>
</html>
