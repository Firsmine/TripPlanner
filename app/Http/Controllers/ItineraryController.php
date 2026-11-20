<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItineraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $itinerary = auth()->user()->itinerary()->latest()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'get all itineraries successful',
            'data' => ['itineraries' => $itinerary]
        ], 200);
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required|string',
            'slug' => 'required|string|unique:itineraries,slug|regex:/^[a-z-0-9-]+$/',
            'summary' => 'nullable|string',
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 422);
        }
        $itinerary = auth()->user()->itinerary()->create($data->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Itinerary created successful',
            'data' => $itinerary
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $slug)
    {
        $itinerary = $request->user()->itinerary()->with('blocks.template.fields')->where('slug', $slug)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Get itinerary successful',
            'data' => $itinerary
        ], 200);
    }

    public function update(Request $request, string $slug)
    {
        $data = Validator::make($request->all(), [
            'title' => 'sometimes|string',
            'slug' => 'sometimes|string|unique:itineraries,slug|regex:/^[a-z-0-9-]+$/',
            'summary' => 'nullable|string',
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 422);
        }
        $itinerary = $request->user()->itinerary()->where('slug', $slug)->first();
        if (!$itinerary) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }
        $itinerary->update($data->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Itinerary updated successful',
            'data' => $itinerary->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $slug)
    {
        $itinerary = $request->user()->itinerary()->where('slug', $slug)->first();
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden access'
            ], 403);
        }
        if (!$itinerary) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }
        $itinerary->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Itinerary deleted successful',
        ], 200);
    }
}
