  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $title ?? '' }}</title>
  @php

      $settings = get_batch_settings(['theme.favicon']);
      $favicon_image = $settings['theme.favicon'];
  @endphp
  <link rel="icon" type="image/png" sizes="32x32"
      href="{{ $favicon_image ? Storage::url($favicon_image) : url('./img/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16"
      href="{{ $favicon_image ? Storage::url($favicon_image) : url('./img/favicon-16x16.png') }}">
  <link rel="apple-touch-icon"
      href="{{ $favicon_image ? Storage::url($favicon_image) : url('./img/apple-touch-icon.php') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap">

  <!-- Styles -->
  @livewireStyles
