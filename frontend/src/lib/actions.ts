"use server";
import { cookies } from "next/headers";
import { redirect, unstable_rethrow } from "next/navigation";
import { revalidatePath, revalidateTag } from "next/cache";
import { api, ApiError } from "./api";
import type { Order } from "./types";

export type FormState = {
  error?: string;
  fieldErrors?: Record<string, string[]>;
  values?: Record<string, string>;
};

type AuthData = { user: { id: number; name: string; email: string }; token: string };

async function saveToken(token: string) {
  (await cookies()).set("token", token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: 60 * 60 * 24 * 7,
  });
}

// Evita "open redirect": solo rutas internas
function safeNext(next: FormDataEntryValue | null) {
  const n = typeof next === "string" ? next : "";
  return n.startsWith("/") && !n.startsWith("//") ? n : "/products";
}

const str = (fd: FormData, k: string) => String(fd.get(k) ?? "");

function toState(e: unknown, values: Record<string, string>): FormState {
  unstable_rethrow(e);
  if (e instanceof ApiError) return { error: e.message, fieldErrors: e.errors, values };
  return { error: "Error inesperado. Intenta de nuevo.", values };
}

export async function loginAction(_: FormState, fd: FormData): Promise<FormState> {
  const values = { email: str(fd, "email") };
  try {
    const data = await api<AuthData>("/login", {
      method: "POST",
      body: JSON.stringify({ email: values.email, password: str(fd, "password") }),
    });
    await saveToken(data.token);
  } catch (e) {
    return toState(e, values);
  }
  redirect(safeNext(fd.get("next")));
}

export async function registerAction(_: FormState, fd: FormData): Promise<FormState> {
  const values = { name: str(fd, "name"), email: str(fd, "email"), phone: str(fd, "phone"), address: str(fd, "address") };
  try {
    const data = await api<AuthData>("/register", {
      method: "POST",
      body: JSON.stringify({
        ...values,
        phone: values.phone || undefined,
        address: values.address || undefined,
        password: str(fd, "password"),
        password_confirmation: str(fd, "password_confirmation"),
      }),
    });
    await saveToken(data.token);
  } catch (e) {
    return toState(e, values);
  }
  redirect("/products");
}

export async function logoutAction() {
  try {
    await api("/logout", { method: "POST", auth: true });
  } catch {
    /* si el token ya no es valido igual borramos la cookie */
  }
  (await cookies()).delete("token");
  redirect("/login");
}

export async function createOrderAction(
  items: { product_id: number; quantity: number }[],
  shippingAddress?: string
): Promise<{ orderId: number; clientSecret: string; total: string; currency: string } | { error: string }> {
  try {
    const res = await api<{ order: Order; client_secret: string }>("/orders", {
      method: "POST",
      auth: true,
      body: JSON.stringify({ items, shipping_address: shippingAddress?.trim() || undefined }),
    });
    // La orden descuenta stock y cambia el historial: invalidar caches
    revalidateTag("products", "max");
    revalidateTag("orders", "max");
    revalidatePath("/products");
    revalidatePath("/orders");
    return {
      orderId: res.order.id,
      clientSecret: res.client_secret,
      total: res.order.total_amount,
      currency: res.order.currency,
    };
  } catch (e) {
    unstable_rethrow(e);
    if (e instanceof ApiError) {
      const first = e.errors ? Object.values(e.errors)[0]?.[0] : undefined;
      return { error: first ?? e.message };
    }
    return { error: "Error inesperado. Intenta de nuevo." };
  }
}
