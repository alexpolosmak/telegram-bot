<?php

namespace App\Jobs;

use App\Services\Listeners\MainListener;
use App\Telegram\BotInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Objects\Update as UpdateObject;

class ProcessingTextMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
private $data;

    public function __construct(UpdateObject $data)
    {
        $this->data=$data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MainListener $mainListener, BotInstance $bot)
    {

        $bot = $bot->getBot();
        $mainListener->listen($this->data);
    }
}
