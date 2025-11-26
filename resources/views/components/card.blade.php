<div
  {{ $attributes->merge(['class' => 'bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600']) }}>
  @isset($header)
    <div
      {{ $header->attributes->class(['border-b border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600']) }}>
      {{ $header }}
    </div>
  @endisset
  @isset($content)
    <div {{ $content->attributes->class(['px-4 py-4 sm:p-6']) }}>
      {{ $content }}
    </div>
  @endisset
  @isset($footer)
    <div
      {{ $footer->attributes->class(['border-t bg-slate-50 dark:bg-transparent rounded-b-lg border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600']) }}>
      {{ $footer }}
    </div>
  @endisset
</div>
