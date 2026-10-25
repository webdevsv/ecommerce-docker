export type Product = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  price: string;
  stock: number;
  sku: string;
  image_url: string | null;
  is_active: boolean;
};

export type Paginated<T> = { data: T[]; current_page: number; last_page: number; total: number };

export type OrderItem = { id: number; quantity: number; unit_price: string; subtotal: string; product: Product };

export type Order = {
  id: number;
  status: string;
  total_amount: string;
  currency: string;
  shipping_address: string | null;
  created_at: string;
  items: OrderItem[];
  payment?: { status: string } | null;
};

export type Payment = { status: string; amount: string; currency: string };

export const money = (v: string | number, cur = "usd") =>
  new Intl.NumberFormat("es-SV", { style: "currency", currency: cur.toUpperCase() }).format(Number(v));

export const statusLabel: Record<string, string> = {
  pending: "Pendiente de pago",
  paid: "Pagada",
  failed: "Fallida",
  succeeded: "Pagado",
  requires_payment: "Requiere pago",
};
