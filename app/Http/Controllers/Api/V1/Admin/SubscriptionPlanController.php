<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return SubscriptionPlanResource::collection(
            SubscriptionPlan::orderBy('price')->get()
        );
    }

    public function store(StoreSubscriptionPlanRequest $request)
    {
        $plan = SubscriptionPlan::create($request->validated());

        return (new SubscriptionPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update($request->validated());

        return new SubscriptionPlanResource($subscriptionPlan);
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Soft-disable rather than hard delete — existing subscriptions reference it
        $subscriptionPlan->update(['is_active' => false]);

        return response()->json(['message' => 'Plan deactivated.']);
    }
}