<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Camiseta Deportiva Dri-Fit', 'price' => 19.99, 'stock' => 150, 'sku' => 'SKU-0001', 'description' => 'Camiseta transpirable ideal para entrenamientos.'],
            ['name' => 'Zapatillas Running Pro', 'price' => 59.90, 'stock' => 80, 'sku' => 'SKU-0002', 'description' => 'Zapatillas ligeras con amortiguación de alto rendimiento.'],
            ['name' => 'Mochila Urbana 20L', 'price' => 34.50, 'stock' => 60, 'sku' => 'SKU-0003', 'description' => 'Mochila resistente al agua con compartimento para laptop.'],
            ['name' => 'Audífonos Bluetooth ANC', 'price' => 45.00, 'stock' => 100, 'sku' => 'SKU-0004', 'description' => 'Cancelación de ruido activa y hasta 30 horas de batería.'],
            ['name' => 'Smartwatch Fitness X3', 'price' => 89.99, 'stock' => 40, 'sku' => 'SKU-0005', 'description' => 'Monitoreo de ritmo cardiaco, GPS y notificaciones inteligentes.'],
            ['name' => 'Botella Térmica 1L', 'price' => 12.75, 'stock' => 200, 'sku' => 'SKU-0006', 'description' => 'Mantiene bebidas frías o calientes por hasta 24 horas.'],
            ['name' => 'Laptop Sleeve 15"', 'price' => 15.20, 'stock' => 120, 'sku' => 'SKU-0007', 'description' => 'Funda acolchada resistente a salpicaduras.'],
            ['name' => 'Gorra Ajustable Classic', 'price' => 9.99, 'stock' => 300, 'sku' => 'SKU-0008', 'description' => 'Gorra unisex de algodón con ajuste trasero.'],
            ['name' => 'Cargador Portátil 10000mAh', 'price' => 22.30, 'stock' => 90, 'sku' => 'SKU-0009', 'description' => 'Batería externa con carga rápida USB-C.'],
            ['name' => 'Lentes de Sol Polarizados', 'price' => 27.00, 'stock' => 70, 'sku' => 'SKU-0010', 'description' => 'Protección UV400 con marco liviano.'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'is_active' => true,
                ]
            );
        }
    }
}