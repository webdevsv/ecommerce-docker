"use client";
import Link from "next/link";
import { useCart } from "@/components/cart-provider";
import { money } from "@/lib/types";

export default function CartPage() {
  const { items, total, hydrated, dispatch } = useCart();

  if (!hydrated) return <main className="page max-w-2xl"><div className="h-40 animate-pulse rounded-lg bg-line" /></main>;

  return (
    <main className="page max-w-2xl">
      <h1 className="mb-4 text-2xl font-bold">Carrito</h1>
      {items.length === 0 ? (
        <p className="text-muted">Tu carrito está vacío. <Link href="/products" className="text-brand underline">Ver el catálogo</Link></p>
      ) : (
        <>
          <ul className="divide-y divide-line rounded-lg border border-line bg-surface">
            {items.map((i) => (
              <li key={i.id} className="flex flex-wrap items-center justify-between gap-3 p-4">
                <div>
                  <p className="font-semibold">{i.name}</p>
                  <p className="text-sm text-muted">{money(i.price)} c/u</p>
                </div>
                <div className="flex items-center gap-2">
                  <button aria-label="Disminuir cantidad" className="btn-ghost" disabled={i.qty <= 1} onClick={() => dispatch({ type: "setQty", id: i.id, qty: i.qty - 1 })}>−</button>
                  <span className="w-6 text-center" aria-live="polite">{i.qty}</span>
                  <button aria-label="Aumentar cantidad" className="btn-ghost" disabled={i.qty >= i.stock} onClick={() => dispatch({ type: "setQty", id: i.id, qty: i.qty + 1 })}>+</button>
                </div>
                <span className="w-20 text-right font-medium">{money(i.price * i.qty)}</span>
                <button className="text-sm text-danger underline" onClick={() => dispatch({ type: "remove", id: i.id })}>Quitar</button>
              </li>
            ))}
          </ul>
          <p className="my-4 text-right text-xl font-bold">Total: {money(total)}</p>
          <div className="flex justify-end"><Link href="/checkout" className="btn">Ir a pagar</Link></div>
        </>
      )}
    </main>
  );
}
