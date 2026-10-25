import { Suspense } from "react";
import type { Metadata } from "next";
import LoginForm from "./login-form";

export const metadata: Metadata = { title: "Ingresar" };
export default function LoginPage() {
  return <main className="page max-w-sm"><h1 className="mb-4 text-2xl font-bold">Ingresar</h1><Suspense><LoginForm /></Suspense></main>;
}
