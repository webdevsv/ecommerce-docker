"use client";
import { useEffect } from "react";
import { useCart } from "@/components/cart-provider";

export default function ClearCart() {
  const { dispatch } = useCart();
  useEffect(() => { dispatch({ type: "clear" }); }, [dispatch]);
  return null;
}
