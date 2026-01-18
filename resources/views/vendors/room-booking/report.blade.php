@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Report') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('vendor.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Room Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Report') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-10">
              <form action="{{ route('vendor.room_bookings.report') }}" method="GET">
                <div class="row no-gutters">
                  <div class="col-lg-2">
                    <div class="form-group">
                      <label>{{ __('From') }}</label>
                      <input name="from" type="text" class="form-control datepicker"
                        placeholder="{{ __('Select Start Date') }}"
                        value="{{ !empty(request()->input('from')) ? request()->input('from') : '' }}" readonly
                        autocomplete="off">
                    </div>
                  </div>

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label>{{ __('To') }}</label>
                      <input name="to" type="text" class="form-control datepicker"
                        placeholder="{{ __('Select To Date') }}"
                        value="{{ !empty(request()->input('to')) ? request()->input('to') : '' }}" readonly
                        autocomplete="off">
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Payment Gateways') }}</label>
                      <select class="form-control h-42" name="payment_gateway">
                        <option value="" {{ empty(request()->input('payment_gateway')) ? 'selected' : '' }}>
                          {{ __('All') }}
                        </option>

                        @if (count($onlineGateways) > 0)
                          @foreach ($onlineGateways as $onlineGateway)
                            <option value="{{ $onlineGateway->keyword }}"
                              {{ request()->input('payment_gateway') == $onlineGateway->keyword ? 'selected' : '' }}>
                              {{ $onlineGateway->name }}
                            </option>
                          @endforeach
                        @endif

                        @if (count($offlineGateways) > 0)
                          @foreach ($offlineGateways as $offlineGateway)
                            <option value="{{ $offlineGateway->name }}"
                              {{ request()->input('payment_gateway') == $offlineGateway->name ? 'selected' : '' }}>
                              {{ $offlineGateway->name }}
                            </option>
                          @endforeach
                        @endif
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Payment Status') }}</label>
                      <select class="form-control h-42" name="payment_status">
                        <option value="" {{ empty(request()->input('payment_status')) ? 'selected' : '' }}>
                          {{ __('All') }}
                        </option>
                        <option value="completed"
                          {{ request()->input('payment_status') == 'completed' ? 'selected' : '' }}>
                          {{ __('Completed') }}
                        </option>
                        <option value="pending" {{ request()->input('payment_status') == 'pending' ? 'selected' : '' }}>
                          {{ __('Pending') }}
                        </option>
                        <option value="rejected"
                          {{ request()->input('payment_status') == 'rejected' ? 'selected' : '' }}>
                          {{ __('Rejected') }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary btn-sm ml-lg-3 card-header-button">
                      {{ __('Submit') }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div class="col-lg-2">
              <a href="{{ route('vendor.room_bookings.export_report') }}"
                class="btn btn-success btn-sm float-lg-right card-header-button">
                <i class="fas fa-file-export"></i> {{ __('Export') }}
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($orders) == 0)
                <h3 class="text-center mt-3">{{ __('NO ORDER FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-2">
                    <thead>
                      <tr>
                        <th>{{ __('Order No.') }}</th>
                        <th>{{ __('Room') }}</th>
                        <th>{{ __('Adult') }}</th>
                        <th>{{ __('Children') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Check Out') }}</th>
                        <th>{{ __('Booking Name') }}</th>
                        <th>{{ __('Booking Email') }}</th>
                        <th>{{ __('Booking Phone') }}</th>
                        <th>{{ __('Booking Address') }}</th>
                        <th>{{ __('Room Price') }}</th>
                        <th>{{ __('Service Charge') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Discount') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Grand Total') }}</th>
                        <th>{{ __('Payment Method') }}</th>
                        <th>{{ __('Gateway Type') }}</th>
                        <th>{{ __('Payment Status') }}</th>
                        <th>{{ __('Created At') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($orders as $order)
                        <tr>
                          <td>{{ '#' . $order->order_number }}</td>
                          <td>
                            @php
                              $roomInfo = null;
                              $roomhave = null;
                              if ($order->hotelRoom) {
                                  $roomInfo = $order->hotelRoom->room_content
                                      ->where('language_id', $defaultLang->id)
                                      ->first();
                                  $roomhave = 'done';
                              }
                            @endphp
                            @if ($roomInfo)
                              <a href="{{ route('frontend.room.details', ['id' => $roomInfo->room_id, 'slug' => $roomInfo->slug]) }}"
                                target="_blank">{{ strlen($roomInfo->title) > 25 ? mb_substr($roomInfo->title, 0, 25, 'utf-8') . '...' : $roomInfo->title }}</a>
                            @else
                              --
                            @endif
                          </td>
                          <td>{{ $order->adult }}</td>
                          <td>{{ $order->children }}</td>
                          <td>
                            {{ \Carbon\Carbon::parse($order->check_in_date_time)->format('jS F, Y . ' . ($settings->time_format == 24 ? 'H:i' : 'h:i A')) }}
                          </td>

                          <td>
                            {{ \Carbon\Carbon::parse($order->check_out_date)->format('jS F, Y') . ' . ' . \Carbon\Carbon::parse($order->check_out_time)->format($settings->time_format == 24 ? 'H:i' : 'h:i A') }}
                          </td>

                          <td>{{ $order->booking_name }}</td>
                          <td>{{ $order->booking_email }}</td>
                          <td>{{ $order->booking_phone }}</td>
                          <td>{{ $order->booking_address }}</td>
                          <td>{{ $order->roomPrice }}</td>
                          <td>{{ $order->serviceCharge }}</td>
                          <td>{{ $order->total }}</td>
                          <td>{{ $order->discount ?? '-' }}</td>
                          <td>{{ $order->tax }}</td>
                          <td>{{ $order->grand_total }}</td>
                          <td>{{ $order->payment_method }}</td>
                          <td>{{ $order->gateway_type }}</td>
                          <td>
                            @if ($order->payment_status == 1)
                              <span class="badge badge-success">{{ __('Completed') }}</span>
                            @elseif ($order->payment_status == 0)
                              <span class="badge badge-warning">{{ __('Pending') }}</span>
                            @else
                              <span class="badge badge-danger">{{ __('Rejected') }}</span>
                            @endif
                          </td>
                          <td>
                            {{ \Carbon\Carbon::parse($order->created_at)->format('jS F, Y . h:i A') }}
                          </td>

                        </tr>
                      @endforeach
                    </tbody>

                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="mt-3 text-center">
            <div class="d-inline-block mx-auto">
              @if (count($orders) > 0)
                {{ $orders->appends([
                        'from' => request()->input('from'),
                        'to' => request()->input('to'),
                        'payment_gateway' => request()->input('payment_gateway'),
                        'payment_status' => request()->input('payment_status'),
                        'order_status' => request()->input('order_status'),
                    ])->links() }}
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
