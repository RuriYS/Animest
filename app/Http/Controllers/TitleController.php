<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTitle;
use App\Models\Title;

class TitleController extends ControllerAbstract {
    public function show(string $id) {
        $title = Title::find($id);

        if (!$title) {
            ProcessTitle::dispatchSync($id, false);
            $title = Title::find($id);
        }

        return response()->json([
            'errors' => $title ? null : "Title not found, it's either invalid or doesn't exist",
            'query'  => $id,
            'result' => $title?->toArray(),
        ]);
    }
}
