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
                'icon' => 'system-uicons:write',
                'name' => 'Text Input',
                'slug' => 'text-input',
                'component' => 'InputText',
            ],
            [
                'type' => 'email',
                'icon' => 'mage:email',
                'name' => 'Email Input',
                'slug' => 'email-input',
                'component' => 'InputText',
            ],
            [
                'type' => 'number',
                'icon' => 'octicon:number-24',
                'name' => 'Number Input',
                'slug' => 'number-input',
                'component' => 'InputNumber',
            ],
            [
                'type' => 'select',
                'icon' => 'fluent:select-all-on-20-regular',
                'name' => 'Select',
                'slug' => 'select',
                'component' => 'Select',
            ],
            [
                'type' => 'multiSelect',
                'icon' => 'fluent:select-all-on-20-regular',
                'name' => 'Multi Select',
                'slug' => 'multi-select',
                'component' => 'MultiSelect',
            ],
            [
                'type' => 'radio',
                'icon' => 'system-uicons:radio-on',
                'name' => 'Radio Button',
                'slug' => 'radio-button',
                'component' => 'RadioButton',
            ],
            [
                'type' => 'checkbox',
                'icon' => 'proicons:checkbox-checked',
                'name' => 'Checkbox',
                'slug' => 'checkbox',
                'component' => 'Checkbox',
            ],
            [
                'type' => 'date',
                'icon' => 'lets-icons:date-today-duotone',
                'name' => 'Date',
                'slug' => 'date',
                'component' => 'DatePicker',
            ],
            [
                'type' => 'file',
                'icon' => 'solar:paperclip-linear',
                'name' => 'File Upload',
                'slug' => 'file-upload',
                'component' => 'File',
            ],
            [
                'type' => 'group',
                'icon' => 'mdi:folder-outline',
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
