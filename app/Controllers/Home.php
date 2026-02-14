<?php

namespace App\Controllers;

class Home extends BaseController
{
  public function index(): string
  {
    return view('welcome_message');
  }

  public function dashboard()
  {
    return view('dashboard');
  }

  public function switchCompany($companyId)
  {
    $companyModel = new \App\Models\CompanyModel();
    $company = $companyModel->find($companyId);

    if ($company) {
      session()->set('company_id', $companyId);
      // Optionally update tax rate or other company specific session data
      session()->set('company_default_tax_rate', $company['tax_rate'] ?? 3.00);
      session()->set('company_name', $company['company_name']);
    }

    return redirect()->back();
  }
}
