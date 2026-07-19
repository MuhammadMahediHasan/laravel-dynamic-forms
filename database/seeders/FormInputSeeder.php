<?php

namespace MuhammadMahediHasan\Df\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MuhammadMahediHasan\Df\Models\FormInput;

class FormInputSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputs = [
            [
                'type' => 'text',
                'icon' => 'mdi:text-short',
                'name' => 'Text Input',
                'slug' => 'text-input',
                'component' => 'InputText',
            ],
            [
                'type' => 'email',
                'icon' => 'mdi:email',
                'name' => 'Email Input',
                'slug' => 'email-input',
                'component' => 'InputText',
            ],
            [
                'type' => 'number',
                'icon' => 'mdi:numeric',
                'name' => 'Number Input',
                'slug' => 'number-input',
                'component' => 'InputNumber',
            ],
            [
                'type' => 'select',
                'icon' => 'mdi:arrow-down-drop-circle',
                'name' => 'Select',
                'slug' => 'select',
                'component' => 'Select',
            ],
            [
                'type' => 'multiSelect',
                'icon' => 'mdi:check-all',
                'name' => 'Multi Select',
                'slug' => 'multi-select',
                'component' => 'MultiSelect',
            ],
            [
                'type' => 'radio',
                'icon' => 'mdi:radiobox-marked',
                'name' => 'Radio Button',
                'slug' => 'radio-button',
                'component' => 'RadioButton',
            ],
            [
                'type' => 'checkbox',
                'icon' => 'mdi:checkbox-marked',
                'name' => 'Checkbox',
                'slug' => 'checkbox',
                'component' => 'Checkbox',
            ],
            [
                'type' => 'date',
                'icon' => 'mdi:calendar',
                'name' => 'Date',
                'slug' => 'date',
                'component' => 'DatePicker',
            ],
            [
                'type' => 'file',
                'icon' => 'mdi:file-upload',
                'name' => 'File Upload',
                'slug' => 'file-upload',
                'component' => 'File',
            ],
            [
                'type' => 'group',
                'icon' => 'mdi:folder',
                'name' => 'Field Group',
                'slug' => 'group',
                'component' => 'Group',
            ],
            [
                'type' => 'header',
                'icon' => 'mdi:format-header-1',
                'name' => 'Section Header',
                'slug' => 'section-header',
                'component' => 'Header',
            ],
        ];

        foreach ($inputs as $input) {
            FormInput::updateOrCreate(
                ['slug' => $input['slug']],
                [
                    'type' => $input['type'],
                    'icon' => $input['icon'],
                    'name' => $input['name'],
                    'component' => $input['component'],
                    'active' => true,
                ]
            );
        }
    }
}
