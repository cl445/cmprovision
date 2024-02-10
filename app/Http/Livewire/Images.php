<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Image;
use \Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class Images extends Component
{
    use \Livewire\WithFileUploads;

    public $images = [];
    public $maxfilesize, $freediskspace;
    public bool $isOpen = false;
    public bool $os32bit = false;

    protected $listeners = ['refreshImages' => 'loadImages'];

    public function mount()
    {
        $this->loadImages();
        $this->maxfilesize = Cache::remember('maxfilesize', 3600, fn() => UploadedFile::getMaxFilesize());

        $uploadsPath = public_path('uploads');
        if (!file_exists($uploadsPath)) {
            mkdir($uploadsPath, 0755, true);
        }

        $this->freediskspace = Cache::remember('freediskspace', 3600, fn() => min(disk_free_space("/tmp"), disk_free_space($uploadsPath)));
        $this->os32bit = PHP_INT_MAX == 2147483647;
    }


    public function render()
    {
        return view('livewire.images');
    }

    public function loadImages()
    {
        $this->images = Image::orderBy('filename')->orderBy('id')->get();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function delete($id)
    {
        Image::destroy($id);
        $this->loadImages();
        session()->flash('message', 'Image deleted.');
    }

    public function create()
    {
        $this->openModal();
    }

    public function cancel()
    {
        $this->closeModal();
    }
}
