<?php

namespace App\Services\AiTools;

class ToolRegistry
{
    /**
     * Get all tool definitions in OpenAI function-calling format.
     * Used by Groq and Cerebras (OpenAI-compatible APIs).
     */
    public static function getOpenAiTools(): array
    {
        return array_map(fn ($tool) => [
            'type' => 'function',
            'function' => $tool,
        ], self::getTools());
    }

    /**
     * Get all tool definitions in Gemini function-calling format.
     */
    public static function getGeminiTools(): array
    {
        $functionDeclarations = [];
        foreach (self::getTools() as $tool) {
            $functionDeclarations[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['parameters'],
            ];
        }

        return [
            ['functionDeclarations' => $functionDeclarations],
        ];
    }

    /**
     * Raw tool definitions (name, description, parameters).
     */
    public static function getTools(): array
    {
        return [
            self::searchProducts(),
            self::getProductDetails(),
            self::getProductImage(),
            self::sendProductImage(),
            self::getCurrentContext(),
            self::getBusinessInfo(),
            self::getDeliveryCharge(),
            self::escalateToHuman(),
        ];
    }

    private static function searchProducts(): array
    {
        return [
            'name' => 'search_products',
            'description' => 'প্রোডাক্ট খুঁজুন নাম, বিবরণ, ব্র্যান্ড, বা ক্যাটাগরি দিয়ে। ফলাফলে প্রোডাক্টের ID, নাম, দাম, স্টক, variant এবং image URL পাবেন। কাস্টমার যেকোনো প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করলে এই tool ব্যবহার করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'সার্চ কুয়েরি — প্রোডাক্টের নাম, বৈশিষ্ট্য, বা বিবরণ (যেমন: "red polo shirt", "blue XL t-shirt")',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'ফলাফলের সর্বোচ্চ সংখ্যা (ডিফল্ট: 5)',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }

    private static function getProductDetails(): array
    {
        return [
            'name' => 'get_product_details',
            'description' => 'একটি নির্দিষ্ট প্রোডাক্টের সম্পূর্ণ তথ্য পান — নাম, দাম, স্টক, variant, image, বিবরণ। product_id বা variant_id দিয়ে কল করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product_id' => [
                        'type' => 'integer',
                        'description' => 'প্রোডাক্টের ID',
                    ],
                    'variant_id' => [
                        'type' => 'integer',
                        'description' => 'variant এর ID (ঐচ্ছিক)',
                    ],
                ],
                'required' => ['product_id'],
            ],
        ];
    }

    private static function getProductImage(): array
    {
        return [
            'name' => 'get_product_image',
            'description' => 'একটি প্রোডাক্টের image URL পান। কাস্টমারকে ছবি দেখাতে চাইলে এই tool ব্যবহার করুন। image URL পাওয়ার পর send_product_image tool দিয়ে পাঠান।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product_id' => [
                        'type' => 'integer',
                        'description' => 'প্রোডাক্টের ID',
                    ],
                    'variant_id' => [
                        'type' => 'integer',
                        'description' => 'variant এর ID (ঐচ্ছিক — variant এর image চাইলে)',
                    ],
                ],
                'required' => ['product_id'],
            ],
        ];
    }

    private static function sendProductImage(): array
    {
        return [
            'name' => 'send_product_image',
            'description' => 'কাস্টমারকে একটি প্রোডাক্টের ছবি পাঠান। product_id দিন, সিস্টেম স্বয়ংক্রিয়ভাবে ছবিটি কাস্টমারকে পাঠাবে। কাস্টমার যদি ছবি দেখতে চায় বা আপনি যদি প্রোডাক্ট দেখাতে চান এই tool ব্যবহার করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product_id' => [
                        'type' => 'integer',
                        'description' => 'প্রোডাক্টের ID',
                    ],
                    'variant_id' => [
                        'type' => 'integer',
                        'description' => 'variant এর ID (ঐচ্ছিক)',
                    ],
                ],
                'required' => ['product_id'],
            ],
        ];
    }

    private static function getCurrentContext(): array
    {
        return [
            'name' => 'get_current_context',
            'description' => 'কাস্টমারের বর্তমান কথোপকথনের কন্টেক্সট পান — কোন প্রোডাক্ট নিয়ে কথা হচ্ছে, কোন variant বাছাই করা হয়েছে, এবং সাম্প্রতিক কথোপকথনের ইতিহাস। ফলো-আপ প্রশ্ন বুঝতে এই tool ব্যবহার করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];
    }

    private static function getBusinessInfo(): array
    {
        return [
            'name' => 'get_business_info',
            'description' => 'বিজনেসের সাধারণ তথ্য পান — নাম, সময়, পেমেন্ট মেথড, রিফান্ড পলিসি, এসকালেশন কন্টাক্ট। কাস্টমার বিজনেস সম্পর্কে জিজ্ঞাসা করলে এই tool ব্যবহার করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];
    }

    private static function getDeliveryCharge(): array
    {
        return [
            'name' => 'get_delivery_charge',
            'description' => 'একটি নির্দিষ্ট এলাকার ডেলিভারি চার্জ জানুন। কাস্টমার ডেলিভারি সম্পর্কে জিজ্ঞাসা করলে এই tool ব্যবহার করুন।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'area' => [
                        'type' => 'string',
                        'description' => 'এলাকার নাম (যেমন: "Dhaka", "Chittagong", "ঢাকা")',
                    ],
                ],
                'required' => ['area'],
            ],
        ];
    }

    private static function escalateToHuman(): array
    {
        return [
            'name' => 'escalate_to_human',
            'description' => 'কাস্টমারকে human agent-কে ট্রান্সফার করুন। কাস্টমার অভিযোগ করছে, সমস্যা হচ্ছে, বা AI সমাধান করতে পারছে না এমন পরিস্থিতিতে এই tool ব্যবহার করুন। কন্টাক্ট তথ্য কাস্টমারকে দেওয়া হবে।',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'reason' => [
                        'type' => 'string',
                        'description' => 'এসকালেশনের কারণ',
                    ],
                ],
                'required' => ['reason'],
            ],
        ];
    }
}
