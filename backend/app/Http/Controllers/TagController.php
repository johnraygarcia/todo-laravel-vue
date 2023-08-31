<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Support\Facades\Response;

class TagController extends Controller
{
    public function getAll()
    {
        $tags = Tag::all();

        return Response::json($tags);
    }
}
