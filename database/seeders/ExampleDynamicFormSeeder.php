<?php

namespace MuhammadMahediHasan\Df\Database\Seeders;

use Illuminate\Database\Seeder;
use MuhammadMahediHasan\Df\Models\DynamicForm;
use MuhammadMahediHasan\Df\Models\DynamicFormInput;
use MuhammadMahediHasan\Df\Models\FormInput;
use MuhammadMahediHasan\Df\Enums\FormStatus;

class ExampleDynamicFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure the base FormInput types catalog is seeded first
        $this->call(FormInputSeeder::class);

        $inputMap = FormInput::pluck('id', 'type')->all();

        // 2. Create the main DynamicForm
        $form = DynamicForm::updateOrCreate(
            ['slug' => 'member-profile-survey'],
            [
                'type' => 'Survey',
                'name' => 'Member Profile Survey',
                'description' => 'A comprehensive survey covering all inputs in English and Bengali',
                'status' => FormStatus::ACTIVE,
            ]
        );

        // Clear existing fields to make it repeatable
        $form->inputs()->delete();

        // 3. Define all fields covering all component types
        $fields = [
            [
                'type' => 'header',
                'label' => [
                    'en' => 'Personal Information',
                    'bn' => 'ব্যক্তিগত তথ্য',
                ],
                'placeholder' => [
                    'en' => 'Please provide your official contact details.',
                    'bn' => 'অনুগ্রহ করে আপনার অফিসিয়াল যোগাযোগের বিবরণ প্রদান করুন।',
                ],
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => [
                    'en' => 'Full Name',
                    'bn' => 'পূর্ণ নাম',
                ],
                'placeholder' => [
                    'en' => 'Enter your full name (e.g. John Doe)',
                    'bn' => 'আপনার পূর্ণ নাম লিখুন (যেমন: জন ডো)',
                ],
                'required' => true,
            ],
            [
                'type' => 'email',
                'label' => [
                    'en' => 'Email Address',
                    'bn' => 'ইমেল ঠিকানা',
                ],
                'placeholder' => [
                    'en' => 'Enter your email (e.g. john@example.com)',
                    'bn' => 'আপনার ইমেল লিখুন (যেমন: john@example.com)',
                ],
                'required' => true,
            ],
            [
                'type' => 'group',
                'label' => [
                    'en' => 'Investment Preferences',
                    'bn' => 'বিনিয়োগ পছন্দ',
                ],
                'placeholder' => [
                    'en' => 'Define your investment size and target asset choices.',
                    'bn' => 'আপনার বিনিয়োগের আকার এবং লক্ষ্য সম্পদের পছন্দগুলি নির্ধারণ করুন।',
                ],
                'required' => false,
                'children' => [
                    [
                        'type' => 'number',
                        'label' => [
                            'en' => 'Monthly Investment Target (USD)',
                            'bn' => 'মাসিক বিনিয়োগের লক্ষ্য (USD)',
                        ],
                        'placeholder' => [
                            'en' => 'Enter amount (e.g. 500)',
                            'bn' => 'পরিমাণ লিখুন (যেমন: ৫০০)',
                        ],
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => [
                            'en' => 'Risk Tolerance Level',
                            'bn' => 'ঝুঁকি সহনশীলতার মাত্রা',
                        ],
                        'placeholder' => [
                            'en' => 'Select risk level...',
                            'bn' => 'ঝুঁকির মাত্রা নির্বাচন করুন...',
                        ],
                        'required' => true,
                        'options' => [
                            'en' => [
                                ['value' => 'low', 'option' => 'Low'],
                                ['value' => 'medium', 'option' => 'Medium'],
                                ['value' => 'high', 'option' => 'High'],
                            ],
                            'bn' => [
                                ['value' => 'low', 'option' => 'কম'],
                                ['value' => 'medium', 'option' => 'মাঝারি'],
                                ['value' => 'high', 'option' => 'উচ্চ'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'multiSelect',
                        'label' => [
                            'en' => 'Sectors of Interest',
                            'bn' => 'আগ্রহের খাতসমূহ',
                        ],
                        'placeholder' => [
                            'en' => 'Select one or more sectors...',
                            'bn' => 'এক বা একাধিক খাত নির্বাচন করুন...',
                        ],
                        'required' => false,
                        'options' => [
                            'en' => [
                                ['value' => 'tech', 'option' => 'Technology'],
                                ['value' => 'real_estate', 'option' => 'Real Estate'],
                                ['value' => 'agriculture', 'option' => 'Agriculture'],
                            ],
                            'bn' => [
                                ['value' => 'tech', 'option' => 'প্রযুক্তি'],
                                ['value' => 'real_estate', 'option' => 'আবাসন'],
                                ['value' => 'agriculture', 'option' => 'কৃষি'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'header',
                'label' => [
                    'en' => 'Verification & Security',
                    'bn' => 'যাচাইকরণ এবং নিরাপত্তা',
                ],
                'placeholder' => [
                    'en' => 'Provide verification documents and accept terms.',
                    'bn' => 'যাচাইকরণ নথি প্রদান করুন এবং শর্তাবলী মেনে নিন।',
                ],
                'required' => false,
            ],
            [
                'type' => 'date',
                'label' => [
                    'en' => 'Date of Birth',
                    'bn' => 'জন্ম তারিখ',
                ],
                'placeholder' => [
                    'en' => 'Select your date of birth',
                    'bn' => 'আপনার জন্ম তারিখ নির্বাচন করুন',
                ],
                'required' => true,
            ],
            [
                'type' => 'radio',
                'label' => [
                    'en' => 'Preferential Contact Method',
                    'bn' => 'পছন্দের যোগাযোগ মাধ্যম',
                ],
                'required' => true,
                'options' => [
                    'en' => [
                        ['value' => 'email', 'option' => 'Email'],
                        ['value' => 'phone', 'option' => 'Phone'],
                        ['value' => 'whatsapp', 'option' => 'WhatsApp'],
                    ],
                    'bn' => [
                        ['value' => 'email', 'option' => 'ইমেল'],
                        ['value' => 'phone', 'option' => 'ফোন'],
                        ['value' => 'whatsapp', 'option' => 'হোয়াটসঅ্যাপ'],
                    ],
                ],
            ],
            [
                'type' => 'file',
                'label' => [
                    'en' => 'Upload National ID / Passport',
                    'bn' => 'জাতীয় পরিচয়পত্র / পাসপোর্ট আপলোড করুন',
                ],
                'placeholder' => [
                    'en' => 'Drag & drop or browse your file (PDF, JPG, PNG)',
                    'bn' => 'আপনার ফাইল ড্র্যাগ করুন অথবা ব্রাউজ করুন (PDF, JPG, PNG)',
                ],
                'required' => true,
            ],
            [
                'type' => 'checkbox',
                'label' => [
                    'en' => 'Agreement to Policies',
                    'bn' => 'নীতিমালার সাথে সম্মতি',
                ],
                'required' => true,
                'options' => [
                    'en' => [
                        ['value' => 'terms', 'option' => 'I agree to the Terms of Service'],
                        ['value' => 'verification', 'option' => 'I consent to background verification'],
                    ],
                    'bn' => [
                        ['value' => 'terms', 'option' => 'আমি সেবা শর্তাবলীতে সম্মত'],
                        ['value' => 'verification', 'option' => 'আমি ব্যাকগ্রাউন্ড যাচাইকরণে সম্মতি দিচ্ছি'],
                    ],
                ],
            ],
        ];

        // 4. Save fields with sort indices and structural parent mappings
        $sort = 1;
        foreach ($fields as $fieldData) {
            $parentInput = DynamicFormInput::create([
                'df_form_id' => $form->id,
                'df_form_input_id' => $inputMap[$fieldData['type']],
                'parent_id' => null,
                'label' => $fieldData['label'],
                'placeholder' => $fieldData['placeholder'] ?? null,
                'required' => $fieldData['required'],
                'options' => $fieldData['options'] ?? null,
                'sort' => $sort++,
            ]);

            if (!empty($fieldData['children'])) {
                foreach ($fieldData['children'] as $childData) {
                    DynamicFormInput::create([
                        'df_form_id' => $form->id,
                        'df_form_input_id' => $inputMap[$childData['type']],
                        'parent_id' => $parentInput->id,
                        'label' => $childData['label'],
                        'placeholder' => $childData['placeholder'] ?? null,
                        'required' => $childData['required'],
                        'options' => $childData['options'] ?? null,
                        'sort' => $sort++,
                    ]);
                }
            }
        }
    }
}
