import Image from "next/image";
import Link from "next/link";
import { money, type Product } from "@/lib/types";

export default function ProductCard({ product: p, priority = false }: { product: Product; priority?: boolean }) {
  return (
    <Link href={`/products/${p.id}`} className="rounded-lg border border-line bg-surface p-3 transition-shadow hover:shadow-md">
      <div className="relative mb-3 aspect-square overflow-hidden rounded bg-bg">
        {p.image_url ? (
          <Image src={p.image_url} alt={p.name} fill sizes="(min-width: 768px) 25vw, 50vw" priority={priority} className="object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-sm text-muted">Sin imagen</div>
        )}
      </div>
      <h2 className="font-semibold leading-snug">{p.name}</h2>
      <p className="mt-1 text-lg">{money(p.price)}</p>
      <p className={`text-sm ${p.stock > 0 ? "text-muted" : "text-danger"}`}>{p.stock > 0 ? `${p.stock} disponibles` : "Agotado"}</p>
    </Link>
  );
}
