import { Suspense } from "react";
import type { Metadata } from "next";
import { api } from "@/lib/api";
import { money, statusLabel, type Order, type Paginated } from "@/lib/types";
import { ListSkeleton } from "@/components/skeleton";

export const metadata: Metadata = { title: "Mis compras" };

async function OrderList() {
  const res = await api<Paginated<Order>>("/orders", { auth: true, tags: ["orders"] });
  if (res.data.length === 0) return <p className="text-muted">Aún no tienes compras.</p>;
  return (
    <ul className="space-y-3">
      {res.data.map((o) => (
        <li key={o.id} className="rounded-lg border border-line bg-surface p-4">
          <div className="flex justify-between font-semibold">
            <span>Orden #{o.id}</span>
            <span>{money(o.total_amount, o.currency)}</span>
          </div>
          <p className="text-sm text-muted">
            {new Date(o.created_at).toLocaleDateString("es-SV")} · {statusLabel[o.status] ?? o.status}
          </p>
          <ul className="mt-2 text-sm">
            {o.items.map((i) => <li key={i.id}>{i.product.name} × {i.quantity}</li>)}
          </ul>
        </li>
      ))}
    </ul>
  );
}

export default function OrdersPage() {
  return (
    <main className="page max-w-2xl">
      <h1 className="mb-4 text-2xl font-bold">Mis compras</h1>
      <Suspense fallback={<ListSkeleton />}><OrderList /></Suspense>
    </main>
  );
}
