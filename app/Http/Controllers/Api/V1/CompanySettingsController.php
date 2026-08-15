<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanySettingsRequest;
use App\Http\Resources\CompanySettingsResource;

class CompanySettingsController extends Controller
{
    public function show()
    {
        $company = request()->user()->company()->with('activeSubscription.plan')->first();

        return new CompanySettingsResource($company);
    }

    public function update(UpdateCompanySettingsRequest $request)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $settings = $company->settings ?? [];
        if (array_key_exists('gps_ping_interval_seconds', $data)) {
            $settings['gps_ping_interval_seconds'] = $data['gps_ping_interval_seconds'];
        }
        if (array_key_exists('distance_unit', $data)) {
            $settings['distance_unit'] = $data['distance_unit'];
        }

        $company->update([
            'name' => $data['name'] ?? $company->name,
            'legal_name' => $data['legal_name'] ?? $company->legal_name,
            'timezone' => $data['timezone'] ?? $company->timezone,
            'settings' => $settings,
        ]);

        return new CompanySettingsResource($company->fresh(['activeSubscription.plan']));
    }
}