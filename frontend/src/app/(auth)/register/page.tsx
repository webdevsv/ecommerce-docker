import type { Metadata } from "next";
import RegisterForm from "./register-form";

export const metadata: Metadata = { title: "Crear cuenta" };
export default function RegisterPage() {
  return <main className="page max-w-sm"><h1 className="mb-4 text-2xl font-bold">Crear cuenta</h1><RegisterForm /></main>;
}
