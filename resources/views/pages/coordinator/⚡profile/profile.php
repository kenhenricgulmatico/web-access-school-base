<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.coordinator')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    #[Locked]
    public string $savedName = ''; // the name as it is now in the database (shown in the header card)
    public string $email = '';
    public string $department = '';
    public string $role = '';

    public $avatar;
    public ?string $currentAvatar = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name          = $user->name ?? '';
        $this->savedName     = $this->name;
        $this->email         = $user->email ?? '';
        $this->department    = $user->department?->department_name ?? 'Not assigned';
        $this->role          = $user->roles->first()?->name ?? 'No role';
        $this->currentAvatar = $user->avatar;
    }

    protected function rules(): array
    {
        return [
            'avatar' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,jpe,png,gif,webp',
                'mimetypes:image/jpeg,image/jpg,image/pjpeg,image/png,image/gif,image/webp',
                'max:5120',
            ],
        ];
    }

    protected $messages = [
        'name.required'    => 'Please enter your name.',
        'name.min'         => 'Your name must be at least 2 characters.',
        'name.max'         => 'Your name can be at most 100 characters.',
        'name.regex'       => 'Your name can only contain letters, spaces, periods, hyphens and apostrophes.',
        'avatar.file'      => 'The uploaded file is not valid. Please try again.',
        'avatar.image'     => 'Only image files are allowed. Documents, videos, and music files cannot be uploaded.',
        'avatar.mimes'     => 'Only image files are allowed — JPG, JPEG, PNG, GIF, or WebP.',
        'avatar.mimetypes' => 'The file content does not match a supported image format.',
        'avatar.max'       => 'Image is too large. Maximum size is 5MB.',
    ];

    /** ✅ Change your own name (email, department and role stay locked). */
    public function saveName(): void
    {
        $this->name = preg_replace('/\s+/u', ' ', trim($this->name));

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\pM .'\-]+$/u"],
        ]);

        if ($this->name === $this->savedName) {
            return;
        }

        Auth::user()->update(['name' => $this->name]);
        $this->savedName = $this->name;

        session()->flash('message', 'Your name has been updated.');

        // The header shows the name too — let it refresh
        $this->dispatch('avatar-updated');
    }

    public function cancelName(): void
    {
        $this->name = $this->savedName;
        $this->resetValidation('name');
    }

    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar');

        if (! $this->avatar) {
            return;
        }

        $user = Auth::user();

        // Delete the old avatar file
        $this->deleteAvatarFile($user->avatar);

        // Make sure the folder exists
        $dir = public_path('avatars');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Write the file explicitly instead of ->move() (avoids empty/corrupt files)
        $filename = Str::uuid() . '.' . $this->avatar->extension();
        file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $this->avatar->get());

        $path = 'avatars/' . $filename;

        $user->update(['avatar' => $path]);

        $this->reset('avatar');
        $this->currentAvatar = $path;

        session()->flash('message', 'Profile photo updated successfully.');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        $this->deleteAvatarFile($user->avatar);

        $user->update(['avatar' => null]);

        $this->currentAvatar = null;

        session()->flash('message', 'Profile photo removed.');
    }

    protected function deleteAvatarFile(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
};
