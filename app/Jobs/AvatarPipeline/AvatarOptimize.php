<?php

namespace App\Jobs\AvatarPipeline;

use \Carbon\Carbon;
use Image as Intervention;
use App\{Avatar, Profile};
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AvatarOptimize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $profile;
    protected $current;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Profile $profile, $current)
    {
        $this->profile = $profile;
        $this->current = $current;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $avatar = $this->profile->avatar;
        $file = storage_path("app/$avatar->media_path");

        try {
            $img = Intervention::make($file)->orientate();
            $img->fit(200, 200, function ($constraint) {
                $constraint->upsize();
            });
            $quality = config('pixelfed.image_quality');
            $img->save($file, $quality);

            $avatar = Avatar::whereProfileId($this->profile->id)->firstOrFail();
            $avatar->thumb_path = $avatar->media_path;
            $avatar->change_count = ++$avatar->change_count;
            $avatar->last_processed_at = Carbon::now();
            $avatar->save();
            $this->deleteOldAvatar($avatar->media_path, $this->current);
        } catch (Exception $e) {
            
        }
    }

    protected function deleteOldAvatar($new, $current)
    {
        if(storage_path('app/' . $new) == $current) {
            return;
        }
        if(is_file($current)) {
            @unlink($current);
        }
    }
}
