<?php

namespace MuhammadMahediHasan\Df\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use MuhammadMahediHasan\Df\Actions\SubmitFormResponseAction;
use MuhammadMahediHasan\Df\Actions\ValidateSubmissionAction;
use MuhammadMahediHasan\Df\Http\Requests\SubmitFormRequest;
use MuhammadMahediHasan\Df\Http\Resources\FormResponseResource;
use MuhammadMahediHasan\Df\Models\DynamicForm;

class FormSubmissionApiController extends Controller
{
    /**
     * Store a form submission response.
     */
    public function store(
        SubmitFormRequest $request,
        DynamicForm $form,
        ValidateSubmissionAction $validator,
        SubmitFormResponseAction $submitAction
    ): JsonResponse {
        $validated = $request->validated();

        // Run internal dynamic field constraints validations
        $errors = $validator->execute($form->id, $validated['items']);

        if (! empty($errors)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $respondent = null;
        if (! empty($validated['respondent_type']) && ! empty($validated['respondent_id'])) {
            $respondentModel = $validated['respondent_type'];
            $respondent = $respondentModel::find($validated['respondent_id']);
        }

        $subject = null;
        if (! empty($validated['subject_type']) && ! empty($validated['subject_id'])) {
            $subjectModel = $validated['subject_type'];
            $subject = $subjectModel::find($validated['subject_id']);
        }

        $metadata = [
            'date' => $validated['date'] ?? now()->toDateString(),
            'lat' => $validated['lat'] ?? null,
            'lon' => $validated['lon'] ?? null,
            'meta_data' => $validated['meta_data'] ?? null,
        ];

        $response = $submitAction->execute(
            $form->id,
            $respondent,
            $subject,
            $validated['items'],
            $metadata
        );

        return response()->json([
            'message' => 'Response submitted successfully.',
            'data' => new FormResponseResource($response->load('items')),
        ], 201);
    }
}
