<?php

namespace Stanford\YouthCheckReport;

/** @var \Stanford\YouthCheckReport\YouthCheckReport $module */
try {


echo $module->initializeJavascriptModuleObject();

    ?>
    <?php print loadJS('vue/vue-factory/dist/js/app.js') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/vue-select@latest/dist/vue-select.css">


    <div id="app"></div>
    <script>
        window.module = <?=$module->getJavascriptModuleObjectName()?>;
        window.records_list = <?php echo json_encode($module->getRecordsList());?>;
    </script>
    <script src="<?php echo $module->getUrl("frontend_3/public/js/bundle.js") ?>"></script>
    <?php
} catch (\Exception $e) {
    ?>
    <div class="alert alert-danger"><?php echo $e->getMessage(); ?></div>
    <?php
}
