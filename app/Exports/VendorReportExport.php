<?php

namespace App\Exports;

use App\Models\Language;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VendorReportExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
  protected $orders;

  public function __construct($orders)
  {
    $this->orders = $orders;
  }

  public function collection()
  {
    return $this->orders;
  }

  public function headings(): array
  {
    return [
      'Order No.',
      'Room',
      'Adult',
      'Children',
      'Check In',
      'Check Out',
      'Booking Name',
      'Booking Email',
      'Booking Phone',
      'Booking Address',
      'Room Price',
      'Service Charge',
      'Total',
      'Discount',
      'Tax',
      'Grand Total',
      'Payment Method',
      'Gateway Type',
      'Payment Status',
      'Created At',
    ];
  }

  public function map($order): array
  {
    $settings = DB::table('basic_settings')->first();
    $defaultLangId = Language::where('is_default', 1)->value('id');

    $formatCurrency = function ($value) use ($order) {
      if (is_null($value)) return '-';
      return ($order->currency_text_position == 'left' ? $order->currency_text . ' ' : '') .
        $value .
        ($order->currency_text_position == 'right' ? ' ' . $order->currency_text : '');
    };

    $checkIn = $order->check_in_date_time
      ? Carbon::parse($order->check_in_date_time)->format('jS F, Y . ' . ($settings->time_format == 24 ? 'H:i' : 'h:i A'))
      : '-';

    $checkOut = ($order->check_out_date && $order->check_out_time)
      ? Carbon::parse($order->check_out_date)->format('jS F, Y') . ' . ' .
      Carbon::parse($order->check_out_time)->format($settings->time_format == 24 ? 'H:i' : 'h:i A')
      : '-';

    $createdAt = $order->created_at
      ? Carbon::parse($order->created_at)->format('jS F, Y . h:i A')
      : '-';


    $roomTitle = optional(optional($order->hotelRoom)->room_content->where('language_id', $defaultLangId)->first())->title ?? '--';

    $paymentStatusLabel = match ($order->payment_status) {
      '1' => 'Completed',
      '0' => 'Pending',
      '2' => 'Rejected',
      default => 'Unknown',
    };

    return [
      '#' . $order->order_number,
      $roomTitle,
      $order->adult,
      $order->children,
      $checkIn,
      $checkOut,
      $order->booking_name,
      $order->booking_email,
      $order->booking_phone,
      $order->booking_address,
      $formatCurrency($order->roomPrice),
      $formatCurrency($order->serviceCharge),
      $formatCurrency($order->total),
      $formatCurrency($order->discount),
      $formatCurrency($order->tax),
      $formatCurrency($order->grand_total),
      $order->payment_method,
      $order->gateway_type,
      $paymentStatusLabel,
      $createdAt,
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        // Auto-fit all columns from A to V
        foreach (range('A', 'T') as $col) {
          $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
        }

        // Optional: bold headings
        $event->sheet->getStyle('A1:V1')->applyFromArray([
          'font' => ['bold' => true],
        ]);
      },
    ];
  }
}
