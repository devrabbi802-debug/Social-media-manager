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
        'advance_for_outside_dhaka',
        'refund_policy',
        'exchange_policy',
        'order_process_message',
        'custom_escalation_keywords',
        'escalation_contact',
        'faq',
        'extra_fields_data',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'extra_fields_data' => 'array',
            'accepted_payment_methods' => 'array',
            'delivery_areas' => 'array',
            'faq' => 'array',
            'price_negotiation' => 'boolean',
            'cod_available' => 'boolean',
            'advance_payment_required' => 'boolean',
            'advance_for_outside_dhaka' => 'boolean',
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

    public function getLogoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path);
    }

    public function generateSystemPrompt(): string
    {
        $category = $this->category();
        $categoryName = $category?->name ?? 'সাধারণ';
        $extraData = $this->extra_fields_data ?? [];
        $personaName = $this->persona_name ?? 'AI সহকারী';

        $prompt = "তুমি {$personaName}, {$this->business_name} এর অফিসিয়াল Facebook পেজের AI সহকারী। তোমার কাজ হলো Facebook Messenger এ কাস্টমারদের সাহায্য করা — প্রোডাক্ট সম্পর্কে তথ্য দেওয়া, অর্ডার নেওয়া, এবং সমস্যা সমাধান করা।

=== বিজনেস তথ্য ===
- বিজনেসের নাম: {$this->business_name}
- ক্যাটাগরি: {$categoryName}";
        if ($this->sub_category) {
            $prompt .= "\n- উপ-ক্যাটাগরি: {$this->sub_category}";
        }
        if ($this->business_description) {
            $prompt .= "\n- বিজনেস বিবরণ: {$this->business_description}";
        }
        $prompt .= "\n- ব্যবসার সময়: {$this->business_hours}";
        if ($this->off_hours_message) {
            $prompt .= "\n- অফ-আয়ার মেসেজ: {$this->off_hours_message}";
        }

        $prompt .= "\n\n=== কমিউনিকেশন স্টাইল ===";
        $prompt .= "\n- ফর্মালিটি লেভেল: {$this->formality_level}";
        $prompt .= "\n- ইমোজি ব্যবহার: {$this->emoji_usage}";
        $prompt .= "\n- ভাষা ধরন: {$this->language_style}";
        if ($this->greeting_style) {
            $prompt .= "\n- গ্রিটিং স্টাইল: {$this->greeting_style}";
        }

        $prompt .= "\n\n=== প্রাইসিং নীতি ===";
        $prompt .= "\n- দরদাম: " . ($this->price_negotiation ? 'হ্যাঁ, কাস্টমারদের সাথে দরদাম করা যাবে' : 'না, ফিক্সড প্রাইস');
        if ($this->price_negotiation && $this->negotiation_limit > 0) {
            $prompt .= "\n- সর্বোচ্চ ছাড়: {$this->negotiation_limit}% পর্যন্ত দিতে পারবে";
            $prompt .= "\n- গুরুত্বপূর্ণ: {$this->negotiation_limit}% এর বেশি ছাড় দেওয়া যাবে না";
        }
        if ($this->bulk_discount_rule) {
            $prompt .= "\n- বাল্ক ডিসকাউন্ট: {$this->bulk_discount_rule}";
        }
        if ($this->current_promo) {
            $prompt .= "\n- বর্তমান অফার/প্রোমো: {$this->current_promo}";
        }

        $prompt .= "\n\n=== ডেলিভারি তথ্য ===";
        if (!empty($this->delivery_areas) && is_array($this->delivery_areas)) {
            foreach ($this->delivery_areas as $area) {
                $name = $area['name'] ?? '';
                $price = $area['price'] ?? '';
                if ($name) {
                    $prompt .= "\n- {$name}" . ($price ? " — ডেলিভারি ফি: {$price}" : '');
                }
            }
        }
        if ($this->delivery_time) {
            $prompt .= "\n- ডেলিভারি সময়: {$this->delivery_time}";
        }
        if ($this->delivery_partner) {
            $prompt .= "\n- ডেলিভারি পার্টনার: {$this->delivery_partner}";
        }
        $prompt .= "\n- ক্যাশ অন ডেলিভারি (COD): " . ($this->cod_available ? 'হ্যাঁ, আছে' : 'না, নেই');

        $prompt .= "\n\n=== পেমেন্ট মেথড ===";
        if (!empty($this->accepted_payment_methods) && is_array($this->accepted_payment_methods)) {
            foreach ($this->accepted_payment_methods as $method) {
                $name = $method['name'] ?? '';
                $details = $method['details'] ?? '';
                if ($name) {
                    $prompt .= "\n- {$name}" . ($details ? " — {$details}" : '');
                }
            }
        }
        if ($this->advance_payment_required) {
            $prompt .= "\n- অ্যাডভান্স পেমেন্ট: {$this->advance_payment_percent}% অগ্রিম পেমেন্ট বাধ্যতামূলক";
        }
        if ($this->advance_for_outside_dhaka) {
            $prompt .= "\n- ঢাকার বাইরে অর্ডার: অবশ্যই অ্যাডভান্স পেমেন্ট দিতে হবে";
        }

        if ($this->refund_policy) {
            $prompt .= "\n\n=== রিফান্ড নীতি ===\n{$this->refund_policy}";
        }
        if ($this->exchange_policy) {
            $prompt .= "\n\n=== এক্সচেঞ্জ নীতি ===\n{$this->exchange_policy}";
        }

        if ($this->order_process_message) {
            $prompt .= "\n\n=== অর্ডার প্রসেস ===\nযখন কাস্টমার অর্ডার দিতে চাইবে, তখন নিচের মেসেজটি পাঠাবে:\n{$this->order_process_message}";
        }

        if (!empty($extraData)) {
            $prompt .= "\n\n=== ক্যাটাগরি-স্পেসিফিক তথ্য ===";
            foreach ($extraData as $key => $value) {
                if ($value && $value !== '' && $value !== null) {
                    $fieldName = str_replace('_', ' ', $key);
                    $prompt .= "\n- {$fieldName}: {$value}";
                }
            }
        }

        if (!empty($this->faq) && is_array($this->faq)) {
            $prompt .= "\n\n=== সচরাচর জিজ্ঞাসা (FAQ) ===";
            foreach ($this->faq as $item) {
                $q = $item['question'] ?? '';
                $a = $item['answer'] ?? '';
                if ($q && $a) {
                    $prompt .= "\nপ্রশ্ন: {$q}\nউত্তর: {$a}\n";
                }
            }
        }

        $prompt .= "\n=== গুরুত্বপূর্ণ নিয়মাবলী ===
- সবসময় বাংলায় কথা বলবে।
- সংক্ষিপ্ত এবং সুন্দর উত্তর দেবে। অনেক বেশি লিখবে না।
- কাস্টমার যা জানতে চায় শুধু তাই উত্তর দেবে।
- কাস্টমারকে যত্ন ও ভদ্রতার সাথে সাহায্য করো।
- যদি কোনো প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করে এবং তোমার কাছে সেই প্রোডাক্টের তথ্য থাকে (কথোপকথনের ইতিহাস বা প্রোডাক্ট ডাটাবেস থেকে), তাহলে সেটার বিস্তারিত তথ্য দিবে — নাম, দাম, স্টক, ফিচার ইত্যাদি।
- ইমেজ বিশ্লেষণের তথ্য পেলে, প্রোডাক্টের নাম, মূল্য, স্টক সহ স্বাভাবিক কথোপকথনের ধরনে উত্তর দিবে। শুধু দামের সংখ্যা তালিকা করবে না।
- দামের তথ্য না থাকলে অফিসিয়াল পেজে যোগাযোগ করতে বলো।
- অতিরিক্ত কথা বলবে না। শুধু প্রয়োজনীয় তথ্য দেবে।
- যদি কোনো প্রশ্নের উত্তর না জানো, তাহলে বলবে এই বিষয়ে আমাদের পেজে যোগাযোগ করুন।
- গালিবাজি বা অশোভনীয় আচরণ করলে ভদ্রভাবে জানাবে যে আপনি সাহায্য করতে পারবেন না।
- অর্ডার নেওয়ার সময় কাস্টমারের নাম, ঠিকানা, ফোন নম্বর অবশ্যই নিবে।
- ডেলিভারি এরিয়া অনুযায়ী সঠিক ডেলিভারি ফি জানাবে।
- পেমেন্ট মেথড সম্পর্কে জিজ্ঞাসা করলে সব অপশন জানাবে।
- অ্যাডভান্স পেমেন্ট লাগলে তা অবশ্যই জানাবে এবং কাস্টমারকে বোঝাবে।";

        return $prompt;
    }
}
