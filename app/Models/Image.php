<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Image extends Model
{
    use HasFactory;

    protected $casts = ['uncompressed_size' => 'integer'];

    function imagepath()
    {
        return 'uploads/'.$this->filename_on_server;
    }

    function filesize()
    {
        try {
            return Storage::size($this->imagepath());
        } catch (\Exception $e) {
            Log::error("Error getting filesize for {$this->imagepath()}: {$e->getMessage()}");
            return false;
        }
    }

    function delete()
    {
        try {
            if (Storage::exists($this->imagepath())) {
                Storage::delete($this->imagepath());
            }
            parent::delete();
        } catch (\Exception $e) {
            Log::error("Error deleting image {$this->imagepath()}: {$e->getMessage()}");
        }
    }
}
