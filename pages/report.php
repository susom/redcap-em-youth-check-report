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

    <script src="<?php echo $module->getUrl("frontend/dist/youth_check_report.umd.js") ?>"></script>
    <div id="youth-check-report"></div>
    <script>
        window.module = <?=$module->getJavascriptModuleObjectName()?>;
        window.records_list = <?php echo json_encode($module->getRecordsList());?>;
        window.addEventListener('DOMContentLoaded', function (event) {
            const componentPromise = window.renderVueComponent(youth_check_report, '#youth-check-report')
            componentPromise.then(component => {
                console.log('component is ready')
            })
        })
    </script>
    <?php
} catch (\Exception $e) {
    ?>
    <div class="alert alert-danger"><?php echo $e->getMessage(); ?></div>
    <?php
}
