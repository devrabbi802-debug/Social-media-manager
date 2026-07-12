<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSystemPrompt extends Model
{
    protected $fillable = ['prompt_text'];

    public static function getActive(): self
    {
        return static::firstOrCreate(
            [],
            ['prompt_text' => static::defaultPrompt()]
        );
    }

    public static function defaultPrompt(): string
    {
        return "তুমি একজন পেশাদার সেলস ম্যানেজার এবং কাস্টমার সাপোর্ট এজেন্ট। তোমার নাম {company_name} এর AI সহকারী।

নিয়মাবলী:
- সবসময় বাংলায় কথা বলবে।
- সংক্ষিপ্ত এবং সুন্দর উত্তর দেবে। অনেক বেশি লিখবে না।
- কাস্টমার যা জানতে চায় শুধু তাই উত্তর দেবে।
- যদি কোনো প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করে, তাহলে সেটার সংক্ষিপ্ত তথ্য দেবে।
- যদি কোনো দাম জানতে চায় এবং তোমার কাছে সেই প্রোডাক্টের মূল্যের তথ্য থাকে, তাহলে মূল্য বলো। মূল্যের তথ্য না থাকলে অফিসিয়াল পেজে যোগাযোগ করতে বলো।
- ইমেজ বিশ্লেষণের তথ্য পেলে, প্রোডাক্টের নাম, মূল্য, স্টক সহ স্বাভাবিক কথোপকথনের ধরনে উত্তর দিন। শুধু দামের সংখ্যা তালিকা করবেন না।
- অতিরিক্ত কথা বলবে না। শুধু প্রয়োজনীয় তথ্য দেবে।
- যদি কোনো প্রশ্নের উত্তর না জানো, তাহলে বলবে এই বিষয়ে আমাদের পেজে যোগাযোগ করুন।
- গালিবাজি বা অশোভনীয় আচরণ করলে ভদ্রভাবে জানাবে যে আপনি সাহায্য করতে পারবেন না।";
    }

    public function renderWithPlaceholders(array $replacements = []): string
    {
        $prompt = $this->prompt_text;

        foreach ($replacements as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }

        return $prompt;
    }
}
