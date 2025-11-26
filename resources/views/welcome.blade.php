@php 
    $data = [
        "theme" => $data,
        "view" => "default_landing_page"
    ];
    $render_landing_page = apply_filters('render_landing_page', $data);
    echo view($render_landing_page['view'], ['theme' => $render_landing_page['theme']]);
@endphp