<?php

namespace Database\Seeders;

use App\Models\PaymentPlan;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Models\ProductMessage;
use App\Models\QuoteProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LoadProductsFromLegacyDatabase extends Seeder
{
    /**
     * Run the database seeds.
     * QuoteLegacySeeder must have run first so QuoteProfiles exist (speech-1, speech-2, speech-3).
     */
    public function run(): void
    {
        $templates = [1, 2, 3];
        foreach ($templates as $message) {
            ProductMessage::create(['message' => 'demo message']);
        }

        $profilesBySpeech = QuoteProfile::query()
            ->whereIn('slug', ['speech-1', 'speech-2', 'speech-3'])
            ->get()
            ->keyBy(fn ($p) => (int) str_replace('speech-', '', $p->slug));

        $items = DB::select('select * from items');

        foreach ($items as $item) {
            $material = ProductMaterial::where('name', 'like', $item->material)->first();
            if (is_null($material)) {
                $material = ProductMaterial::create(['name' => $item->material]);
            }

            $aux = $item->precio_compuesto
                ? explode(',', $item->precio_compuesto)
                : [12, 1, $item->precio12, null];

            $plan = PaymentPlan::firstOrCreate([
                'payments' => Arr::get($aux, 0, 12),
                'down_payments' => Arr::get($aux, 1, 1),
                'amount' => Arr::get($aux, 2, $item->precio12),
                'down_amount' => Arr::get($aux, 3),
            ]);

            $speech = (int) ($item->speech ?? 1);
            $quoteProfileId = $profilesBySpeech->get($speech)?->id;

            Product::create([
                'name' => $item->name,
                'price' => $item->value,
                'material_id' => $material->id,
                'included' => $item->incluye,
                'message_id' => $item->speech,
                'trips_required' => $item->viajes_instalacion,
                'payment_plan_id' => $plan->id,
                'quote_profile_id' => $quoteProfileId,
            ]);
        }
    }
}
