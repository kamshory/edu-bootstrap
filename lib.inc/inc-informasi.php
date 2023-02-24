<div class="card-container row container container-fluid d-flex justify-content-between">
    <?php
    $sql = "SELECT `edu_info`.* 
    FROM `edu_info` 
    WHERE `edu_info`.`active` = true
    ORDER BY `edu_info`.`info_id` DESC
    LIMIT 0, 2
    ";
    $stmt = $database->executeQuery($sql);
    $numArticle = $stmt->rowCount();
    if($numArticle > 0)
    {
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $content = "";
        foreach($rows as $data)
        {
            $pars = extractParagraph($data['content']);
            foreach($pars as $txt)
            {
                if(!empty($txt))
                {
                    $content = $txt;
                    $content = preg_replace('/[\s]+/', ' ', $content);
                    if(strlen($content) > 100)
                    {
                        $content = substr($content, 0, 100)."&hellip;";
                    }
                }
            }
    ?>
    <div class="<?php echo $picoEdu->trueFalse($numArticle == 1, 'col-sm-12', 'col-sm-6');?>">
        <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo $data['name'];?></h5>
            <p class="card-text"><?php echo $content;?></p>
            <a href="informasi.php?info_id=<?php echo $data['info_id'];?>" class="btn btn-primary">Baca</a>
        </div>
        </div>
    </div>
        <?php
}
}
?>

</div>