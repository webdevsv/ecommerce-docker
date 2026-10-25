import type { Metadata } from "next";
import CheckoutForm from "./checkout-form";

export const metadata: Metadata = { title: "Pago" };
export default function CheckoutPage() {
  return <main className="page max-w-xl"><h1 className="mb-4 text-2xl font-bold">Pago</h1><CheckoutForm /></main>;
}
