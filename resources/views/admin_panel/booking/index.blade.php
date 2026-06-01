@extends('admin_panel.layout.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0">BOOKINGS</h5>
                <span class="fw-bold text-dark">
                    @can('bookings.create')
                        <a href="{{ route('sale.add') }}?type=booking" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Booking
                        </a>
                    @endcan
                </span>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Reference</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Booking Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ optional($booking->customer_relation)->customer_name ?? 'N/A' }}</td>
                                <td>{{ $booking->reference }}</td>
                                <td>
                                    @foreach ($booking->items as $item)
                                        {{ optional($item->product)->item_name ?? 'N/A' }} <br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($booking->items as $item)
                                        {{ $item->qty }} <br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($booking->items as $item)
                                        {{ number_format($item->price, 2) }} <br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($booking->items as $item)
                                        {{ $item->discount_percent }}% <br>
                                    @endforeach
                                </td>
                                <td>{{ number_format($booking->total_net, 2) }}</td>
                                <td>
                                    @if ($booking->sale_status === 'booked')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-bookmark me-1"></i>Booked</span>
                                    @elseif ($booking->sale_status === 'posted')
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Confirmed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($booking->sale_status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $booking->created_at->format('d-m-Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if ($booking->sale_status === 'booked')
                                            {{-- Pending Booking: One-click Confirm + Edit --}}
                                            <form action="{{ route('sales.confirm', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to confirm this booking and convert it to a sale?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success text-white"><i class="fas fa-check me-1"></i>Confirm</button>
                                            </form>
                                            <a href="{{ route('sales.edit', $booking->id) }}" class="btn btn-sm btn-warning text-dark">Edit</a>
                                            <a href="{{ route('sales.invoice', $booking->id) }}" target="_blank" class="btn btn-sm btn-info text-white">Invoice</a>
                                        @else
                                            {{-- Confirmed Booking: DC, Invoice, Receipt --}}
                                            <a href="{{ route('sales.invoice', $booking->id) }}" target="_blank" class="btn btn-sm btn-info text-white">Invoice</a>
                                            <a href="{{ route('sales.dc', $booking->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">DC Receipt</a>
                                            <a href="{{ route('sales.receipt', $booking->id) }}" target="_blank" class="btn btn-sm btn-success text-white">Receipt</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
