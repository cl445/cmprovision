<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'device', 'storage', 'image_id', 'label_id', 'label_moment',
        'eeprom_firmware', 'eeprom_settings', 'verify'
    ];

    protected $casts = [
        'verify' => 'boolean'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }

    public function scripts()
    {
        return $this->belongsToMany(Script::class);
    }

    public function cms()
    {
        return $this->hasMany(Cm::class);
    }

    public function isActive()
    {
        return $this->id == self::getActiveId();
    }

    public static function getActive()
    {
        $activeId = self::getActiveId();
        if (!$activeId) {
            return null;
        }
        return self::find($activeId);
    }

    public static function getActiveId()
    {
        $setting = Setting::find('active_project');
        return $setting ? intval($setting->value) : null;
    }

    public function delete()
    {
        if ($this->isActive()) {
            Setting::where('key', 'active_project')->delete();
        }

        parent::delete();
    }
}
