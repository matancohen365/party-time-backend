<?php

namespace App\Jobs;

use App\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadTrack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * @var \App\Track
     */
    protected $track;

    /**
     * Create a new job instance.
     *
     * @param \App\Track $track
     */
    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->setStatus(Track::STATUS_PROCESSING);

        if( ! $this->track->file->exists()) {

            $this->track->file =
                $this->track->video->download()
                    ->setTags($this->track->getMetadata())->getFile();

        }
        
        sleep(3);

        $this->setStatus(Track::STATUS_SUCCESS);
    }

    /**
     * @param string $status
     */
    protected function setStatus($status)
    {
        $this->track->status = $status;

        $this->track->save();
    }

    /**
     * The job failed to process.
     */
    public function failed()
    {
        $this->setStatus(Track::STATUS_ERROR);

        if($this->track->file->exists()) {

            $this->track->file->delete();

        }
    }
}
