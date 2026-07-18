<?php

namespace MuhammadMahediHasan\Df\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MuhammadMahediHasan\Df\Models\FormResponse;

class FormResponseSubmitted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public FormResponse $response
    ) {}
}
