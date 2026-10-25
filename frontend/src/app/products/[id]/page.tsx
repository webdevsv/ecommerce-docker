import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { api, ApiError } from "@/lib/api";
import { money, type Product } from "@/lib/types";
import AddToCart from "@/components/add-to-cart";

async function getProduct(id: string) {
  try {
    return await api<Product>(`/products/${id}`, { tags: ["products", `product-${id}`], revalidate: 60 });
  } catch (e) {
    if (e instanceof ApiError && e.status === 404) notFound();
    throw e;
  }
}

export async function generateMetadata({ params }: { params: Promise<{ id: string }> }): Promise<Metadata> {
  const { id } = await params;
  try { return { title: (await getProduct(id)).name }; } catch { return { title: "Producto" }; }
}

export default async function ProductPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const p = await getProduct(id);
  return (
    <main className="page">
      <Link href="/products" className="mb-4 inline-block text-sm text-muted hover:text-brand">Volver al catálogo</Link>
      <div className="grid gap-8 md:grid-cols-2">
        <div className="relative aspect-square overflow-hidden rounded-lg border border-line bg-surface">
          {p.image_url ? (
            <Image src={p.image_url} alt={p.name} fill priority sizes="(min-width: 768px) 50vw, 100vw" className="object-cover" />
          ) : (
            <div className="flex h-full items-center justify-center text-muted">Sin imagen</div>
          )}
        </div>
        <div>
          <h1 className="text-3xl font-bold">{p.name}</h1>
          <p className="mt-1 text-sm text-muted">SKU: {p.sku}</p>
          <p className="my-4 max-w-prose text-muted">{p.description ?? "Sin descripción."}</p>
          <p className="text-2xl font-semibold">{money(p.price)}</p>
          <p className={`mb-5 text-sm ${p.stock > 0 ? "text-muted" : "text-danger"}`}>{p.stock > 0 ? `${p.stock} disponibles` : "Agotado"}</p>
          <AddToCart product={{ id: p.id, name: p.name, price: Number(p.price), stock: p.stock }} />
        </div>
      </div>
    </main>
  );
}
