import Link from "next/link";
import { cookies } from "next/headers";
import { logoutAction } from "@/lib/actions";
import CartLink from "./cart-link";

export default async function Navbar() {
  const logged = !!(await cookies()).get("token");
  return (
    <nav className="flex flex-wrap items-center gap-x-5 gap-y-2">
      <CartLink />
      {logged ? (
        <>
          <Link href="/orders" className="font-medium hover:text-brand">Mis compras</Link>
          <form action={logoutAction}><button className="btn-ghost">Cerrar sesión</button></form>
        </>
      ) : (
        <>
          <Link href="/login" className="font-medium hover:text-brand">Ingresar</Link>
          <Link href="/register" className="btn-ghost">Crear cuenta</Link>
        </>
      )}
    </nav>
  );
}