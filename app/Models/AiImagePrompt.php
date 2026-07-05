<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiImagePrompt extends Model
{
    protected $fillable = ['prompt_text'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.database.central_connection');
    }

    public static function getActive(): self
    {
        return static::firstOrCreate(
            [],
            ['prompt_text' => static::defaultPrompt()]
        );
    }

    public static function defaultPrompt(): string
    {
        return 'তুমি একটি প্রোডাক্ট ইমেজ বিশ্লেষণ করছো। নিচের নিয়মগুলো কঠোরভাবে মেনে চলো: ১) ইমেজের উপর থাকা ads, banner, watermark, text overlay, price tag, discount sticker, বা যেকোনো UI element সম্পর্কে কিছু লিখবে না। ২) শুধুমাত্র মূল প্রোডাক্টটি বর্ণনা করো — প্রোডাক্টের ধরন, রঙ, ডিজাইন, ম্যাটেরিয়াল, আনুমানিক সাইজ। ৩) প্রোডাক্ট ছাড়া অন্য কিছু (মানুষ, গাড়ি, রুম, প্রকৃতি) দেখতে পারলে তা বর্ণনা করো। ৪) ১৫০ শব্দের বেশি না লিখে সংক্ষেপে লেখো।';
    }
}
