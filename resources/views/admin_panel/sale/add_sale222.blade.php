@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="{{ isset($sale) ? '' : 'd-none' }} position-fixed top-0 start-0 w-100 h-100 d-flex flex-column gap-3 justify-content-center align-items-center"
        style="background: rgba(255,255,255,0.9); z-index: 1055;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Loading Sale Data...</div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ================= RESPONSIVE SALES UI ================= */

        /* allow smooth horizontal scroll on small devices */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* base table width */
        .sales-table {
            min-width: 700px;
        }

        /* 🔹 DISCOUNT COLUMN – THORI SI BARI */
        .sales-table td.large-col {
            min-width: 95px;
            width: 95px;
            padding: 4px;
        }

        /* 🔹 DISCOUNT LAYOUT */
        .discount-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: nowrap;
        }

        /* 🔹 INPUT – NOT TOO SMALL */
        .discount-wrapper .discount-value {
            width: 60px;
            min-width: 60px;
            font-size: 0.8rem;
            padding: 4px 6px;
        }

        /* 🔹 PLUS ICON – NEAT & SMALL */
        .discount-wrapper .discount-plus {
            width: 22px;
            height: 22px;
            padding: 0;
            font-size: 13px;
            line-height: 1;
        }

        /* 🔹 DROPDOWN */
        .discount-wrapper .discount-type {
            position: absolute;
            right: 0;
            top: 115%;
            width: 65px;
            font-size: 0.75rem;
            z-index: 30;
        }



        /* ---------- TABLET (<= 992px) ---------- */
        @media (max-width: 992px) {

            .main-container {
                max-width: 100%;
            }

            .sales-table {
                min-width: 700px;
            }

            .minw-350 {
                min-width: 100%;
            }

        }

        /* ---------- MOBILE (<= 768px) ---------- */
        @media (max-width: 768px) {

            .header-text {
                font-size: 1rem;
            }

            .btn {
                padding: .35rem .5rem;
            }

            /* stack header buttons */
            .d-flex.justify-content-between.align-items-center {
                flex-wrap: wrap;
                gap: 8px;
            }

            /* customer + invoice panel full width */
            .minw-350 {
                width: 100%;
            }

            /* reduce input font */
            .form-control,
            .form-select {
                font-size: .8rem;
            }

        }

        /* ---------- VERY SMALL DEVICES ---------- */
        @media (max-width: 576px) {

            .sales-table {
                min-width: 650px;
            }

            .discount-wrapper .discount-value {
                min-width: 90px;
            }

        }
    </style>
    <style>
        /* Premium Customer Card CSS - Refactored 2-row layout */
        .customer-card-premium {
            background-color: #111827 !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            color: #f3f4f6 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        .customer-card-premium .col-title {
            font-size: 0.65rem !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            color: #9ca3af !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
        }

        .customer-card-premium .col-title i {
            margin-right: 2px !important;
        }

        .customer-card-premium .col-value {
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }

        .customer-card-premium .col-value-sub {
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            margin-top: -2px !important;
        }

        /* Sleek Segmented Control for Radio Buttons */
        .toggle-button-group {
            background-color: #f1f5f9;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            padding: 2px;
            display: flex;
            height: 38px;
            align-items: center;
        }
        .toggle-button-group .btn-check {
            display: none;
        }
        .toggle-button-group .toggle-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            height: 100%;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0 !important;
            padding: 0 !important;
        }
        .toggle-button-group .toggle-btn:hover {
            color: #1e293b;
            background-color: #e2e8f0;
        }
        .toggle-button-group .btn-check:checked + .toggle-btn {
            background-color: #2563eb;
            color: #ffffff !important;
        }
        .toggle-button-group .btn-check:checked + .toggle-btn:hover {
            background-color: #1d4ed8;
            color: #ffffff !important;
        }

        /* Specific styling for the customer selection Select2 to match standard input heights */
        #customerSelect + .select2-container--default .select2-selection--single {
            border: 2px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0 12px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }
        #customerSelect + .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important;
            padding-left: 0 !important;
            font-size: 0.85rem !important;
            color: #1e293b !important;
        }
        #customerSelect + .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important;
            top: 2px !important;
            right: 8px !important;
        }
        #customerSelect + .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
        }

        /* 💎 PREMIUM MODERN ERP THEME FOR TRANSACTION ENTRY 💎 */
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        /* Containers & Cards */
        .main-container {
            border: 2px solid #475569 !important; /* Bold outer border */
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
            background-color: #ffffff !important;
            padding: 24px !important;
            font-size: .85rem;
            max-width: 98%;
        }
        
        .card-panel {
            background-color: #f8fafc !important;
            border: 2px solid #cbd5e1 !important; /* Bold panel borders */
            border-radius: 10px !important;
            padding: 20px !important;
            height: 100%;
            transition: all 0.2s;
        }
        
        .card-panel:hover {
            border-color: #94a3b8 !important;
        }
        
        .totals-card {
            background-color: #f1f5f9 !important;
            border: 2px solid #cbd5e1 !important; /* Bold summary borders */
            border-radius: 10px !important;
            padding: 20px !important;
        }
        
        /* Bold Section Titles */
        .section-title {
            font-weight: 800 !important;
            text-transform: uppercase;
            font-size: 0.8rem !important;
            letter-spacing: 1px !important;
            color: #1e293b !important;
            margin-bottom: 16px !important;
            border-left: 4px solid #2563eb !important;
            padding-left: 10px !important;
        }
        
        /* Clean inputs with bold borders */
        .form-control,
        .form-select,
        .select2-container--default .select2-selection--single {
            border: 2px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease-in-out !important;
            height: auto !important;
            font-size: 0.85rem !important;
        }
        
        .form-control:focus,
        .form-select:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }
        
        /* Read-only fields */
        .input-readonly {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            cursor: not-allowed !important;
        }
        
        /* Elegant & Bold Buttons */
        .btn-action-primary {
            background-color: #2563eb !important;
            border: 2px solid #1d4ed8 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            padding: 8px 20px !important;
            transition: all 0.2s;
            font-size: 0.85rem !important;
        }
        .btn-action-primary:hover {
            background-color: #1d4ed8 !important;
            transform: translateY(-1px);
            color: #ffffff !important;
        }
        
        .btn-action-secondary {
            background-color: #ffffff !important;
            border: 2px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            padding: 8px 20px !important;
            transition: all 0.2s;
            font-size: 0.85rem !important;
        }
        .btn-action-secondary:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        
        /* Transaction Grid / Table */
        .table-responsive {
            border: 1px solid #cbd5e1 !important; /* Elegant outer border */
            border-radius: 8px !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
            min-height: 200px;
            background-color: #ffffff;
        }
        
        .minw-350 {
            min-width: 320px;
            width: 320px;
            flex-shrink: 0;
        }

        .sales-table {
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            min-width: 935px;
        }
        
        .sales-table thead th {
            background-color: #f8fafc !important; /* Light clean header */
            color: #0f172a !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 11px !important;
            letter-spacing: 0.5px;
            padding: 10px 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-bottom: 2px solid #94a3b8 !important; /* Thick header separator border */
            vertical-align: middle !important;
            text-align: center;
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 12px !important;
        }
        
        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important; /* Flat interior cell borders */
            padding: 0 !important; /* Zero padding to let input fill cell completely */
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        /* ⚡ FLAT BORDERLESS GRID INPUTS ⚡ */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 38px !important; /* Uniform height */
            margin: 0 !important;
            padding: 6px 8px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center; /* Center-align text in grid inputs */
            color: #1e293b !important;
            font-weight: 500 !important;
            font-size: 0.82rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 12px !important;
        }

        /* Calculations and Read-Only cells get a neat slate tone background */
        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly],
        .sales-table tbody select[disabled] {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        /* Subtle focus highlight inside cell */
        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        /* Select2 Specific flat borderless styling */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            font-size: 0.82rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        /* Select2 Focus state */
        .sales-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        /* Elegant flat block layout for discount input + toggle */
        .sales-table tbody .discount-wrapper {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            height: 38px !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sales-table tbody .discount-wrapper .discount-value {
            flex-grow: 1 !important;
            border: none !important;
            border-radius: 0 !important;
            height: 100% !important;
            text-align: center;
            background-color: transparent !important;
            padding: 6px 8px !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle {
            border: none !important;
            border-radius: 0 !important;
            background-color: #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            width: 32px !important;
            min-width: 32px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            cursor: pointer !important;
            transition: background-color 0.2s !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle:hover {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        
        .sales-table tfoot td {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-top: 2px solid #94a3b8 !important; /* Thick tfoot separator */
            padding: 8px 10px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        
        /* Row hover */
        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }
        
        /* Column Widths */
        .col-product { width: 180px; min-width: 180px; }
        .col-warehouse { min-width: 130px; }
        .col-stock { width: 70px; }
        .col-qty { width: 70px; }
        .col-price { width: 90px; }
        .col-disc { width: 90px; }
        .col-disc-amt { width: 80px; }
        .col-pieces { width: 80px; }
        .col-price-p { width: 90px; }
        .col-price-m2 { width: 90px; }
        .col-amount { width: 100px; }
        .col-action { width: 40px; text-align: center; }

        /* Invalid cells & inputs */
        .invalid-cell {
            background-color: #fff5f5 !important;
            border: 2px solid #ef4444 !important;
        }
        .invalid-select,
        .invalid-input {
            border-color: #ef4444 !important;
            box-shadow: none !important;
        }
        
        .badge-soft {
            background: #eef2ff;
            color: #3730a3;
            font-weight: 700;
        }
    </style>



    <div class="container-fluid py-2">
        <div class="main-container bg-white border shadow-sm mx-auto p-2 rounded-3">

            <div id="alertBox" class="alert d-none mb-3" role="alert"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="sale">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <small class="text-secondary" id="entryDateTime">Entry Date_Time: --</small> <br>
                        <a href="{{ route('sale.index') }}" target="_blank" rel="noopener"
                            class="btn btn-sm btn-outline-secondary" title="Sales List (opens new tab)">
                            Sales List
                        </a>
                    </div>


                    <h2 class="header-text text-secondary fw-bold mb-0">Sales</h2>


                    <div class="d-flex align-items-center gap-2">
                        <small class="text-secondary me-2" id="entryDate">Date: --</small>
                        <button type="button" class="btn btn-sm btn-success" id="btnHeaderPosted"
                            disabled>Sale</button>
                    </div>
                </div>

                <!-- HORIZONTAL TOP PANEL -->
                <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                    <div class="row align-items-stretch g-3">
                        
                        <!-- LEFT SECTION: Inputs (col-lg-7) -->
                        <div class="col-lg-7 d-flex flex-column justify-content-between">
                            <!-- Row 1: Invoice metadata -->
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-sm-3">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.82rem;">Invoice No.</label>
                                    <input type="text" class="form-control input-readonly" name="Invoice_no"
                                        value="{{ $nextInvoiceNumber }}" readonly style="height: 38px !important;">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.82rem;">Credit Days</label>
                                    <input type="number" class="form-control" name="credit_days" placeholder="Days"
                                        min="0" value="{{ $sale->credit_days ?? '' }}" style="height: 38px !important;">
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.82rem;">Type</label>
                                        <button type="button" class="btn btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal" title="Add New Customer" style="font-size: 0.75rem; height: 22px; line-height: 20px;">
                                            <i class="fas fa-plus"></i> Add Customer
                                        </button>
                                    </div>
                                    <div class="toggle-button-group w-100" id="partyTypeGroup">
                                        <input type="radio" class="btn-check" name="partyType" id="typeCustomers" value="Main Customer" checked>
                                        <label class="toggle-btn" for="typeCustomers">Customers</label>
                                        
                                        <input type="radio" class="btn-check" name="partyType" id="typeWalkin" value="Walking Customer">
                                        <label class="toggle-btn" for="typeWalkin">Walk-in</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 2: Customer selection, Remarks, Date -->
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.82rem;">M.Bill (Optional):</label>
                                    <input type="text" class="form-control" name="reference" id="remarks" placeholder="Enter remarks..." style="height: 38px !important;">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.82rem;">Date:</label>
                                    <input type="text" class="form-control" id="displayDateInput" value="{{ date('d/m/Y') }}" readonly style="background-color: #ffffff; cursor: default; height: 38px !important;">
                                </div>
                                <div class="col-sm-5">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.82rem;">Customer:</label>
                                    <select class="form-select" id="customerSelect" name="customer" style="width:100%">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SECTION: Premium Horizontal Dark Customer Card Column (col-lg-5) -->
                        <div class="col-lg-5 d-flex flex-column justify-content-center">
                            <div class="customer-card-premium p-3" style="min-height: 106px;">
                                <!-- Top Row: Customer Name and Clear Link -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px solid #374151;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-primary me-2" style="font-size: 1.1rem; color: #a78bfa !important;"></i>
                                        <span class="fw-bold text-white text-truncate" id="cc_customer_name" style="max-width: 260px; font-size: 0.95rem;">Select Customer</span>
                                    </div>
                                    <button id="clearCustomerData" type="button" class="btn btn-link text-secondary p-0 text-decoration-none" style="font-size: 0.75rem; color: #9ca3af !important; transition: color 0.2s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#9ca3af'">
                                        <i class="fas fa-times-circle me-1"></i>Clear
                                    </button>
                                </div>
                                
                                <!-- Bottom Row: Financial metrics -->
                                <div class="d-flex align-items-center justify-content-between text-center w-100">
                                    <!-- Column 1: PREVIOUS BALANCE -->
                                    <div style="flex: 1; border-right: 1px solid #374151; min-width: 0; padding: 0 4px;">
                                        <div class="col-title text-center text-truncate">
                                            <i class="fas fa-history"></i> Prev Bal
                                        </div>
                                        <div class="text-center text-truncate">
                                            <span class="col-value text-danger" id="cc_prev_bal_val">Rs 0</span>
                                            <span class="col-value-sub text-danger" id="cc_prev_bal_suffix">Dr</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Column 2: CURRENT BILL -->
                                    <div style="flex: 1; border-right: 1px solid #374151; min-width: 0; padding: 0 4px;">
                                        <div class="col-title text-center text-truncate">
                                            <i class="far fa-file-alt"></i> Current
                                        </div>
                                        <div class="col-value text-white text-center text-truncate" id="cc_current_bill">
                                            Rs 0
                                        </div>
                                    </div>
                                    
                                    <!-- Column 3: PAID NOW -->
                                    <div style="flex: 1; border-right: 1px solid #374151; min-width: 0; padding: 0 4px;">
                                        <div class="col-title text-center text-truncate" style="color: #10B981 !important;">
                                            <i class="fas fa-check-square"></i> Paid
                                        </div>
                                        <div class="col-value text-success text-center text-truncate" id="cc_paid_now">
                                            Rs 0
                                        </div>
                                    </div>
                                    
                                    <!-- Column 4: CLOSING BALANCE -->
                                    <div style="flex: 1; min-width: 0; padding: 0 4px;">
                                        <div class="col-title text-center text-truncate" style="color: #f87171 !important;">
                                            <i class="fas fa-dot-circle"></i> Closing
                                        </div>
                                        <div class="text-center text-truncate">
                                            <span class="col-value text-danger" id="cc_closing_bal_val">Rs 0</span>
                                            <span class="col-value-sub text-danger" id="cc_closing_bal_suffix">Dr</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                    </div>
                </div>

                {{-- Hidden fields for backend --}}
                <input type="hidden" id="address" name="address">
                <input type="hidden" id="tel" name="tel">
                <input type="hidden" id="previousBalance" value="0">
                <input type="hidden" id="rangeBalance" value="0">

                <!-- Items Section full width -->
                <div class="p-3 border rounded-3 bg-white shadow-sm mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0">Items</div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#quickAddProductModal">
                                <i class="fas fa-plus me-1"></i>Quick Add Product
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAdd">Add Row</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered sales-table mb-0">
                            <thead>
                                <tr>
                                    <th class="col-product">Product</th>
                                    <th class="col-stock">Stock</th>
                                    <th style="width:65px;min-width:65px;">Carton</th>
                                    <th style="width:70px;min-width:70px;">Loose Pcs</th>
                                    <th class="col-qty pack-size-col" title="Pieces per Carton">Pcs/Ctn</th>
                                    <th class="col-pieces boxes-col">Total Pcs</th>
                                    <th class="col-price-p price-pc-header">Retail Price</th>
                                    <th class="col-disc">Disc %</th>
                                    <th class="col-disc-amt">Disc Amt</th>
                                    <th class="col-amount">Amount</th>
                                    <th class="col-action">—</th>
                                </tr>
                            </thead>
                            <tbody id="salesTableBody">

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="9" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold"><span id="totalAmount">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Totals + Receipts --}}
                <div class="row g-3 mt-3">
                    <div class="col-lg-7">
                        <div class="section-title mb-2">Receipt Vouchers</div>
                        <div id="rvWrapper" class="border rounded-3 p-2">
                            <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                <select class="form-select rv-account" name="receipt_account_id[]"
                                    style="max-width: 320px">
                                    <option value="" selected disabled>Select account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control text-end rv-amount" name="receipt_amount[]"
                                    placeholder="0.00" style="max-width:160px">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddRV">Add
                                    more</button>
                            </div>
                            <div class="text-end">
                                <span class="me-2">Receipts Total:</span>
                                <span class="fw-bold" id="receiptsTotal">0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="section-title mb-2">Totals</div>
                        <div class="totals-card p-3">
                            <div class="row py-1">
                                <div class="col-7 text-muted">Total Qty</div>
                                <div class="col-5 text-end"><span id="tQty">0</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Invoice Gross (Σ Sales Price × Qty)</div>
                                <div class="col-5 text-end"><span id="tGross">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Line Discount (on Retail)</div>
                                <div class="col-5 text-end"><span id="tLineDisc">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 fw-semibold">Sub-Total</div>
                                <div class="col-5 text-end fw-semibold"><span id="tSub">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7">Aditional Discount %</div>
                                <div class="col-5 text-end">
                                    <input type="text" class="form-control text-end" name="discountPercent"
                                        id="discountPercent" value="0" style="max-width:120px; margin-left:auto">
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Aditional Discount Rs</div>
                                <div class="col-5 text-end"><span id="tOrderDisc">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 fw-bold">Current Bill Total</div>
                                <div class="col-5 text-end fw-bold"><span id="tCurrentBill">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-danger">Previous Balance</div>
                                <div class="col-5 text-end text-danger"><span id="tPrev">0.00</span></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-7 fw-bold text-primary">Payable / Total Balance</div>
                                <div class="col-5 text-end fw-bold text-primary"><span id="tPayable">0.00</span></div>
                            </div>

                            {{-- hidden mirrors for backend --}}
                            {{-- Maps to 'total_bill_amount' in DB (SubTotal AFTER line discounts) --}}
                            <input type="hidden" name="subTotal1" id="subTotal1" value="0">
                            <input type="hidden" name="total_subtotal" id="subTotal2" value="0">

                            {{-- Maps to 'total_extradiscount' in DB --}}
                            <input type="hidden" name="total_extra_cost" id="discountAmount" value="0">

                            {{-- Maps to 'total_net' in DB --}}
                            <input type="hidden" name="total_net" id="totalBalance" value="0">

                            {{-- Default values for nullable fields to satisfy controller --}}
                            <input type="hidden" name="cash" value="0">
                            <input type="hidden" name="card" value="0">
                            <input type="hidden" name="change" value="0">
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex flex-wrap gap-3 justify-content-center p-3 mt-3 border-top">
                    <button type="button" class="btn btn-action-primary bg-primary border-primary" id="btnSave"><i class="fas fa-bookmark me-2"></i>Booking</button>
                    <button type="button" class="btn btn-action-primary bg-success border-success" id="btnPosted" disabled><i class="fas fa-check-circle me-2"></i>Sale</button>

                    <button type="button" class="btn btn-action-secondary" id="btnPrint"><i class="fas fa-print me-2"></i>Print A4 Half</button>
                    <button type="button" class="btn btn-action-secondary" id="btnEstimate"><i class="fas fa-file-invoice me-2"></i>Estimate</button>
                    <button type="button" class="btn btn-action-secondary" id="btnPrint2"><i class="fas fa-receipt me-2"></i>Print Thermal</button>
                    <button type="button" class="btn btn-action-secondary" id="btnDcThermal"><i class="fas fa-truck me-2"></i>DC Thermal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus text-primary me-2"></i>New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ajaxAddCustomerForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="customer_type" required>
                                    <option value="Main Customer">Main Customer</option>
                                    <option value="Walking Customer">Walking Customer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Customer Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveAjaxCustomer">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== QUICK ADD PRODUCT MODAL ===== --}}
<!-- <div class="modal fade" id="quickAddProductModal" tabindex="-1" aria-labelledby="quickAddProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="quickAddProductModalLabel">
                    <i class="fa fa-plus-circle text-primary me-2"></i>Quick Add Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" required placeholder="Enter product name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="qap_category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sub Category</label>
                            <select class="form-select" name="sub_category_id" id="qap_subcategory">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Brand <span class="text-danger">*</span></label>
                            <select class="form-select" name="brand_id" id="qap_brand" required>
                                <option value="">Select Brand</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Model / Series</label>
                            <input type="text" class="form-control" name="model" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Size Mode <span class="text-danger">*</span></label>
                            <select class="form-select" name="size_mode" id="qap_size_mode" required>
                                <option value="by_cartons" selected>By Cartons</option>
                                <option value="by_pieces">By Pieces</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="qap_ppb_wrap">
                            <label class="form-label fw-bold small text-muted">Pieces Per Box</label>
                            <input type="number" class="form-control" name="pieces_per_box" id="qap_ppb" value="1" min="1" placeholder="e.g. 12">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Low Stock (Cartons)</label>
                            <input type="number" class="form-control" name="alert_carton_quantity" min="0" placeholder="e.g. 5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Purchase Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="purchase_price_per_piece" value="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sale Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="sale_price_per_box" value="0" placeholder="0.00">
                        </div>
                    </div>
                    {{-- Hidden defaults for validation --}}
                    <input type="hidden" name="boxes_quantity" value="0">
                    <input type="hidden" name="loose_pieces" value="0">
                    <input type="hidden" name="piece_quantity" value="0">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnQuickSaveProduct">
                        <i class="fa fa-save me-1"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle pieces_per_box field based on size mode
    $('#qap_size_mode').on('change', function() {
        if ($(this).val() === 'by_pieces') {
            $('#qap_ppb_wrap').hide();
            $('#qap_ppb').val(1);
        } else {
            $('#qap_ppb_wrap').show();
        }
    });

    // Load categories, brands, and subcategories immediately
    var $catSelect = $('#qap_category');
    var $brandSelect = $('#qap_brand');
    var $subCatSelect = $('#qap_subcategory');

    // Load categories if empty
    if ($catSelect.find('option').length <= 1) {
        $.get("{{ url('/get-categories') }}", function(data) {
            (data || []).forEach(function(cat) {
                $catSelect.append('<option value="'+ cat.id +'">'+ cat.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load categories');
        });
    }

    // Load brands if empty
    if ($brandSelect.find('option').length <= 1) {
        $.get("{{ url('/get-brands') }}", function(data) {
            (data || []).forEach(function(brand) {
                $brandSelect.append('<option value="'+ brand.id +'">'+ brand.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load brands');
        });
    }

    // Load all subcategories initially if empty
    if ($subCatSelect.find('option').length <= 1) {
        $.get("{{ url('/get-all-subcategories') }}", function(data) {
            (data || []).forEach(function(sub) {
                $subCatSelect.append('<option value="'+ sub.id +'">'+ sub.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load subcategories');
        });
    }

    // Load subcategories when category changes
    $('#qap_category').on('change', function() {
        var categoryId = $(this).val();
        var $subCatSelect = $('#qap_subcategory');
        $subCatSelect.html('<option value="">Select Sub Category</option>');
        
        if (categoryId) {
            $.get("{{ url('/get-subcategories') }}/" + categoryId, function(data) {
                (data || []).forEach(function(sub) {
                    $subCatSelect.append('<option value="'+ sub.id +'">'+ sub.name +'</option>');
                });
            });
        }
    });

    // Submit Quick Add Product
    $('#quickAddProductForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnQuickSaveProduct');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: "{{ route('store-product') }}",
            method: "POST",
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);
                $('#quickAddProductForm')[0].reset();

                // Close modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('quickAddProductModal'));
                if (modal) modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Product Added!',
                    text: response.message || 'Product created successfully. You can now search for it.',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                var msg = 'Error adding product.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script> -->

    <!-- {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal') -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal')
    {{-- sjadlfksal --}}

    <script>
        /* ========== DISCOUNT TOGGLE (% ↔ PKR) ========== */

        // $(document).on('click', '.discount-toggle', function () {

        //     const $btn = $(this);
        //     const currentType = $btn.data('type');

        //     if (currentType === 'percent') {
        //         // switch to PKR
        //         $btn.data('type', 'pkr');
        //         $btn.text('PKR');
        //     } else {
        //         // switch to %
        //         $btn.data('type', 'percent');
        //         $btn.text('%');
        //     }

        //     // focus back to input
        //     $btn.closest('.discount-wrapper')
        //         .find('.discount-value')
        //         .focus();
        // });
    </script>














    {{-- hajshdsadsdsksa --}}

    {{-- Shared Logic for Sales (Add/Edit) --}}
@endsection

@section('js')
    @include('admin_panel.sale.scripts.shared_logic')

    <script>
        $(document).ready(function() {
            // --- Initial Setup ---
            if ($('#salesTableBody tr').length === 0) {
                addNewRow();
            }
            updateGrandTotals();
            refreshPostedState();

            // --- Check if URL is for Booking Flow ---
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') === 'booking') {
                $('.header-text').html('<i class="fas fa-bookmark text-primary me-2"></i>Add Booking');
                $('#action').val('booking');
                $('#btnPosted').addClass('d-none');
                $('#btnHeaderPosted').addClass('d-none');
            }

            // ============================================================
            // CUSTOMER SELECT2 AJAX SEARCH (Name or Code)
            // ============================================================
            function getPartyType() {
                return $('input[name="partyType"]:checked').val() || 'Main Customer';
            }

            $('#customerSelect').select2({
                placeholder: 'Search by Name or Code...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('salecustomers.index') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            type: getPartyType(),
                            search: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(c) {
                                return {
                                    id: c.id,
                                    text: (c.customer_id || '') + ' — ' + c.customer_name,
                                    customer: c
                                };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    if (!item.customer) return item.text;
                    const c = item.customer;
                    return $(`<div>
                        <strong>${c.customer_name}</strong>
                        <small class="text-muted ms-2">${c.customer_id || ''}</small>
                        ${c.mobile ? '<br><small class="text-muted">' + c.mobile + '</small>' : ''}
                    </div>`);
                },
                templateSelection: function(item) {
                    if (!item.customer) return item.text;
                    return item.customer.customer_id + ' — ' + item.customer.customer_name;
                }
            });

            // Party type change → reset customer
            $(document).on('change', 'input[name="partyType"]', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
            });

            // Customer selected → load details
            $('#customerSelect').on('select2:select', function(e) {
                const id = e.params.data.id;
                if (!id) return;

                $.get("{{ url('sale/customers') }}/" + id + "?t=" + new Date().getTime(), function(d) {
                    // Fill hidden fields
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    const prev = parseFloat(d.previous_balance || 0);
                    const range = parseFloat(d.balance_range || 0);
                    $('#previousBalance').val(prev.toFixed(2));
                    $('#rangeBalance').val(range.toFixed(2));

                    // Fill info card
                    $('#ci_code').text(d.customer_id || '—');
                    $('#ci_name').text(d.customer_name || '—');
                    $('#ci_mobile').text(d.mobile || '—');
                    $('#ci_address').text(d.address || '—');
                    $('#ci_prev_bal').text(prev.toFixed(2));
                    $('#ci_range_bal').text(range.toFixed(2));
                    $('#customerInfoCard').removeClass('d-none');

                    // Auto-fill Sales Officer if customer has one
                    if (d.sales_officer_id) {
                        $('#salesOfficerSelect').val(d.sales_officer_id);
                    }

                    if (typeof updateGrandTotals === 'function') updateGrandTotals();
                }).fail(function() {
                    showAlert('error', 'Failed to load customer details');
                });
            });

            // Customer cleared
            $('#customerSelect').on('select2:clear', function() {
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            function clearCustomerInfo() {
                $('#address, #tel').val('');
                $('#previousBalance, #rangeBalance').val('0');
                $('#ci_code, #ci_name, #ci_mobile, #ci_address').text('—');
                $('#ci_prev_bal, #ci_range_bal').text('0.00');
                $('#customerInfoCard').addClass('d-none');
                $('#salesOfficerSelect').val('');
            }

            $('#clearCustomerData').on('click', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            $('#btnPrint').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice', '_blank'));
            });
            $('#btnEstimate').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice?type=estimate', '_blank'));
            });
            $('#btnPrint2').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/recepit', '_blank'));
            });
            $('#btnDcThermal').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/dc-thermal', '_blank'));
            });

            // AJAX Customer Submit
            $('#btnSaveAjaxCustomer').on('click', function() {
                let form = $('#ajaxAddCustomerForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).text('Save Customer');
                        if (res.success) {
                            $('#addCustomerModal').modal('hide');
                            form[0].reset();
                            
                            // Make sure UI toggles map to the new customer's type
                            if (res.customer.customer_type === 'Walking Customer') {
                                $('#typeWalkin').prop('checked', true).trigger('change');
                            } else {
                                $('#typeCustomers').prop('checked', true).trigger('change');
                            }
                            
                            // Auto select new customer
                            let newOption = new Option(res.customer.customer_id + ' — ' + res.customer.customer_name, res.customer.id, true, true);
                            $('#customerSelect').append(newOption).trigger('change');
                            
                            // trigger select2 API selection to load customer details like Prev Bal
                            $('#customerSelect').trigger({
                                type: 'select2:select',
                                params: {
                                    data: {
                                        id: res.customer.id,
                                        text: res.customer.customer_id + ' — ' + res.customer.customer_name
                                    }
                                }
                            });
                            
                            showAlert('success', 'Customer added successfully!');
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Save Customer');
                        showAlert('error', 'Error adding customer. Check inputs.');
                    }
                });
            });
        });
    </script>
@endsection
