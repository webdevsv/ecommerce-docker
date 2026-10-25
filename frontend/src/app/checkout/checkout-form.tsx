"use client";
import { useState, useTransition } from "react";
import Link from "next/link";
import { loadStripe } from "@stripe/stripe-js";
import { Elements, PaymentElement, useElements, useStripe } from "@stripe/react-stripe-js";
import { useCart } from "@/components/cart-provider";
import { createOrderAction } from "@/lib/actions";
import { money } from "@/lib/types";

const stripePromise = loadStripe(process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY!);
type Created = { orderId: number; clientSecret: string; total: string; currency: string };

export default function CheckoutForm() {
  const { items, total, hydrated } = useCart();
  const [pending, start] = useTransition();
  const [error, setError] = useState<string>();
  const [address, setAddress] = useState("");
  const [order, setOrder] = useState<Created>();

  if (!hydrated) return <div className="h-40 animate-pulse rounded-lg bg-line" />;

  if (!order && items.length === 0) {
    return <p className="text-muted">Tu carrito está vacío. <Link href="/products" className="text-brand underline">Ver el catálogo</Link></p>;
  }

  if (order) {
    return (
      <div className="space-y-4">
        <p className="rounded-lg border border-line bg-surface p-4">
          Orden #{order.orderId} · Total a pagar: <strong>{money(order.total, order.currency)}</strong>
        </p>
        <Elements stripe={stripePromise} options={{ clientSecret: order.clientSecret }}>
          <PayForm orderId={order.orderId} />
        </Elements>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <ul className="divide-y divide-line rounded-lg border border-line bg-surface">
        {items.map((i) => (
          <li key={i.id} className="flex justify-between p-3"><span>{i.name} × {i.qty}</span><span>{money(i.price * i.qty)}</span></li>
        ))}
      </ul>
      <p className="text-right text-lg font-bold">Total estimado: {money(total)}</p>
      <div>
        <label htmlFor="address" className="mb-1 block text-sm font-medium">Dirección de envío (opcional)</label>
        <textarea id="address" value={address} onChange={(e) => setAddress(e.target.value)} maxLength={500} rows={3} className="input" />
      </div>
      {error && <p role="alert" className="field-error">{error}</p>}
      <button
        className="btn w-full"
        disabled={pending}
        onClick={() => {
          setError(undefined);
          start(async () => {
            const r = await createOrderAction(items.map((i) => ({ product_id: i.id, quantity: i.qty })), address);
            if ("error" in r) setError(r.error);
            else setOrder(r);
          });
        }}
      >
        {pending ? "Creando orden..." : "Continuar al pago"}
      </button>
    </div>
  );
}

function PayForm({ orderId }: { orderId: number }) {
  const stripe = useStripe();
  const elements = useElements();
  const [busy, setBusy] = useState(false);
  const [msg, setMsg] = useState<string>();

  async function pay() {
    if (!stripe || !elements) return;
    setBusy(true);
    setMsg(undefined);
    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: { return_url: `${window.location.origin}/checkout/success?order=${orderId}` },
    });
    // Solo llegamos aqui si hubo error; si no, Stripe redirige a return_url
    if (error) setMsg(error.message ?? "No se pudo procesar el pago.");
    setBusy(false);
  }

  return (
    <div className="space-y-4">
      <PaymentElement
        onLoadError={(e) => {
          console.error("Stripe loaderror:", e.error);
          setMsg(e.error?.message ?? "No se pudo cargar el formulario de pago.");
        }}
      />
      {msg && <p role="alert" className="field-error">{msg}</p>}
      <button onClick={pay} disabled={busy || !stripe} className="btn w-full">{busy ? "Procesando..." : "Pagar"}</button>
      <p className="text-xs text-muted">Modo de prueba: tarjeta 4242 4242 4242 4242, cualquier fecha futura y CVC.</p>
    </div>
  );
}