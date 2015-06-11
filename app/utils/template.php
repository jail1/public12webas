<?php

class TemplateRenderer {
    public function render ($tpl, $locals=null) {
        if ($locals != null) {
            extract($locals);
        }
        ob_start();
        include APP_PATH . '/templates/' . $tpl . '.tpl.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}

$app->template = new TemplateRenderer();
