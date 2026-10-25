"use client";
import { createContext, useContext, useEffect, useReducer, useState } from "react";

export type CartItem = { id: number; name: string; price: number; stock: number; qty: number };

type Action =
  | { type: "add"; item: Omit<CartItem, "qty"> }
  | { type: "setQty"; id: number; qty: number }
  | { type: "remove"; id: number }
  | { type: "clear" }
  | { type: "hydrate"; items: CartItem[] };

function reducer(state: CartItem[], a: Action): CartItem[] {
  switch (a.type) {
    case "hydrate":
      return a.items;
    case "add": {
      const found = state.find((i) => i.id === a.item.id);
      if (!found) return [...state, { ...a.item, qty: 1 }];
      return state.map((i) => (i.id === a.item.id ? { ...i, stock: a.item.stock, qty: Math.min(i.qty + 1, a.item.stock) } : i));
    }
    case "setQty":
      return state.map((i) => (i.id === a.id ? { ...i, qty: Math.max(1, Math.min(a.qty, i.stock)) } : i));
    case "remove":
      return state.filter((i) => i.id !== a.id);
    case "clear":
      return [];
  }
}

type Ctx = { items: CartItem[]; count: number; total: number; hydrated: boolean; dispatch: React.Dispatch<Action> };
const CartCtx = createContext<Ctx | null>(null);

export function useCart() {
  const ctx = useContext(CartCtx);
  if (!ctx) throw new Error("useCart debe usarse dentro de <CartProvider>");
  return ctx;
}

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, dispatch] = useReducer(reducer, []);
  const [hydrated, setHydrated] = useState(false);

  // Hidratar tras el primer render para evitar errores de hidratacion SSR/cliente
  useEffect(() => {
    try {
      const raw = localStorage.getItem("cart");
      if (raw) dispatch({ type: "hydrate", items: JSON.parse(raw) });
    } catch {}
    setHydrated(true);
  }, []);

  useEffect(() => {
    if (!hydrated) return;
    try {
      localStorage.setItem("cart", JSON.stringify(items));
    } catch {}
  }, [items, hydrated]);

  const count = items.reduce((s, i) => s + i.qty, 0);
  const total = items.reduce((s, i) => s + i.qty * i.price, 0);

  return <CartCtx.Provider value={{ items, count, total, hydrated, dispatch }}>{children}</CartCtx.Provider>;
}
