<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    protected $orders;
    
    public function __construct($orders)
    {
        $this->orders = $orders;
    }
    
    public function collection()
    {
        $data = [];
        
        foreach ($this->orders as $order) {
            $products = $order->products()->pluck('name')->implode(', ');
            $ingredient = $order->ingredients()->pluck('name')->implode(', ');
            $branch = $order->branch()->pluck('address')->implode(', ');
            $data[] = [
                'Order ID' => $order->id,
                'Table Number' => $order->table_num,
                'Products' => $products,
                'Ingredient' => $ingredient,
                'Time' => $order->created_at->format('H:i:s'),
                'Total Price' => $order->total_price,
                'Branch' => $branch
            ];
        }
        
        return collect($data);
    }
    
    public function headings(): array
    {
        return [
            'Order ID',
            'Table Number',
            'Products',
            'Ingredient',
            'Time',
            'Total Price',
            'Branch'
        ];
    }
    
    
    
}
