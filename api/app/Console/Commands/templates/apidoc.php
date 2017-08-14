<link href="https://cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">
<link href="https://cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

<!-- REST API doc template for WIKI -->
<h3><?=$doc['index']?>.&nbsp;<?=$doc['title']?>&nbsp;</h3>
<div style="margin: 0 30px 0 30px">
<table class="table table-bordered table-striped">
    <tbody>
    <tr>
        <td class="confluenceTd"><p><strong>API</strong></p></td>
        <td class="confluenceTd" colspan="4"><p><?=$doc['httpMethod']?>&nbsp;<a href='<?=$doc['url']?>'><?=$doc['url']?></a></p></td>
    </tr>
    <tr>
        <td class="confluenceTd"><p><strong>注释</strong></p></td>
        <td class="confluenceTd" colspan="4"><?=$doc['comment']?></td>
    </tr>
    <!-- URL 参数 -->
    <?php if (count($doc['urlParams']) > 0):?>
    <tr>
        <td class="confluenceTd" rowspan="<?=count($doc['urlParams']) + 1?>"><p><strong>URL参数 </strong></p></td>
        <td class="confluenceTd"><p><strong>名称</strong></p></td>
        <td class="confluenceTd"><p><strong>类型及范围</strong></p></td>
        <td class="confluenceTd"><p><strong>说明</strong></p></td>
        <td class="confluenceTd"><p><strong>必选</strong></p></td>
    </tr>
        <?php foreach ($doc['urlParams'] as $param):?>
    <tr>
        <td class="confluenceTd"><p><?=$param['name']?></p></td>
        <td class="confluenceTd"><p><?=$param['type']?></p></td>
        <td class="confluenceTd"><p><?=$param['comment']?></p></td>
        <td class="confluenceTd"><p><?=$param['required']?></p></td>
    </tr>
        <?php endforeach;?>
    <?php endif;?>

    <!-- Form表单 参数 -->
    <?php if (count($doc['formParams']) > 0):?>
        <tr>
            <td class="confluenceTd" rowspan="<?=count($doc['formParams']) + 1?>"><p><strong>表单参数 </strong></p></td>
            <td class="confluenceTd"><p><strong>名称</strong></p></td>
            <td class="confluenceTd"><p><strong>类型及范围</strong></p></td>
            <td class="confluenceTd"><p><strong>说明</strong></p></td>
            <td class="confluenceTd"><p><strong>必选</strong></p></td>
        </tr>
        <?php foreach ($doc['formParams'] as $param):?>
            <tr>
                <td class="confluenceTd"><p><?=$param['name']?></p></td>
                <td class="confluenceTd"><p><?=$param['type']?></p></td>
                <td class="confluenceTd"><p><?=$param['comment']?></p></td>
                <td class="confluenceTd"><p><?=$param['required']?></p></td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>

    <!-- 返回值 字段 -->
    <?php if (count($doc['retFields']) > 0):?>
        <tr>
            <td class="confluenceTd" rowspan="<?=count($doc['retFields']) + 1?>"><p><strong>返回值字段 </strong></p></td>
            <td class="confluenceTd"><p><strong>名称</strong></p></td>
            <td class="confluenceTd"><p><strong>类型及范围</strong></p></td>
            <td class="confluenceTd"><p><strong>说明</strong></p></td>
            <td class="confluenceTd"><p><strong>其他</strong></p></td>
        </tr>
        <?php foreach ($doc['retFields'] as $ret):?>
            <tr>
                <td class="confluenceTd"><p><?=$ret['name']?></p></td>
                <td class="confluenceTd"><p><?=$ret['type']?></p></td>
                <td class="confluenceTd"><p><?=$ret['comment']?></p></td>
                <td class="confluenceTd"><p></p></td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>

    <!-- 返回值status 说明 -->
    <?php if (count($doc['statusEnum']) > 0):?>
        <tr>
            <td class="confluenceTd" rowspan="<?=count($doc['statusEnum']) + 1?>"><p><strong>返回值状态 </strong></p></td>
            <td class="confluenceTd"><p><strong>值</strong></p></td>
            <td class="confluenceTd"><p><strong>说明</strong></p></td>
        </tr>
        <?php foreach ($doc['statusEnum'] as $ret):?>
            <tr>
                <td class="confluenceTd"><p><?=$ret['value']?></p></td>
                <td class="confluenceTd"><p><?=$ret['comment']?></p></td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>


    <?php foreach ($doc['examples'] as $example):?>
        <tr>
            <td class="confluenceTd" rowspan="1"><p><strong>调用示例</strong></p></td>
            <td class="confluenceTd" colspan="4">
                <p>
                    <?=$example?>
                </p>
            </td>
        </tr>
    <?php endforeach;?>

    <tr>
        <td class="confluenceTd" rowspan="3"><p><strong>返回值示例(data)</strong></p></td>
        <td class="confluenceTd" colspan="4">
            <p>
                <?=$doc['retVal']?>
            </p>
        </td>
    </tr>


    </tbody>
</table>
</div>