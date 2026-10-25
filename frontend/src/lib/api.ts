import "server-only";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";

const BASE = process.env.API_URL!;

export class ApiError extends Error {
  constructor(public status: number, message: string, public errors?: Record<string, string[]>) {
    super(message);
  }
}

type Opts = RequestInit & { auth?: boolean; tags?: string[]; revalidate?: number };

/** Cliente de la API Laravel. Devuelve el contenido de `data` de la respuesta { success, message, data }. */
export async function api<T>(path: string, opts: Opts = {}): Promise<T> {
  const { auth, tags, revalidate, ...init } = opts;
  const headers = new Headers(init.headers);
  headers.set("Accept", "application/json");
  if (init.body) headers.set("Content-Type", "application/json");

  if (auth) {
    const token = (await cookies()).get("token")?.value;
    if (token) headers.set("Authorization", `Bearer ${token}`);
  }

  // Datos de usuario: nunca se cachean (evita mezclar datos entre usuarios).
  // Lecturas publicas: cache con tags/revalidate para poder invalidarlas tras mutaciones.
  const cacheOpts: RequestInit = auth
    ? { cache: "no-store" }
    : tags || revalidate !== undefined
      ? { next: { tags, revalidate } }
      : {};

  const res = await fetch(`${BASE}${path}`, { ...init, headers, ...cacheOpts });
  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    if (res.status === 401 && auth) redirect("/login");
    throw new ApiError(res.status, body.message ?? "Error en la API", body.errors);
  }
  return (body.data !== undefined ? body.data : body) as T;
}
