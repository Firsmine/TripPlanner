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
            $resData['template'] = [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug
            ];
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
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function reorder(Request $request, string $slug)
    {
        $check = $this->checkAccess($slug, $request->user());
        if ($check['status'] !== 200) {
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);
        }
        $data = Validator::make($request->all(), [
            'blocks' => 'required|array',
            'blocks.*' => 'required|integer|distinct|exists:itinerary_blocks,id',
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 422);
        }
        DB::transaction(function () use ($request, $check) {
            foreach ($request->blocks as $index => $blockId) {
                ItineraryBlock::where('id', $blockId)->where('itinerary_id', $check['itinerary']->id)->update(['position' => $index + 1]);
            }
        });
        return response()->json([
            'status' => 'success',
            'message' => 'Blocks ordered successful'
        ]);
    }

    public function update(Request $request, string $slug, string $blockId)
    {
        $check = $this->checkAccess($slug, $request->user());
        if ($check['status'] !== 200) {
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);
        }
        $block = ItineraryBlock::where('id', $blockId)->where('itinerary_id', $check['itinerary']->id)->first();
        if (!$block) {
            return response()->json([
                'status' => 'error',
                'message' => 'Block not found',
            ], 404);
        }
        $data = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|integer|exists:template_fields,id',
            'fields.*.value' => 'nullable|string'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 422);
        }
        foreach ($request->fields as $fieldInput) {
            BlockField::where('template_field_id', $fieldInput['field_id'])
                ->where('itinerary_block_id', $block->id)
                ->update(['value' => $fieldInput['value']]);
        }
        $block->load('fields.template');
        $formattedFields = $block->fields->map(function ($fieldValue) {
            return [
                'id' => $fieldValue->template->id,
                'name' => $fieldValue->template->name,
                'slug' => $fieldValue->template->slug,
                'type' => $fieldValue->template->type,
                'value' => $fieldValue->value,
            ];
        })->values();
        $resData = $block->unsetRelation('fields')->toArray();
        $resData['fields'] = $formattedFields;

        return response()->json([
            'status' => 'success',
            'message' => 'Block fields updated successful',
            'data' => $resData
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $slug, string $blockId)
    {
        $check = $this->checkAccess($slug, $request->user());
        if ($check['status'] !== 200) {
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);
        }
        $block = ItineraryBlock::where('id', $blockId)->where('itinerary_id', $check['itinerary']->id)->first();
        if (!$block) {
            return response()->json([
                'status' => 'error',
                'message' => 'Block not found',
            ], 404);
        }
        $block->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Block removed successful'
        ], 200);
    }
}
