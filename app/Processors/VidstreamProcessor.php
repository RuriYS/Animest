<?php

namespace App\Processors;

use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class VidstreamProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $streamData = $item->get('stream_data');

        if ($streamData) {
            unset($streamData['advertising']);
            unset($streamData['track']);
            unset($streamData['linkiframe']);
            $item->set('stream_data', $streamData);
        }

        return $item;
    }
}
