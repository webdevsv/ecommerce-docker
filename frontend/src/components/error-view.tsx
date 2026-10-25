"use client";
import { useEffect } from "react";

export default function ErrorView({ error, reset, message }: { error: Error & { digest?: string }; reset: () => void; message: string }) {
  useEffect(() => { console.error(error); }, [error]);
  return (
    <div role="alert" className="page text-center">
      <p className="mb-1 text-lg font-semibold text-danger">{message}</p>
      <p className="mb-4 text-muted">Verifica que la API esté encendida e intenta de nuevo.</p>
      <button onClick={reset} className="btn">Reintentar</button>
    </div>
  );
}
