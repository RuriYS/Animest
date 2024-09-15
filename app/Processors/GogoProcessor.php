<?php

namespace App\Processors;

use Illuminate\Support\Facades\Log;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class GogoProcessor implements ItemProcessorInterface
{
    use Configurable;
    public function processItem(ItemInterface $item): ItemInterface
    {
        $all = $item->all();
        array_walk($all, function ($value, $key) use ($item) {
            if (in_array($key, ['language', 'status'])) {
                $item->set($key, strtoupper($value));
            }
        });

        Log::debug(json_encode($item->all(), JSON_PRETTY_PRINT));
        return $item;
    }
}
