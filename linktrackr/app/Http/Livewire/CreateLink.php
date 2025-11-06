<?php

namespace App\Http\Livewire;

use App\Models\Link;
use App\Services\ShortCodeGenerator;
use App\Services\QRCodeService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateLink extends Component
{
    // Public properties (bound to form inputs)
    public $originalUrl = '';
    public $customAlias = '';
    public $title = '';
    
    // Component state
    public $generatedLink = null;
    public $showSuccess = false;
    public $aliasError = '';

    
    // Validation rules
    protected $rules = [
        'originalUrl' => 'required|url|max:2048',
        'customAlias' => 'nullable|string|min:3|max:50|regex:/^[a-zA-Z0-9-]+$/',
        'title' => 'nullable|string|max:255',
    ];

    // Custom validation messages
    protected $messages = [
        'originalUrl.required' => 'Please enter a URL to shorten',
        'originalUrl.url' => 'Please enter a valid URL (including http:// or https://)',
        'customAlias.min' => 'Custom alias must be at least 3 characters',
        'customAlias.max' => 'Custom alias cannot exceed 50 characters',
        'customAlias.regex' => 'Custom alias can only contain letters, numbers, and hyphens',
    ];

    /**
     * Validate custom alias in real-time
     */
    public function updatedCustomAlias($value)
    {
        $this->aliasError = '';
        
        if (empty($value)) {
            return;
        }

        $codeGenerator = app(ShortCodeGenerator::class);
        $validation = $codeGenerator->validateCustomAlias($value);
        
        if (!$validation['valid']) {
            $this->aliasError = $validation['error'];
        }
    }

    /**
     * Create the short link
     */
    public function createLink()
    {
         dd([
            'originalUrl' => $this->originalUrl,
            'customAlias' => $this->customAlias,
            'title' => $this->title,
        ]);
        // Validate inputs
        $this->validate();
        
        // Additional validation for custom alias
        if ($this->customAlias) {
            $codeGenerator = app(ShortCodeGenerator::class);
            $validation = $codeGenerator->validateCustomAlias($this->customAlias);
            
            if (!$validation['valid']) {
                $this->addError('customAlias', $validation['error']);
                return;
            }
        }

        try {
            // Generate short code
            $codeGenerator = app(ShortCodeGenerator::class);
            $shortCode = $codeGenerator->generate();

            // Create link
            $link = Link::create([
                'user_id' => Auth::id(),
                'short_code' => $shortCode,
                'original_url' => $this->originalUrl,
                'custom_alias' => $this->customAlias ?: null,
                'title' => $this->title ?: null,
            ]);

            // Generate QR codes
            $qrCodeService = app(QRCodeService::class);
            $qrCodes = $qrCodeService->generate(
                url($link->custom_alias ?? $link->short_code),
                $link->short_code
            );

            $link->update([
                'qr_code_png' => $qrCodes['png'],
                'qr_code_svg' => $qrCodes['svg'],
            ]);

            // Update UI state
            $this->generatedLink = $link;
            $this->showSuccess = true;
            
            // Emit event for parent components
            $this->emit('linkCreated', $link->id);
            
            // Show success message
            session()->flash('message', 'Short link created successfully!');

        } catch (\Exception $e) {
            $this->addError('general', 'An error occurred. Please try again.');
            // \Log::error('Link creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Reset form for creating another link
     */
    public function createAnother()
    {
        $this->reset(['originalUrl', 'customAlias', 'title', 'generatedLink', 'showSuccess', 'aliasError']);
    }

    /**
     * Copy short URL to clipboard (triggers JavaScript)
     */
    public function copyToClipboard()
    {
        $this->emit('copyToClipboard', $this->generatedLink->short_url);
    }

    public function render()
    {
        return view('livewire.create-link');
    }
}
