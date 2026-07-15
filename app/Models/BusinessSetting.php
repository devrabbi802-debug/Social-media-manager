<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSetting extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'category_id',
        'sub_category',
        'persona_name',
        'business_hours',
        'off_hours_message',
        'business_description',
        'formality_level',
        'emoji_usage',
        'language_style',
        'greeting_style',
        'price_negotiation',
        'negotiation_limit',
        'bulk_discount_rule',
        'current_promo',
        'delivery_areas',
        'delivery_time',
        'delivery_partner',
        'cod_available',
        'accepted_payment_methods',
        'advance_payment_required',
        'advance_payment_percent',
        'custom_escalation_keywords',
        'escalation_contact',
        'extra_fields_data',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'extra_fields_data' => 'array',
            'price_negotiation' => 'boolean',
            'cod_available' => 'boolean',
            'advance_payment_required' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): ?BusinessCategory
    {
        if (!$this->category_id) {
            return null;
        }

        return BusinessCategory::find($this->category_id);
    }

    public function generateSystemPrompt(): string
    {
        $category = $this->category();
        $categoryName = $category?->name ?? 'সাধারণ';
        $extraData = $this->extra_fields_data ?? [];

        $prompt = "তুমি {$this->persona_name}, {$this->business_name} এর AI সহকারী।

বিজনেস তথ্য:
- ক্যাটাগরি: {$categoryName}";
        if ($this->sub_category) {
            $prompt .= "\n- উপ-ক্যাটাগরি: {$this->sub_category}";
        }
        $prompt .= "\n- বিবরণ: {$this->business_description}";
        $prompt .= "\n- সময়: {$this->business_hours}";
        if ($this->off_hours_message) {
            $prompt .= "\n- অফ-আয়ার মেসেজ: {$this->off_hours_message}";
        }

        $prompt .= "\n\nকমিউনিকেশন স্টাইল:";
        $prompt .= "\n- ফর্মালিটি: {$this->formality_level}";
        $prompt .= "\n- ইমোজি: {$this->emoji_usage}";
        $prompt .= "\n- ভাষা: {$this->language_style}";
        $prompt .= "\n- গ্রিটিং: {$this->greeting_style}";

        $prompt .= "\n\nপ্রাইসিং নীতি:";
        $prompt .= "\n- দরদাম: " . ($this->price_negotiation ? 'হ্যাঁ' : 'না');
        if ($this->price_negotiation && $this->negotiation_limit > 0) {
            $prompt .= "\n- সর্বোচ্চ ছাড়: {$this->negotiation_limit}%";
        }
        if ($this->bulk_discount_rule) {
            $prompt .= "\n- বাল্ক ডিসকাউন্ট: {$this->bulk_discount_rule}";
        }
        if ($this->current_promo) {
            $prompt .= "\n- বর্তমান অফার: {$this->current_promo}";
        }

        $prompt .= "\n\nডেলিভারি তথ্য:";
        if ($this->delivery_areas) {
            $prompt .= "\n- এরিয়াস: {$this->delivery_areas}";
        }
        if ($this->delivery_time) {
            $prompt .= "\n- সময়কাল: {$this->delivery_time}";
        }
        if ($this->delivery_partner) {
            $prompt .= "\n- পার্টনার: {$this->delivery_partner}";
        }
        $prompt .= "\n- COD: " . ($this->cod_available ? 'হ্যাঁ' : 'না');

        $prompt .= "\n\nপেমেন্ট:";
        if ($this->accepted_payment_methods) {
            $prompt .= "\n- মেথড: {$this->accepted_payment_methods}";
        }
        if ($this->advance_payment_required) {
            $prompt .= "\n- অ্যাডভান্স: {$this->advance_payment_percent}% লাগে";
        }

        if (!empty($extraData)) {
            $prompt .= "\n\nক্যাটাগরি-স্পেসিফিক তথ্য:";
            foreach ($extraData as $key => $value) {
                if ($value && $value !== '' && $value !== null) {
                    $fieldName = str_replace('_', ' ', $key);
                    $prompt .= "\n- {$fieldName}: {$value}";
                }
            }
        }

        $prompt .= "\n\nনিয়মাবলী:
- সবসময় বাংলায় কথা বলবে।
- সংক্ষিপ্ত এবং সুন্দর উত্তর দেবে। অনেক বেশি লিখবে না।
- কাস্টমার যা জানতে চায় শুধু তাই উত্তর দেবে।
- যদি কোনো প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করে, তাহলে সেটার সংক্ষিপ্ত তথ্য দেবে।
- মূল্যের তথ্য না থাকলে অফিসিয়াল পেজে যোগাযোগ করতে বলো।
- অতিরিক্ত কথা বলবে না। শুধু প্রয়োজনীয় তথ্য দেবে।
- যদি কোনো প্রশ্নের উত্তর না জানো, তাহলে বলবে এই বিষয়ে আমাদের পেজে যোগাযোগ করুন।
- গালিবাজি বা অশোভনীয় আচরণ করলে ভদ্রভাবে জানাবে যে আপনি সাহায্য করতে পারবেন না।";

        return $prompt;
    }
}
