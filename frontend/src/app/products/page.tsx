import { Suspense } from "react";
import Link from "next/link";
import type { Metadata } from "next";
import { api } from "@/lib/api";
import type { Paginated, Product } from "@/lib/types";
import ProductCard from "@/components/product-card";
import { GridSkeleton } from "@/components/skeleton";

export const metadata: Metadata = { title: "Catálogo" };
const PER_PAGE = 12;

async function ProductList({ page, search }: { page: number; search: string }) {
  const res = await api<Paginated<Product>>(
    `/products?per_page=${PER_PAGE}&page=${page}&search=${encodeURIComponent(search)}`,
    { tags: ["products"], revalidate: 60 }
  );
  if (res.data.length === 0) return <p className="text-muted">No hay productos que coincidan con la búsqueda.</p>;

  const q = (p: number) => `/products?page=${p}${search ? `&search=${encodeURIComponent(search)}` : ""}`;
  return (
    <>
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {res.data.map((p, i) => <ProductCard key={p.id} product={p} priority={i < 4} />)}
      </div>
      {res.last_page > 1 && (
        <div className="mt-8 flex items-center justify-center gap-4">
          {page > 1 && <Link className="btn-ghost" href={q(page - 1)}>Anterior</Link>}
          <span className="text-sm text-muted">Página {res.current_page} de {res.last_page}</span>
          {page < res.last_page && <Link className="btn-ghost" href={q(page + 1)}>Siguiente</Link>}
        </div>
      )}
    </>
  );
}

export default async function ProductsPage({ searchParams }: { searchParams: Promise<{ page?: string; search?: string }> }) {
  const sp = await searchParams;
  const page = Math.max(1, Number(sp.page) || 1);
  const search = sp.search ?? "";
  return (
    <main className="page">
      <div className="mb-6 flex flex-wrap items-end justify-between gap-3">
        <h1 className="text-2xl font-bold">Catálogo</h1>
        <form className="flex gap-2" role="search">
          <input name="search" defaultValue={search} placeholder="Buscar por nombre" aria-label="Buscar productos" className="input w-64" />
          <button className="btn">Buscar</button>
        </form>
      </div>
      <Suspense key={`${page}-${search}`} fallback={<GridSkeleton />}>
        <ProductList page={page} search={search} />
      </Suspense>
    </main>
  );
}
