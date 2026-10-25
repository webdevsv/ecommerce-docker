import type { Metadata } from "next";
import Link from "next/link";
import { Suspense } from "react";
import { Manrope } from "next/font/google";
import { CartProvider } from "@/components/cart-provider";
import Navbar from "@/components/navbar";
import "./globals.css";

const manrope = Manrope({ subsets: ["latin"], variable: "--font-manrope", display: "swap" });

export const metadata: Metadata = { title: { default: "Tienda", template: "%s | Tienda" }, description: "E-commerce con Next.js y Laravel" };

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="es" className={manrope.variable}>
      <body className="min-h-screen antialiased">
        <CartProvider>
          <header className="border-b border-line bg-surface">
            <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-3">
              <Link href="/products" className="text-lg font-extrabold tracking-tight">Tienda</Link>
              {/* Suspense: la barra lee cookies (dinamico) sin bloquear el resto de la pagina */}
              <Suspense fallback={<div className="h-8 w-40" />}><Navbar /></Suspense>
            </div>
          </header>
          {children}
        </CartProvider>
      </body>
    </html>
  );
}