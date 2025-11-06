<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Short Link
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-800">✓ {{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Short Link</h2>

                <form method="POST" action="{{ route('links.store') }}" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label for="original_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Original URL <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="url" 
                            id="original_url"
                            name="original_url"
                            value="{{ old('original_url') }}"
                            placeholder="https://example.com/your-long-url"
                            required
                            class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('original_url') border-red-500 @enderror"
                        >
                        @error('original_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="custom_alias" class="block text-sm font-medium text-gray-700 mb-2">
                            Custom Alias <span class="text-gray-400 text-xs">(optional)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">{{ url('/') }}/</span>
                            <input 
                                type="text" 
                                id="custom_alias"
                                name="custom_alias"
                                value="{{ old('custom_alias') }}"
                                placeholder="my-custom-alias"
                                class="flex-1 border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('custom_alias') border-red-500 @enderror"
                            >
                        </div>
                        @error('custom_alias')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">3-50 characters, letters, numbers, and hyphens only</p>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Title <span class="text-gray-400 text-xs">(optional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="Give this link a memorable name"
                            class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit"
                            class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition-colors duration-200"
                        >
                            Create Short Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>