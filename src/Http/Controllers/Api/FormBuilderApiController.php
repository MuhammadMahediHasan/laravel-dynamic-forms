<?php

namespace MuhammadMahediHasan\Df\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use MuhammadMahediHasan\Df\Actions\StoreFormBuilderAction;
use MuhammadMahediHasan\Df\Actions\UpdateFormBuilderAction;
use MuhammadMahediHasan\Df\Http\Requests\StoreFormBuilderRequest;
use MuhammadMahediHasan\Df\Http\Requests\UpdateFormBuilderRequest;
use MuhammadMahediHasan\Df\Http\Resources\DynamicFormResource;
use MuhammadMahediHasan\Df\Models\DynamicForm;
use MuhammadMahediHasan\Df\Models\FormInput;

class FormBuilderApiController extends Controller
{
    /**
     * Get the catalog of form input components.
     */
    public function getInputs(): JsonResponse
    {
        $inputs = FormInput::active()->get()->map(function ($input) {
            $data = $input->toArray();
            $data['label'] = $input->name;
            return $data;
        });

        return response()->json([
            'data' => $inputs,
        ]);
    }

    /**
     * Store a newly created form.
     */
    public function store(StoreFormBuilderRequest $request, StoreFormBuilderAction $action): JsonResponse
    {
        $validated = $request->validated();

        $form = $action->execute($validated, $validated['elements']);

        return response()->json([
            'message' => 'Dynamic form created successfully.',
            'data' => new DynamicFormResource($form->load('inputs.input')),
        ], 201);
    }

    /**
     * Update an existing form.
     */
    public function update(
        UpdateFormBuilderRequest $request,
        DynamicForm $form,
        UpdateFormBuilderAction $action
    ): JsonResponse {
        $validated = $request->validated();

        $updatedForm = $action->execute($form, $validated, $validated['elements']);

        return response()->json([
            'message' => 'Dynamic form updated successfully.',
            'data' => new DynamicFormResource($updatedForm->load('inputs.input')),
        ]);
    }

    /**
     * Display the specified form.
     */
    public function show(DynamicForm $form): JsonResponse
    {
        return response()->json([
            'data' => new DynamicFormResource($form->load('inputs.input')),
        ]);
    }
}
