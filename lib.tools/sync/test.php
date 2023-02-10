<?php
header('Content-type: application/json');

if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '1'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    exit();
}


if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '2'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '3'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}


if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '4'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '5'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}


if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '2'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '3'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}




if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '4'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '5'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}
if(
    @$_GET['type'] == 'file'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '6'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}














if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '1'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    exit();
}


if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '2'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '3'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}


if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '4'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'down'
    && @$_GET['step'] == '5'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}




if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '2'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '3'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}




if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '4'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true,
            'recordList'=>array(
                array('recordId'=>'123456', 'executed'=>false),
                array('recordId'=>'123457', 'executed'=>false),
                array('recordId'=>'123458', 'executed'=>false),
                array('recordId'=>'1234568', 'executed'=>false),
                array('recordId'=>'2123457', 'executed'=>false),
                array('recordId'=>'2123458', 'executed'=>false),
                array('recordId'=>'31234564', 'executed'=>false),
                array('recordId'=>'3123457', 'executed'=>false),
                array('recordId'=>'3123458', 'executed'=>false),
                array('recordId'=>'123459', 'executed'=>false)
            )
        )
    );
    exit();
}

if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '5'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}
if(
    @$_GET['type'] == 'database'
    && @$_GET['direction'] == 'up'
    && @$_GET['step'] == '6'
)
{
    echo json_encode(
        array(
            'success'=>true,
            'completed'=>true
        )
    );
    usleep(80000);
    exit();
}
