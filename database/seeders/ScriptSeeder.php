<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ScriptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pathToScripts = base_path('scripts');

        $scripts = [
            [
                'name' => 'Resize ext4 partition',
                'filename' => 'resize_ext4_partition.sh',
                'script_type' => 'postinstall',
                'priority' => 50,
                'bg' => false,
                'replace' => ['$STORAGE', '$PART2'],
                'with' => ['/dev/sda1', '/dev/sda2'],
            ],
            [
                'name' => 'Add dtoverlay=dwc2 to config.txt',
                'filename' => 'add_dtoverlay_config.sh',
                'script_type' => 'postinstall',
                'priority' => 100,
                'bg' => false,
                'replace' => ['$PART1'],
                'with' => ['/dev/mmcblk0p1'],
            ],
            [
                'name' => 'Format eMMC as pSLC (one time settable only)',
                'filename' => 'format_emmc_slc.sh',
                'script_type' => 'preinstall',
                'priority' => 100,
                'bg' => false,
                'replace' => ['$MAXSIZEKB'],
                'with' => ['/dev/mmcblk0'],
            ],
        ];

        foreach ($scripts as $script) {
            $filepath = $pathToScripts . '/' . $script['filename'];
            if (File::exists($filepath)) {
                $scriptContent = File::get($filepath);
                $command = str_replace(
                    $script['replace'],
                    array_map('escapeshellarg', $script['with']),
                    $scriptContent
                );

                // Only insert script into database if it's not empty
                if (trim($command)) {
                    DB::table('scripts')->insert([
                        'name' => $script['name'],
                        'script_type' => $script['script_type'],
                        'priority' => $script['priority'],
                        'bg' => $script['bg'],
                        'script' => $command
                    ]);
                } else {
                    echo "The script is empty for: $filepath ";
                }
            } else {
                echo "File does not exist: $filepath ";
            }
        }
    }
}
