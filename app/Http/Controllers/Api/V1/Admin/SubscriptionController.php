<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Company;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions)
    {
    }

    public function index(Company $company)
    {
        return SubscriptionResource::collection(
            $company->subscriptions()->with('plan')->latest()->get()
        );
    }

    public function store(StoreSubscriptionRequest $request, Company $company)
    {
        $subscription = $this->subscriptions->assign($company, $request->validated());

        return (new SubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }
}