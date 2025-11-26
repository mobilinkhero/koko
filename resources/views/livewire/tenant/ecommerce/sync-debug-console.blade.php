{{-- Debug Console for Sync Issues --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c🔧 SYNC DEBUG MODE ACTIVE', 'background: #222; color: #00ff00; font-size: 16px; padding: 10px;');
    
    // Listen for Livewire events
    window.addEventListener('sync-started', (event) => {
        console.log('%c📊 SYNC STARTED', 'background: #0066cc; color: white; padding: 5px;');
        console.log('Tenant ID:', event.detail?.tenantId || 'Unknown');
    });
    
    window.addEventListener('table-creating', (event) => {
        console.log('%c🔨 CREATING TABLE', 'background: #ff9900; color: white; padding: 5px;');
        console.log('Table Name:', event.detail?.tableName || 'Unknown');
        console.log('Columns:', event.detail?.columns || 'Unknown');
    });
    
    window.addEventListener('table-created', (event) => {
        console.log('%c✅ TABLE CREATED', 'background: #00cc00; color: white; padding: 5px;');
        console.log('Table Name:', event.detail?.tableName || 'Unknown');
    });
    
    window.addEventListener('sync-error', (event) => {
        console.error('%c❌ SYNC ERROR', 'background: #cc0000; color: white; padding: 5px;');
        console.error('Error:', event.detail?.message || 'Unknown error');
    });
    
    window.addEventListener('sync-completed', (event) => {
        console.log('%c🎉 SYNC COMPLETED', 'background: #00cc00; color: white; padding: 5px;');
        console.log('Products Synced:', event.detail?.synced || 0);
        console.log('Errors:', event.detail?.errors || 0);
    });
    
    // Log all Livewire events for debugging
    Livewire.hook('commit', ({ component, commit, respond }) => {
        if (commit.calls.length > 0) {
            console.log('%cLivewire Call:', 'color: #6600cc;', commit.calls[0].method);
        }
    });
    
    Livewire.hook('message.processed', (message, component) => {
        if (message.response?.serverMemo?.errors) {
            console.error('%cLivewire Errors:', 'color: #cc0000;', message.response.serverMemo.errors);
        }
    });
});
</script>
