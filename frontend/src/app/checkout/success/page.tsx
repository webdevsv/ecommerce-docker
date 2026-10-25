import Link from "next/link";
import type { Metadata } from "next";
import { api, ApiError } from "@/lib/api";
import { money, statusLabel, type Payment } from "@/lib/types";
import ClearCart from "./clear-cart";

export const metadata: Metadata = { title: "Confirmación de compra" };

export default async function Success({ searchParams }: { searchParams: Promise<{ order?: string }> }) {
  const { order } = await searchParams;
  if (!order || !/^\d+$/.test(order)) {
    return <main className="page max-w-xl"><p>No se indicó la orden. <Link href="/orders" className="text-brand underline">Ver mis compras</Link></p></main>;
  }

  // Estado real consultado a la API (que a su vez lo verifica en Stripe), no el query param
  let payment: Payment | null = null;
  try {
    payment = await api<Payment>(`/payments/${order}/status`, { auth: true });
  } catch (e) {
    if (!(e instanceof ApiError)) throw e;
  }
  const ok = payment?.status === "succeeded";

  return (
    <main className="page max-w-xl">
      {ok && <ClearCart />}
      <h1 className={`mb-2 text-2xl font-bold ${ok ? "text-ok" : ""}`}>{ok ? "¡Compra confirmada!" : "Tu pago está en proceso"}</h1>
      <p>Orden #{order}{payment && <> · {money(payment.amount, payment.currency)}</>}</p>
      <p className="mb-6 text-sm text-muted">
        Estado del pago: {payment ? statusLabel[payment.status] ?? payment.status : "no disponible"}
        {!ok && ". Actualiza esta página en unos segundos."}
      </p>
      <div className="flex gap-3">
        <Link href="/orders" className="btn">Ver mis compras</Link>
        <Link href="/products" className="btn-ghost">Seguir comprando</Link>
      </div>
    </main>
  );
}
