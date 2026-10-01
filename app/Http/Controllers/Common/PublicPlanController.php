<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ManagementPackage;
use App\Models\ManagementPricing;
use Illuminate\Http\Request;

/**
 * Plans for the public Pricing page: what each plan includes and, for the chosen
 * country, its price per member per day in that country's currency.
 * Read-only and holds no personal data, so it needs no login.
 */
class PublicPlanController extends Controller
{
    private const FEATURES = [
        'max_member', 'storage_limit', 'meeting_limit', 'event_limit', 'project_limit', 'asset_limit', 'document_limit',
        'advanced_report', 'custom_report', 'premium_support', 'priority_support', 'custom_email_template', 'custom_username',
        'custom_branding', 'custom_domain', 'web_profile', 'api_access', 'dedicated_account_manager',
    ];

    public function index(Request $request)
    {
        $request->validate(['country_id' => 'nullable|integer']);

        // With no country chosen, show one that has prices
        $countryId = $request->integer('country_id') ?: null;
        $pricedCountryIds = Country::whereHas('countryRegion', fn ($q) => $q->where('is_active', true))->pluck('id');
        if (!$countryId) {
            $countryId = $pricedCountryIds->first();
        }

        $country = $countryId ? Country::with('countryRegion.region.regionCurrency.currency')->find($countryId) : null;
        $region = $country?->countryRegion?->region;
        $currency = $region?->regionCurrency?->currency;
        $prices = $region
            ? ManagementPricing::where('region_id', $region->id)->where('is_active', true)->pluck('price_rate', 'management_package_id')
            : collect();

        $plans = ManagementPackage::where('is_active', 1)->orderBy('id')->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price_rate' => $prices->has($p->id) ? (float) $prices[$p->id] : null,
                'features' => collect(self::FEATURES)->mapWithKeys(fn ($f) => [$f => $p->{$f}]),
            ]);

        return response()->json([
            'status' => true,
            'data' => [
                'country_id' => $country?->id,
                'priced_country_ids' => $pricedCountryIds,
                'currency' => $currency ? ['code' => $currency->currency_code, 'symbol' => trim((string) $currency->currency_symbol)] : null,
                'plans' => $plans,
            ],
        ]);
    }
}
