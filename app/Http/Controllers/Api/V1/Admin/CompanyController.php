<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(protected CompanyService $companies)
    {
    }

    public function index(Request $request)
    {
        $companies = $this->companies->paginate(
            $request->only(['status', 'search']),
            (int) $request->input('per_page', 20)
        );

        return CompanyResource::collection($companies);
    }

    public function store(StoreCompanyRequest $request)
    {
        $company = $this->companies->create($request->validated());

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Company $company)
    {
        return new CompanyResource(
            $company->loadCount(['vehicles', 'users'])->load('activeSubscription.plan')
        );
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company = $this->companies->update($company, $request->validated());

        return new CompanyResource($company);
    }

    public function suspend(Company $company)
    {
        return new CompanyResource($this->companies->suspend($company));
    }

    public function activate(Company $company)
    {
        return new CompanyResource($this->companies->activate($company));
    }

    public function stats()
    {
        return response()->json($this->companies->platformStats());
    }
}