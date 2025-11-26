<?php 
    $data = [
        "theme" => $data,
        "view" => "default_landing_page"
    ];
    $render_landing_page = apply_filters('render_landing_page', $data);
    echo view($render_landing_page['view'], ['theme' => $render_landing_page['theme']]);
?><?php /**PATH /var/www/vhosts/projectnow.run.place/httpdocs/resources/views/welcome.blade.php ENDPATH**/ ?>