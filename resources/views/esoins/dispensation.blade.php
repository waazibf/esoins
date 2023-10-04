@extends('layouts.template')
@section('page_title', 'ECOM | All livre')
@section('dispensation', 'active')
@section('content')
<div class="mb-3 mt-1">
    <h4 style="padding: 0.4rem 0 0.4rem 1rem; background-color: #004ebc; color: white !important; font-size: 0.8rem;">LISTE DES ORDONNANCES</h4>
</div>
  <div id="categories" data-list='{"valueNames":["id","livre_name","sublivre_count","product_count","last_active","slug"],"page":10,"pagination":true}'>
    <div class="row align-items-center justify-content-between g-3 mb-4">
      <div class="col col-auto">
        <div class="search-box">
          <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
            <input class="form-control form-control-sm search-input search" type="search" placeholder="Rechercher" aria-label="Search" />
            <span class="fas fa-search search-box-icon"></span>

          </form>
        </div>
      </div>
      <div class="col-auto">
        <div class="d-flex align-items-center">
          <a href="{{ route('esoins.ordonnance') }}" class="btn btn-outline-primary btn-sm" style="font-weight: 600;"><span class="fas fa-plus me-2"></span>Saisie ordonnance</a>
          <button class="btn btn-outline-secondary ms-2" type="button" data-bs-toggle="modal" data-bs-target="#addOrdonnance"><span class="fas fa-plus me-2"></span> Saisie ordonnance</button>
        </div>
      </div>
    </div>
    <div class="mx-n4 mx-lg-n6 px-4 px-lg-6 mb-9 bg-white border-y border-300 mt-2 position-relative top-1">
      <div class="table-responsive scrollbar ms-n1 ps-1">
        <table class="table table-sm mb-0 p-1 text-700" style="font-size: .72rem;">
          <thead>
            <tr>
              <th class="white-space-nowrap fs--1 align-middle ps-0">
                <div class="form-check mb-0 fs-0">
                  <input class="form-check-input" id="checkbox-bulk-categories-select" type="checkbox" data-bulk-select='{"body":"categories-table-body"}' />
                </div>
              </th>
              <th class="sort align-middle" scope="col" data-sort="id" style="min-width:10px; color: #004ebc;">ID</th>
              <th class="sort align-middle" scope="col" data-sort="livre_name" style="color: #004ebc;">NUMÉRO</th>
              <th class="sort align-middle" scope="col" data-sort="livre_name" style="color: #004ebc;">PRODUITS</th>
              <th class="sort align-middle" scope="col" data-sort="livre_name" style="color: #004ebc;">QUANTITÉ</th>
              <th class="sort align-middle" scope="col" data-sort="livre_name" style="color: #004ebc;">MONTANT</th>
              <th class="sort align-middle" scope="col" data-sort="last_active" style="color: #004ebc;">DATE DISPENS.</th>
            </tr>
          </thead>
          <tbody class="list" id="categories-table-body">
            <?php $i = 1; ?>
            @foreach($nordonnances as $nordonnance)
            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
              <td class="fs--1 align-middle ps-0 py-3">
                <div class="form-check mb-0 fs-0">
                    <input class="form-check-input" type="checkbox" name="livre[]" id="{{ $i }}" data-bulk-select-row='{}' />
                </div>
              </td>
              <td class="id align-middle white-space-nowrap fw-bold">{{ $i }}</td>
              <td class="livre_name align-middle white-space-nowrap text-td"><a class="fw-bold" href="{{ route('esoins.fiche', $nordonnance->ordonnance_id) }}">{{ $nordonnance->ordonnance_id }}</a></td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $nordonnance->name }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $nordonnance->quantity_product }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ intval($nordonnance->quantity_product )* intval($nordonnance->drd_price) }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $nordonnance->date_dispensation }}</td>
            </tr>
            <?php $i++; ?>
            @endforeach
            <?php $k = $i; ?>
            @foreach($ordonnances as $ordonnance)
            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
              <td class="fs--1 align-middle ps-0 py-3">
                <div class="form-check mb-0 fs-0">
                    <input class="form-check-input" type="checkbox" name="livre[]" id="{{ $k }}" data-bulk-select-row='{}' />
                </div>
              </td>
              <td class="id align-middle white-space-nowrap fw-bold">{{ $k }}</td>
              <td class="livre_name align-middle white-space-nowrap text-td"><a class="fw-bold" href="{{ route('esoins.fiche', $ordonnance->ordonnance_id) }}">{{ $ordonnance->ordonnance_id }}</a></td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $ordonnance->name }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $ordonnance->quantity_product }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ intval($ordonnance->quantity_product) * intval($ordonnance->prix_drd) }}</td>
              <td class="livre_name align-middle white-space-nowrap fw-bold text-td">{{ $ordonnance->date_dispensation }}</td>
            </tr>
            <?php $k++; ?>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="row align-items-center justify-content-between py-2 pe-0 fs--1">
        <div class="col-auto d-flex">
          <p class="mb-0 d-none d-sm-block me-3 fw-semi-bold text-900" data-list-info="data-list-info"></p><a class="fw-semi-bold" href="#!" data-list-view="*">Tous les livres<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a><a class="fw-semi-bold d-none" href="#!" data-list-view="less">View Less<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
        <div class="col-auto d-flex">
          <button class="page-link" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
          <ul class="mb-0 pagination"></ul>
          <button class="page-link pe-0" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addOrdonnance" tabindex="-1" aria-labelledby="addOrdonnanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addOrdonnanceModalLabel">NOUVELLE DISEPENSATION DE L'ORDONNACE</h5>
          <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times fs--1"></span></button>
        </div>
        <div class="modal-body">
          <form action="">
            <input type="hidden" id="code_product">
            <input type="hidden" id="quantity_product">
            <input type="hidden" id="index_product">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label" for="validationCustom01">Numéro d'ordonnance</label>
                    <table style="width: 100%;">
                        <tr>
                            <td><input class="form-control" id="ordonnance_id" type="text" placeholder="00000001" /></td>
                            <td><span class="btn btn-primary" id="check-odonnance"><span class="fas fa-search"></span></span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="validationCustom01">Date dispensation</label>
                    <input class="form-control datetimepicker" id="consultation_date_product" name="consultation_date" type="text" placeholder="dd/mm/yyyy" data-options='{"disableMobile":true,"dateFormat":"d/m/Y"}' value="{{ date('d/m/Y') }}" />
                </div>
            </div>
            <div id="products">

            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="button" id="add-dispensation">Enregistrer</button>
          <button class="btn btn-outline-primary" type="button" data-bs-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
@endsection
