<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use MuhammadMahediHasan\Df\Actions\StoreFormBuilderAction;
use MuhammadMahediHasan\Df\Actions\UpdateFormBuilderAction;
use MuhammadMahediHasan\Df\Actions\SubmitFormResponseAction;
use MuhammadMahediHasan\Df\Actions\ValidateSubmissionAction;
use MuhammadMahediHasan\Df\Database\Seeders\FormInputSeeder;
use MuhammadMahediHasan\Df\Events\FormResponseSubmitted;
use MuhammadMahediHasan\Df\Models\DynamicForm;
use MuhammadMahediHasan\Df\Models\FormInput;
use MuhammadMahediHasan\Df\Models\FormResponse;
use App\Models\User;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Seed the package input fields dictionary
    $this->seed(FormInputSeeder::class);
});

test('it can seed form inputs catalog', function () {
    expect(FormInput::count())->toBeGreaterThan(0);
    $textInput = FormInput::where('slug', 'text-input')->first();
    expect($textInput)->not->toBeNull();
    expect($textInput->component)->toBe('InputText');
});

test('it can build a dynamic form recursively', function () {
    $textInput = FormInput::where('slug', 'text-input')->first();
    $groupInput = FormInput::where('slug', 'group')->first();

    $action = app(StoreFormBuilderAction::class);

    $form = $action->execute(
        data: [
            'name' => 'General Survey',
            'type' => 'Survey',
            'slug' => 'general-survey',
            'description' => 'A decoupled dynamic survey form',
            'status' => 'Active',
        ],
        elements: [
            [
                'id' => 'temp_group_1',
                'form_input_id' => $groupInput->id,
                'type' => 'group',
                'label' => ['en' => 'Personal Info Group', 'bn' => 'ব্যক্তিগত তথ্য গ্রুপ'],
                'sort' => 1,
            ],
            [
                'id' => 'temp_text_1',
                'form_input_id' => $textInput->id,
                'type' => 'text',
                'label' => ['en' => 'Full Name', 'bn' => 'সম্পূর্ণ নাম'],
                'required' => true,
                'parent_key' => 'temp_group_1',
                'sort' => 2,
            ],
        ]
    );

    expect(DynamicForm::count())->toBe(1);
    expect($form->name)->toBe('General Survey');
    expect($form->inputs)->toHaveCount(2);

    $inputs = $form->inputs()->get();

    // Verify parent ID is mapped correctly
    $group = $inputs->where('label.en', 'Personal Info Group')->first();
    $text = $inputs->where('label.en', 'Full Name')->first();

    expect($group)->not->toBeNull();
    expect($text)->not->toBeNull();
    expect($text->parent_id)->toBe($group->id);
});

test('it can update form elements and delete removed ones', function () {
    $textInput = FormInput::where('slug', 'text-input')->first();
    $form = DynamicForm::create([
        'name' => 'Editable Form',
        'type' => 'Survey',
        'slug' => 'editable-form',
    ]);

    $formInput = $form->inputs()->create([
        'df_form_input_id' => $textInput->id,
        'label' => ['en' => 'Old Question'],
        'sort' => 1,
    ]);

    $action = app(UpdateFormBuilderAction::class);
    $action->execute(
        form: $form,
        data: ['name' => 'Updated Form Name'],
        elements: [
            [
                'id' => $formInput->id,
                'form_input_id' => $textInput->id,
                'type' => 'text',
                'label' => ['en' => 'Updated Question Name'],
            ],
            [
                'form_input_id' => $textInput->id,
                'type' => 'text',
                'label' => ['en' => 'New Question Added'],
            ]
        ]
    );

    $form->refresh();
    expect($form->name)->toBe('Updated Form Name');
    expect($form->inputs)->toHaveCount(2);
    expect($form->inputs()->pluck('label')->pluck('en')->all())
        ->toContain('Updated Question Name')
        ->toContain('New Question Added')
        ->not->toContain('Old Question');
});

test('it validates dynamic submission required constraints and options', function () {
    $textInput = FormInput::where('slug', 'text-input')->first();
    $selectInput = FormInput::where('slug', 'select')->first();

    $form = DynamicForm::create([
        'name' => 'Validation Form',
        'type' => 'Survey',
        'slug' => 'validation-form',
    ]);

    $requiredField = $form->inputs()->create([
        'df_form_input_id' => $textInput->id,
        'label' => ['en' => 'Required Field'],
        'required' => true,
    ]);

    $selectField = $form->inputs()->create([
        'df_form_input_id' => $selectInput->id,
        'label' => ['en' => 'Preferred Choice'],
        'options' => [
            'en' => [['label' => 'Yes', 'value' => 'yes'], ['label' => 'No', 'value' => 'no']],
            'bn' => [['label' => 'হ্যাঁ', 'value' => 'yes'], ['label' => 'না', 'value' => 'no']]
        ],
    ]);

    $validator = app(ValidateSubmissionAction::class);

    // Empty submission for required field
    $errors = $validator->execute($form->id, [
        ['dynamicFormInputId' => $requiredField->id, 'value' => ''],
        ['dynamicFormInputId' => $selectField->id, 'value' => 'invalid-choice'],
    ]);

    expect($errors)->toHaveKey('items.0.value', 'This field is required.');
    expect($errors)->toHaveKey('items.1.value', 'The selected value is invalid.');
});

test('it successfully stores form response items and fires event', function () {
    Event::fake([FormResponseSubmitted::class]);

    $textInput = FormInput::where('slug', 'text-input')->first();
    $form = DynamicForm::create([
        'name' => 'Submission Test Form',
        'type' => 'Survey',
        'slug' => 'submission-test-form',
    ]);

    $field = $form->inputs()->create([
        'df_form_input_id' => $textInput->id,
        'label' => ['en' => 'Username', 'bn' => 'ইউজারনেম'],
    ]);

    // Create a dummy respondent
    $respondent = User::factory()->create();

    $action = app(SubmitFormResponseAction::class);
    $response = $action->execute(
        formId: $form->id,
        respondent: $respondent,
        subject: null,
        items: [
            [
                'dynamicFormInputId' => $field->id,
                'value' => 'john_doe',
            ]
        ],
        metadata: [
            'lat' => 23.8103,
            'lon' => 90.4125,
            'meta_data' => ['device' => 'Mobile Safari'],
        ]
    );

    expect(FormResponse::count())->toBe(1);
    expect($response->respondent_id)->toBe($respondent->id);
    expect($response->respondent_type)->toBe(get_class($respondent));
    expect($response->lat)->toEqual(23.8103);
    expect($response->meta_data)->toHaveKey('device', 'Mobile Safari');

    expect($response->items)->toHaveCount(1);
    $item = $response->items()->first();
    expect($item->value)->toBe('john_doe');

    Event::assertDispatched(FormResponseSubmitted::class, function ($event) use ($response) {
        return $event->response->id === $response->id;
    });
});

test('it handles and validates dynamic locale sets (en, fr, es)', function () {
    // Inject dynamic locale configurations
    config(['dynamic-forms.locales' => ['en', 'fr', 'es']]);

    $selectInput = FormInput::where('slug', 'select')->first();

    $form = DynamicForm::create([
        'name' => 'Multi Locale Form',
        'type' => 'Survey',
        'slug' => 'multi-locale-form',
    ]);

    $selectField = $form->inputs()->create([
        'df_form_input_id' => $selectInput->id,
        'label' => ['en' => 'Select Language', 'fr' => 'Choisir la langue', 'es' => 'Seleccione el idioma'],
        'options' => [
            'en' => [['label' => 'English', 'value' => 'en_val']],
            'fr' => [['label' => 'Français', 'value' => 'fr_val']],
            'es' => [['label' => 'Español', 'value' => 'es_val']],
        ],
    ]);

    $validator = app(ValidateSubmissionAction::class);

    // Valid choices for any configured locale should pass
    $errorsVal1 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $selectField->id, 'value' => 'fr_val'],
    ]);
    expect($errorsVal1)->toBeEmpty();

    $errorsVal2 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $selectField->id, 'value' => 'es_val'],
    ]);
    expect($errorsVal2)->toBeEmpty();

    // Invalid choice (e.g. bn choice or a nonexistent choice) should fail
    $errorsVal3 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $selectField->id, 'value' => 'bn_val'],
    ]);
    expect($errorsVal3)->toHaveKey('items.0.value', 'The selected value is invalid.');
});

test('it handles checkbox validation and stores array values in the json field', function () {
    $checkboxInput = FormInput::where('slug', 'checkbox')->first();
    if (!$checkboxInput) {
        $checkboxInput = FormInput::create([
            'name' => 'Checkbox',
            'slug' => 'checkbox',
            'component' => 'Checkbox',
        ]);
    }

    $form = DynamicForm::create([
        'name' => 'Checkbox Test Form',
        'type' => 'Survey',
        'slug' => 'checkbox-test-form',
    ]);

    $field = $form->inputs()->create([
        'df_form_input_id' => $checkboxInput->id,
        'label' => ['en' => 'Preferences'],
        'required' => true,
        'options' => [
            'en' => [
                ['label' => 'Option A', 'value' => 'a'],
                ['label' => 'Option B', 'value' => 'b'],
            ]
        ],
    ]);

    $validator = app(ValidateSubmissionAction::class);

    // Empty submission for required checkbox should fail
    $errors1 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $field->id, 'value' => []],
    ]);
    expect($errors1)->toHaveKey('items.0.value', 'This field is required.');

    // Valid choices submission should pass
    $errors2 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $field->id, 'value' => ['a', 'b']],
    ]);
    expect($errors2)->toBeEmpty();

    // Invalid choice submission should fail
    $errors3 = $validator->execute($form->id, [
        ['dynamicFormInputId' => $field->id, 'value' => ['a', 'c']],
    ]);
    expect($errors3)->toHaveKey('items.0.value', 'The selected value is invalid.');

    // Submit and verify JSON array persistence
    $respondent = User::factory()->create();
    $submitAction = app(SubmitFormResponseAction::class);
    $response = $submitAction->execute(
        formId: $form->id,
        respondent: $respondent,
        subject: null,
        items: [
            [
                'dynamicFormInputId' => $field->id,
                'value' => ['a', 'b'],
            ]
        ]
    );

    expect(FormResponse::count())->toBe(1);
    expect($response->items)->toHaveCount(1);
    $item = $response->items()->first();
    expect($item->value)->toBe(['a', 'b']);
});

test('it scopes active form inputs and published forms', function () {
    $textInput = FormInput::where('slug', 'text-input')->first();
    $textInput->update(['active' => false]);

    expect(FormInput::active()->where('slug', 'text-input')->first())->toBeNull();

    $activeForm = DynamicForm::create([
        'type' => 'Survey',
        'name' => 'Active Form',
        'slug' => 'active-form',
        'status' => \MuhammadMahediHasan\Df\Enums\FormStatus::ACTIVE,
        'is_public' => true,
    ]);

    $pendingForm = DynamicForm::create([
        'type' => 'Survey',
        'name' => 'Pending Form',
        'slug' => 'pending-form',
        'status' => \MuhammadMahediHasan\Df\Enums\FormStatus::PENDING,
        'is_public' => true,
    ]);

    $published = DynamicForm::published()->get();
    expect($published->pluck('id'))->toContain($activeForm->id);
    expect($published->pluck('id'))->not->toContain($pendingForm->id);
});

test('it validates allowed respondent and subject morph types', function () {
    $textInput = FormInput::where('slug', 'text-input')->first();
    $action = app(StoreFormBuilderAction::class);
    $form = $action->execute(
        data: [
            'name' => 'Survey',
            'type' => 'Survey',
            'slug' => 'survey-test',
        ],
        elements: [
            [
                'form_input_id' => $textInput->id,
                'label' => ['en' => 'Name'],
            ]
        ]
    );

    $prefix = config('dynamic-forms.route_prefix', 'api/v1');
    $url = "/{$prefix}/dynamic-forms/{$form->id}/submissions";

    // Call submit API endpoint with unauthorized respondent_type
    $response = $this->postJson($url, [
        'respondent_id' => 1,
        'respondent_type' => 'App\Models\NonExistentModel',
        'items' => [
            [
                'dynamicFormInputId' => $form->inputs->first()->id,
                'value' => 'Hello',
            ]
        ]
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['respondent_type']);
});
