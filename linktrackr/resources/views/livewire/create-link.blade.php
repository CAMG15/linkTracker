<div class="max-w-2xl mx-auto">
    @if($showSuccess && $generatedLink)
        <!-- Success State -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h3 class="text-lg font-semibold text-green-800">Link Created Successfully!</h3>
            </div>

            <!-- Short URL Display -->
            <div class="bg-white border border-green-200 rounded-md p-4 mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Short URL</label>
                <div class="flex items-center gap-2">
                    <input 
                        type="text" 
                        readonly 
                        value="{{ $generatedLink->short_url }}"
                        class="flex-1 bg-gray-50 border border-gray-300 rounded-md px-3 py-2 text-sm"
                        id="shortUrlInput"
                    >
                    <button 
                        type="button"
                        onclick="copyToClipboard()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm font-medium"
                    >
                        Copy
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <a 
                    href="{{ route('links.show', $generatedLink) }}" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm font-medium"
                >
                    View Analytics
                </a>
                <button 
                    type="button"
                    wire:click="createAnother"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm font-medium"
                >
                    Create Another
                </button>
            </div>
        </div>
    @else
        <!-- Creation Form -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Short Link</h2>

            <form wire:submit.prevent="createLink" class="space-y-5">
                
                <!-- Original URL -->
                <div>
                    <label for="originalUrl" class="block text-sm font-medium text-gray-700 mb-2">
                        Original URL <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="url" 
                        id="originalUrl"
                        wire:model.defer="originalUrl"
                        placeholder="https://example.com/your-long-url"
                        class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('originalUrl') border-red-500 @enderror"
                    >
                    @error('originalUrl')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Alias (Optional) -->
                <div>
                    <label for="customAlias" class="block text-sm font-medium text-gray-700 mb-2">
                        Custom Alias <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-sm">{{ url('/') }}/</span>
                        <input 
                            type="text" 
                            id="customAlias"
                            wire:model="customAlias"
                            placeholder="my-custom-alias"
                            class="flex-1 border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('customAlias') border-red-500 @enderror"
                        >
                    </div>
                    {{-- Sección corregida para manejar aliasError y validation error correctamente --}}
                    @error('customAlias')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @elseif($aliasError)
                        <p class="mt-1 text-sm text-red-600">{{ $aliasError }}</p>
                    @enderror
                    
                    <p class="mt-1 text-xs text-gray-500">3-50 characters, letters, numbers, and hyphens only</p>
                </div>

                <!-- Title (Optional) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <input 
                        type="text" 
                        id="title"
                        wire:model.defer="title"
                        placeholder="Give this link a memorable name"
                        class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Create Short Link</span>
                        <span wire:loading class="flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>

            </form>
        </div>
    @endif
</div>

<script>
function copyToClipboard() {
    const input = document.getElementById('shortUrlInput');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('bg-green-600');
    button.classList.remove('bg-blue-600');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-blue-600');
    }, 2000);
}
</script>