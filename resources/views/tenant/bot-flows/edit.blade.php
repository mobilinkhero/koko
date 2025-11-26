<x-app-layout>
  <link rel="stylesheet" href="{{ asset('location/leaflet.css') }}" crossorigin="" />
      <link rel="stylesheet" href="{{ asset('location/fullscreen.css') }}" />
  <x-slot:title>
    {{ t('create_flow') }}
  </x-slot:title>
  <div class="mx-auto h-full">
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
      <div class="w-full overflow-x-auto bg-white dark:bg-gray-800">
        <div id="bot-flow-builder" data-flow-id="{{ $flow->id }}" class="w-full">
          <bot-flow-builder></bot-flow-builder>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
<script src="{{ asset('location/leaflet.js') }}" crossorigin=""></script>

    <script src="{{ asset('location/fullscreen.js') }}"></script>
<script>
  // Personal Assistant data for AI node
  window.personalAssistantData = @json(apply_filters('botflow.personal_assistant', $flow));

  // AI Assistant module enabled flag
  window.isAiAssistantModuleEnabled = @json($isAiAssistantModuleEnabled);

  // List of available personal assistants for selection in AI Assistant node
  window.personalAssistantsList = @json($personalAssistantsList ?? []);

  // Debug: Log personal assistants list
  console.log('=== PERSONAL ASSISTANTS DEBUG ===');
  console.log('personalAssistantsList:', window.personalAssistantsList);
  console.log('Count:', window.personalAssistantsList?.length || 0);
  console.log('Full data:', JSON.stringify(window.personalAssistantsList, null, 2));

  // Meta allowed extensions
  window.metaAllowedExtensions = @json(get_meta_allowed_extension());
</script>
