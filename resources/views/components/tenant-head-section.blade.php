  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $title ?? '' }}</title>
  @php
      $settings = tenant_settings_by_group('system');

      $themeSettings = get_batch_settings(['theme.favicon']);
      // $favicon_image = $settings['favicon'];
      $favicon_image_32 = $themeSettings['theme.favicon']
          ? Storage::url($themeSettings['theme.favicon'])
          : url('./img/favicon-32x32.png');

      $favicon_image_16 = $themeSettings['theme.favicon']
          ? Storage::url($themeSettings['theme.favicon'])
          : url('./img/favicon-16x16.png');

      $favicon_image_apple = $themeSettings['theme.favicon']
          ? Storage::url($themeSettings['theme.favicon'])
          : url('./img/apple-touch-icon.php');

  @endphp

  <link rel="icon" type="image/png" sizes="32x32"
      href="{{ !empty($settings['favicon']) ? Storage::url($settings['favicon']) : $favicon_image_32 }}}}">
  <link rel="icon" type="image/png" sizes="16x16"
      href="{{ !empty($settings['favicon']) ? Storage::url($settings['favicon']) : $favicon_image_16 }}">
  <link rel="apple-touch-icon"
      href="{{ !empty($settings['favicon']) ? Storage::url($settings['favicon']) : $favicon_image_apple }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">

  <!-- Styles -->
  @livewireStyles
