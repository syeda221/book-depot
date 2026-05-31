<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vouchers = \App\Models\VoucherMaster::latest()->take(20)->get();
foreach ($vouchers as $v) {
    echo "ID: {$v->id} | Type: {$v->voucher_type} | Party: {$v->party_id} | Amt: {$v->total_amount} | Remarks: {$v->remarks}\n";
}
echo "\nPayment Vouchers:\n";
$pv = \App\Models\PaymentVoucher::latest()->take(5)->get();
foreach ($pv as $p) {
    echo "ID: {$p->id} | PVID: {$p->pvid} | Amt: {$p->total_amount}\n";
}
echo "\nReceipt Vouchers:\n";
$rv = \App\Models\ReceiptsVoucher::latest()->take(5)->get();
foreach ($rv as $r) {
    echo "ID: {$r->id} | RVID: {$r->rvid} | Amt: {$r->total_amount}\n";
}
