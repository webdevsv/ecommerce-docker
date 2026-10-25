"use client";
import { useState } from "react";
import { useCart } from "./cart-provider";

type P = { id: number; name: string; price: number; stock: number };

export default function AddToCart({ product }: { product: P }) {
  const { items, dispatch } = useCart();
  const [added, setAdded] = useState(false);
  const inCart = items.find((i) => i.id === product.id)?.qty ?? 0;
  const maxed = inCart >= product.stock;

  return (
    <div>
      <button
        className="btn"
        disabled={product.stock < 1 || maxed}
        onClick={() => {
          dispatch({ type: "add", item: product });
          setAdded(true);
        }}
      >
        {product.stock < 1 ? "Agotado" : maxed ? "Máximo en el carrito" : "Agregar al carrito"}
      </button>
      {added && inCart > 0 && (
        <p role="status" className="mt-2 text-sm text-ok">
          Agregado. Tienes {inCart} en el carrito.
        </p>
      )}
    </div>
  );
}