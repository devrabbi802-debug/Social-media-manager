<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class ChatSelectionService
{
    private const SELECTION_DATA_START = '###SELECTION_DATA_START###';

    private const SELECTION_DATA_END = '###SELECTION_DATA_END###';

    /**
     * Reply theke selection JSON block extract koro.
     *
     * @return array{items: array<int, array<string, mixed>>}|null
     */
    public function extractSelectionData(string $reply): ?array
    {
        $startPos = strpos($reply, self::SELECTION_DATA_START);
        $endPos = strpos($reply, self::SELECTION_DATA_END);

        if ($startPos === false || $endPos === false) {
            return null;
        }

        $jsonString = substr(
            $reply,
            $startPos + strlen(self::SELECTION_DATA_START),
            $endPos - ($startPos + strlen(self::SELECTION_DATA_START))
        );

        $data = json_decode(trim($jsonString), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data['items'])) {
            Log::warning('ChatSelectionService: failed to parse selection JSON', [
                'json' => trim($jsonString),
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        return $data;
    }

    /**
     * Reply theke selection data block remove koro — customer ke dekhabe na.
     * END marker na thakleo START marker theke sesh porjonto strip hoy (invalid/truncated JSON).
     */
    public function removeSelectionDataBlock(string $reply): string
    {
        $startPos = strpos($reply, self::SELECTION_DATA_START);

        if ($startPos === false) {
            return $reply;
        }

        $before = substr($reply, 0, $startPos);
        $endPos = strpos($reply, self::SELECTION_DATA_END);

        if ($endPos === false) {
            $result = trim($before);

            return $result !== '' ? $result : '';
        }

        $after = substr($reply, $endPos + strlen(self::SELECTION_DATA_END));
        $result = trim($before.$after);

        return $result !== '' ? $result : '';
    }

    /**
     * Reply te selection marker (START/END) ase kina — JSON valid na holeo.
     */
    public function containsSelectionDataBlock(string $reply): bool
    {
        return strpos($reply, self::SELECTION_DATA_START) !== false
            || strpos($reply, self::SELECTION_DATA_END) !== false;
    }

    /**
     * AI er output theke selection JSON parse kore conversation er selected_products e
     * merge koro. AI kono product_id/variant_id bhol thik bhabe dileo server-side resolve
     * kore real product/variant e convert kora hoy (SKU ke product_id bole dileo).
     *
     * @param  array{items: array<int, array<string, mixed>>}  $data
     */
    public function applySelection(Conversation $conversation, array $data): void
    {
        $items = $conversation->selected_products ?? [];

        foreach ($data['items'] as $incoming) {
            $resolved = $this->resolveItem($incoming);

            if (! $resolved) {
                Log::warning('ChatSelectionService: could not resolve selection item', [
                    'item' => $incoming,
                ]);

                continue;
            }

            // Composite key: same product + same variant merge hobe; alada variant
            // thakle alada item thakbe (quantity barano hobe na).
            $key = (string) $resolved['product_id'];
            if ($resolved['variant_id']) {
                $key .= ':v'.$resolved['variant_id'];
            } else {
                $key .= ':a'.md5(json_encode($resolved['variant_attributes'] ?? []));
            }

            $found = false;
            foreach ($items as &$existing) {
                $existingKey = (string) ($existing['product_id'] ?? '');
                if ($existing['variant_id']) {
                    $existingKey .= ':v'.$existing['variant_id'];
                } else {
                    $existingKey .= ':a'.md5(json_encode($existing['variant_attributes'] ?? []));
                }

                if ($existingKey === $key) {
                    $existing['quantity'] = (int) ($existing['quantity'] ?? 1) + (int) $resolved['quantity'];
                    $existing['variant_attributes'] = $resolved['variant_attributes'];
                    $found = true;
                    break;
                }
            }
            unset($existing);

            if (! $found) {
                $items[] = $resolved;
            }
        }

        $conversation->update(['selected_products' => $items]);

        Log::info('ChatSelectionService: selection saved', [
            'conversation_id' => $conversation->id,
            'item_count' => count($items),
        ]);
    }

    /**
     * AI r pathano item theke real product/variant resolve koro.
     *
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>|null
     */
    private function resolveItem(array $incoming): ?array
    {
        $productId = $incoming['product_id'] ?? null;
        $name = (string) ($incoming['name'] ?? '');
        $variantId = $incoming['variant_id'] ?? null;
        $variantAttributes = $incoming['variant_attributes'] ?? [];
        $quantity = max(1, (int) ($incoming['quantity'] ?? 1));

        $product = null;
        if ($productId) {
            $product = Product::find($productId);
        }
        // AI kono product ke SKU hisebe product_id e dile
        if (! $product && $productId) {
            $product = Product::where('sku', (string) $productId)->first();
        }
        if (! $product && $name) {
            $product = Product::where('name', $name)->first();
        }
        if (! $product) {
            return null;
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant && (int) $variant->product_id !== (int) $product->id) {
                $variant = null;
            }
        }

        // variant_id na dile attributes theke variant match koro
        if (! $variant && ! empty($variantAttributes) && is_array($variantAttributes)) {
            $variant = $product->variants->first(fn ($v) => $this->attributesMatch($v->attributes ?? [], $variantAttributes));
            if ($variant) {
                $variantId = $variant->id;
                $variantAttributes = $variant->attributes ?? $variantAttributes;
            }
        }

        return [
            'product_id' => (int) $product->id,
            'variant_id' => $variant ? (int) $variant->id : null,
            'name' => $name !== '' ? $name : $product->name,
            'unit_price' => $incoming['unit_price'] ?? ($variant->price ?? $product->discount_price ?? $product->base_price),
            'variant_attributes' => is_array($variantAttributes) ? $variantAttributes : [],
            'quantity' => $quantity,
        ];
    }

    /**
     * AI r dewa variant attributes gulo DB variant er attributes er sathe match kore kina.
     * Key er variation ignore kora hoy (e.g. "Color" vs "Color 1") — value diyei match.
     *
     * @param  array<string, mixed>  $dbAttrs
     * @param  array<string, mixed>  $given
     */
    private function attributesMatch(array $dbAttrs, array $given): bool
    {
        if (empty($given)) {
            return false;
        }

        $dbValues = array_map(fn ($v) => mb_strtolower((string) $v), array_values($dbAttrs));

        foreach ($given as $value) {
            if (! in_array(mb_strtolower((string) $value), $dbValues, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Customer er bachai kora sob product/variant list koro.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItems(Conversation $conversation): array
    {
        return $conversation->selected_products ?? [];
    }

    /**
     * Conversation er selection muche dao (e.g. order confirm er por).
     */
    public function clearItems(Conversation $conversation): void
    {
        $conversation->update(['selected_products' => []]);
    }

    /**
     * AI er jonno current selection er context string — priti AI call e inject hobe
     * jate AI jane customer kon kon product/variant kon quantity te nite chay.
     */
    public function buildContextString(Conversation $conversation): ?string
    {
        $items = $this->getItems($conversation);

        if (empty($items)) {
            return null;
        }

        $context = "=== কাস্টমারের বর্তমানে বাছাইকৃত প্রোডাক্ট (কনফার্মড কার্ট) ===\n";

        foreach ($items as $i => $item) {
            $line = '• '.($item['name'] ?? 'প্রোডাক্ট');
            $attrs = collect($item['variant_attributes'] ?? [])
                ->map(fn ($v, $k) => "{$k}: {$v}")
                ->implode(', ');
            if ($attrs) {
                $line .= " ({$attrs})";
            }
            if (! empty($item['unit_price'])) {
                $line .= ' — ৳'.number_format((float) $item['unit_price'], 2);
            }
            $line .= ' — পরিমাণ: '.(int) ($item['quantity'] ?? 1).'টি';
            $context .= $line."\n";
        }

        $context .= "\nকাস্টমার এই আইটেমগুলো নিতে চায়। নতুন প্রোডাক্ট বাছাইয়ের সময় ইতিমধ্যে বাছাই করা আইটেমগুলো আবার জিজ্ঞাসা করবে না — শুধু যে আইটেমগুলো এখনো বাছাই হয়নি সেগুলোর জন্য বিকল্প (color/size ইত্যাদি) জিজ্ঞাসা করো। অর্ডার দিতে চাইলে বাছাইকৃত সব আইটেম ORDER_DATA JSON এ অন্তর্ভুক্ত করো।";

        return $context;
    }
}
