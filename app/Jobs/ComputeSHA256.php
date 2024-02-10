<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Image;
use Exception;

class ComputeSHA256 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Image for which the hash should be computed.
     *
     * @var \App\Models\Image
     */
    public $image;

    public $tries = 1;
    public $timeout = 7200;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->image->sha256)
        {
            $this->image->sha256 = hash_file("sha256", $this->image->imagepath());
            $this->updateImage();
        }
        if (!$this->image->uncompressed_sha256)
        {
            $cmd = $this->getDecompressorCmd();

            /* We want to know both the sha256 of uncompressed image and uncompressed size.
               In absense of a standard function that gives us both,
               just use tee to pipe the output of the decompressor to both sha256sum
               and wc */

            $response = $this->runDecompressorCmd($cmd);

            /* Output of sha256sum is like: "002bc976b7f95f3803b6d0b85c022911840a175268d5d2145c2b0a358892afb9  -" */
            if ($response['ret'] == 0 && preg_match("/^([a-z0-9]+)  /", $response['sha256sum'], $r))
            {
                $this->image->uncompressed_sha256 = $r[1];
                $this->image->uncompressed_size = intval($response['wc']);
            }
            else
            {
                throw new Exception("Error decompressing/calculating sha256. Ret code=".$response['ret']." sha256sum=".$response['sha256sum']." wc=".$response['wc']);
            }

            $this->updateImage();
        }
    }

    private function getDecompressorCmd()
    {
        switch ($this->image->filename_extension)
        {
            case 'gz':
                return "gzip -dc";
            case 'xz':
                return "xz -dc";
            case 'bz2':
                return "bunzip2 -dc";
            case 'zst':
                return "unzstd -dc";
            default:
                throw new Exception("Unsupported image file extension");
        }
    }

    private function runDecompressorCmd($decompressorCmd)
    {
        $cmd = $decompressorCmd." ".escapeshellarg($this->image->imagepath())." | tee >(sha256sum>&2) | LC_ALL=C wc --bytes";
        $cmd = 'bash -c "'.$cmd.'"';

        $desc = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"], /* wc */
            2 => ["pipe", "w"]  /* sha256sum */
        ];
        $pipes = null;

        $proc = proc_open($cmd, $desc, $pipes);
        if ($proc === false) {
            throw new Exception("Error starting decompressor/sha256sum/wc");
        }

        fclose($pipes[0]);
        $sha256sum = stream_get_contents($pipes[2]);
        $wc = trim(stream_get_contents($pipes[1]));
        fclose($pipes[1]);
        fclose($pipes[2]);
        $ret = proc_close($proc);

        return ['ret'=>$ret, 'sha256sum'=>$sha256sum, 'wc'=>$wc];
    }

    private function updateImage()
    {
        $this->image->save();
    }
}
