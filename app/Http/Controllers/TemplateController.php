<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::with('fields')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Get all templates successful',
            'data' => ['templates' => $templates]
        ]);
    }


    public function show(string $slug)
    {
        $template = Template::with('fields')->where('slug', $slug)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Get template successful',
            'data' => ['template' => $template]
        ]);
    }
}
