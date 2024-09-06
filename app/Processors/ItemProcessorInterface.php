<?php

namespace App\Processors;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\Support\ConfigurableInterface;

interface ItemProcessorInterface extends ConfigurableInterface
{
    public function process(ConfigurableInterface $item): ItemInterface;
}
