"use client";
import Link from "next/link";
import { useCart } from "./cart-provider";

export default function CartLink() {
  const { count, hydrated } = useCart();
  return (
    <Link href="/cart" className="font-medium hover:text-brand">
      Carrito{hydrated && count > 0 ? ` (${count})` : ""}
    </Link>
  );
}
