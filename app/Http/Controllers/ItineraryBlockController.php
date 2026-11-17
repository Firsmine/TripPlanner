<?php

namespace App\Http\Controllers;

use App\Models\BlockField;
use App\Models\Itinerary;
use App\Models\ItineraryBlock;
use App\Models\Template;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItineraryBlockController extends Controller
{
    public function checkAccess($slug, $user)
    {
        $itinerary = Itinerary::where('slug', $slug)->first();
        if (!$itinerary)
            return [
                'status' => 404,
                'message' => 'Not found'
            ];
        if ($itinerary->user_id !== $user->id)
            return [
                'status' => 403,
                'message' => 'Forbidden access'
            ];
        return [
            'status' => 200,
            'itinerary' => $itinerary
        ];
    }

    public function store(Request $request, $slug)
    {
        $check = $this->checkAccess($slug, $request->user());
        if ($check['status'] !== 200) {
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);
        }

        $data = Validator::make($request->all(), [
            'template_id' => 'required|integer|exists:templates,id',
            'position' => 'required|integer|min:1'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 422);
        }

        $itinerary = $check['itinerary'];
        DB::beginTransaction();
        try {
            $template = Template::with('fields')->find($request->template_id);
            if (!$template) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template not found'
                ], 404);
            }

            $block = ItineraryBlock::create([
                'itinerary_id' => $itinerary->id,
                'template_id' => $request->template_id,
                'position' => $request->position,
            ]);
            foreach ($template->fields as $field) {
                BlockField::create([
                    'itinerary_block_id' => $block->id,
                    'template_field_id' => $field->id,
                    'value' => null,
                ]);
            }
            DB::commit();
            $formattedFields = $template->fields->map(function ($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'slug' => $f->slug,
                    'type' => $f->type,
                    'value' => null,
                ];
            })->values();
            $resData = $block->toArray();
            $resData['fields'] = $formattedFields;

            return response()->json([
                'status' => 'success',
                'message' => 'Block added successful',
                'data' => $resData
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Server error',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ItineraryBlock $itineraryBlock)
    {
        //
    }

    public function update(Request $request, ItineraryBlock $itineraryBlock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItineraryBlock $itineraryBlock)
    {
        //
    }
}
