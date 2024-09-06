<?php

namespace App\Console\Commands;

use App\Scrapers\VidStream\VidStreamScraper;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class VidstreamGetPage extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vidstream:getpage
                            {id : Video/Episode ID}
                            {--S|save : Whether to save it to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scraper = new VidStreamScraper();
        $result = $scraper->getPage($this->argument('id'), $this->option('save'));

        $this->info(print_r($result, true));
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'id' => 'ID?',
        ];
    }
}
